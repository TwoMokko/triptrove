<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'login' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'login' => $request->login,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $this->generateAndSendCode($user);

        return response()->json([
            'message' => 'Verification code sent',
            'user' => new UserResource($user)
        ], 201);

//        return response()->json(['message' => 'User registered successfully'], 201);
    }

    private function generateAndSendCode(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        $user->emailVerification()->updateOrCreate(
            [],
            [
                'code' => $hashedCode,
                'expires_at' => now()->addHour(),
                'attempts' => 0
            ]
        );

        // Отправка email с кодом через ваш сервис
        Mail::to($user->email)->send(new VerificationCodeMail($code));
    }

    /**
     * @throws ValidationException
     */
    public function login(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'message' => ['Неверные учетные данные'],
            ]);
        }

        if (!$user->email_verified_at) {
            return response()->json([
                'message' => 'Email не подтвержден',
                'requires_verification' => true,
                'user_id' => $user->id
            ], 403);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


}
