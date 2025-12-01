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
            'email_verified_at' => null, // Явно указываем, что email не подтвержден
        ]);

        // Генерируем и отправляем код верификации
        $this->generateAndSendCode($user);

        // Создаем временный токен для верификации
        $tempToken = $user->createToken('verification-token', ['verify-email'])->plainTextToken;

        return response()->json([
            'message' => 'Verification code sent to your email',
            'temp_token' => $tempToken,
            'user_id' => $user->id,
            'login' => $user->login
        ], 201);
    }

    private function generateAndSendCode(User $user): void
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        // Используем отношение для создания/обновления
        $user->emailVerification()->updateOrCreate(
            [],
            [
                'code' => $hashedCode,
                'expires_at' => now()->addHour(),
                'attempts' => 0
            ]
        );

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

        $user->tokens()->delete();

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => new UserResource($user) // Добавьте данные пользователя
        ])->withoutCookie('laravel_session');
    }

    public function logout(Request $request): \Illuminate\Http\JsonResponse
    {
//        $request->user()->currentAccessToken()->delete();
        $request->user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully'])
            ->withoutCookie('laravel_session')
            ->withoutCookie('XSRF-TOKEN');
    }

    public function checkAuth()
    {

    }


}
