<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\AccountVerification;
use App\Models\User;
use App\Models\UserVerification;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
    }

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = request(['email', 'password']);
        $user = User::whereEmail($credentials['email'])->with('roles')->first();

        if (!$user) {
            return response()->json(['error' => 'User Not Found'], 401);
        }
        if (!Hash::check($credentials['password'], $user->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        if (!$user->email_verified_at) {
            return response()->json(['error' => 'Please verify your account'], 400);
        }
        if (!$user->is_active) {
            return response()->json(['error' => 'User is not active'], 400);
        }

        $payload = [
            'iss'  => env('APP_URL'),
            'sub'  => $user->id,
            'data' => $user,
            'iat'  => time(),
            'exp'  => time() + 86400 // 1 day
        ];
        $token = JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));

        $payloadRefreshToken = $payload;
        $payloadRefreshToken['exp'] = $payloadRefreshToken['exp'] + (86400 * 14); // 2 week
        $refreshToken = JWT::encode($payloadRefreshToken, config('jwt.secret'), config('jwt.algo'));

        return $this->respondWithToken($token, $refreshToken, 86400);

    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me(): JsonResponse
    {
        return response()->json(User::with('roles')->find(Auth::id()));
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout(): JsonResponse
    {
        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh(Request $request)
    {
        try {
            $token = $request->token;
            $payload = JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algo')));
            if (!$payload) {
                return response()->json([
                    'message' => 'Token is invalid!'
                ], 401);
            }
            $user = User::find($payload->sub);
            if (!$user) {
                return response()->json([
                    'message' => 'Token is invalid!'
                ], 401);
            }
            $url = $request->header('origin');
            $payload = [
                'iss'  => $url,
                'sub'  => $user->id,
                'data' => $user,
                'iat'  => time(),
                'exp'  => time() + 86400 // 1 day
            ];
            $token = JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));

            $payloadRefreshToken = $payload;
            $payloadRefreshToken['exp'] = $payloadRefreshToken['exp'] + (86400 * 14); // 2 week
            $refreshToken = JWT::encode($payloadRefreshToken, config('jwt.secret'), config('jwt.algo'));

            return $this->respondWithToken($token, $refreshToken, 86400);
        } catch (SignatureInvalidException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $refreshToken, $expire): JsonResponse
    {
        return response()->json([
            'access_token' => $token,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => $expire,
        ]);
    }

    public function register(RegisterRequest $request)
    {
        try {
            DB::beginTransaction();

            $user = new User();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->assignRole($request->role);
            $user->save();

            $this->sendOtp($user);

            $data['status'] = 200;
            $data['message'] = 'Register Success';
            $data['data'] = $user;

            DB::commit();

        } catch (Exception $e) {
            $data['status'] = 400;
            $data['message'] = $e->getMessage();
        }

        return response()->json($data, $data['status']);

    }

    public function tokenVerify(Request $request)
    {
        try {
            DB::beginTransaction();

            $request->validate([
                'otp' => 'required'
            ]);

            $is_exits = UserVerification::with('user')->whereToken($request->otp)->whereStatus(0)->first();

            $datetime = Carbon::now()->addHour(7);

            if ($is_exits && $is_exits->expired > $datetime) {
                $user = User::findOrFail($is_exits->user_id);
                $user->is_active = true;
                $user->email_verified_at = $datetime;
                $user->save();

                $is_exits->status = 1;
                $is_exits->save();

                $payload = [
                    'iss'  => env('APP_URL'),
                    'sub'  => $user->id,
                    'data' => $user,
                    'iat'  => time(),
                    'exp'  => time() + 86400 // 1 day
                ];
                $jwt['token'] = JWT::encode($payload, config('jwt.secret'), config('jwt.algo'));

                $payloadRefreshToken = $payload;
                $payloadRefreshToken['exp'] = $payloadRefreshToken['exp'] + (86400 * 14); // 2 week
                $jwt['refreshToken'] = JWT::encode($payloadRefreshToken, config('jwt.secret'), config('jwt.algo'));

                $data['message'] = 'Account has been verified';
                $data['status'] = 200;
                $data['data'] = $jwt;
            } else {
                $data['message'] = 'OTP Code is invalid';
                $data['status'] = 400;
            }

            DB::commit();
        } catch (Exception $e) {
            $data['status'] = 400;
            $data['message'] = $e->getMessage();
        }

        return response()->json($data, $data['status']);
    }

    public function sendOtp($user)
    {
        $token = rand(111111, 999999);
        $datetime = Carbon::now()->addHour(7);

        $verification = UserVerification::create([
            'user_id' => $user->id,
            'token' => $token,
            'expired' => $datetime->addMinute(5)
        ]);

        Mail::to($user->email)->send(new AccountVerification($verification));
    }
}

