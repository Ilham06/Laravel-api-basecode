<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;

use App\Guards\JWTGuard;
use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        $this->registerPolicies();

        Auth::viaRequest('jwt', function (Request $request) {
            if ($request->bearerToken()) {
                $credential = JWT::decode($request->bearerToken(), new Key(config('jwt.secret'), config('jwt.algo')));

                return User::find($credential->sub);
            }
            return null;
        });
    }
}
