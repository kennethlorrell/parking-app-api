<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Auth
 */
class LoginController extends Controller
{
    public function __invoke(LoginRequest $request)
    {
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => __('validation.current_password')
            ]);
        }

        $device = substr($request->userAgent() ?? '', 0, 255);

        $expiresAt = $request->remember
            ? null
            : now()->addMinutes(config('session.lifetime'));

        return response()->json([
            'access_token' => $user->createToken($device, expiresAt: $expiresAt)->plainTextToken,
        ], Response::HTTP_CREATED);
    }
}
