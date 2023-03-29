<?php

namespace App\Http\Controllers\Apps;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalProfileRequest;
use App\Models\Umkm;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function personalProfileStore(PersonalProfileRequest $request)
    {
        $title = 'Personal Profile';
        try {
            DB::beginTransaction();

            $store = Umkm::created($request->all());

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'success created' . $title,
                'data' => $store
            ]);
        } catch (Exception $e) {
            DB::rollBack();
            return response()->json([
                'msg' => $e->getMessage()
            ], 400);
        }
    }
}
