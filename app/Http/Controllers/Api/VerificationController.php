<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class VerificationController extends Controller
{
    /**
     * Подтверждение email (POST /api/email/verify)
     */
    public function verify(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:6',
            'login' => 'sometimes|string'
        ]);

        $request->validate(['login' => 'sometimes|string']);

//        $user = $request->user();
//
//        if (!$user && $request->has('login')) {
//            $user = User::where('login', $request->login)->first();
//        }

        $user = User::where('login', $request->login)->first();

        $verification = $user->emailVerification;

        if (!$verification) {
            return response()->json(['error' => 'Код не найден или устарел'], 400);
        }

        // Проверяем срок действия
        if (Carbon::now()->gt($verification->expires_at)) {
            $user->emailVerification()->delete(); // ← УДАЛЕНИЕ при просрочке
            return response()->json(['error' => 'Код просрочен'], 400);
        }

        // Проверяем попытки
        if ($verification->attempts >= 3) {
            $user->emailVerification()->delete(); // ← УДАЛЕНИЕ при превышении попыток
            return response()->json(['error' => 'Превышено число попыток'], 429);
        }

        // Проверяем код
        if (!Hash::check($request->code, $verification->code)) {
            $verification->increment('attempts');

            $attemptsLeft = 3 - $verification->attempts;
            if ($attemptsLeft <= 0) {
                $user->emailVerification()->delete(); // ← УДАЛЕНИЕ при исчерпании попыток
            }

            return response()->json([
                'error' => 'Неверный код',
                'attempts_left' => $attemptsLeft
            ], 401);
        }

        // УСПЕШНАЯ ВЕРИФИКАЦИЯ
        $user->email_verified_at = Carbon::now();
        $user->save();
        $user->emailVerification()->delete(); // ← УДАЛЕНИЕ после успешной верификации

        // Создаем полноценный токен
        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'message' => 'Email успешно подтверждён',
            'token' => $token,
            'user' => new UserResource($user)
        ]);
    }

    /**
     * Повторная отправка кода (POST /api/email/resend)
     */
    public function resend(Request $request)
    {
        $request->validate(['login' => 'sometimes|string']);

        $user = $request->user();

        if (!$user && $request->has('login')) {
            $user = User::where('login', $request->login)->first();
        }

        if (!$user) {
            return response()->json(['error' => 'Пользователь не найден'], 404);
        }

        if ($user->email_verified_at) {
            return response()->json(['error' => 'Email уже подтверждён'], 400);
        }

        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        // Используем отношение
        $user->emailVerification()->updateOrCreate(
            [],
            [
                'code' => $hashedCode,
                'expires_at' => Carbon::now()->addHour(),
                'attempts' => 0
            ]
        );

        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json(['message' => 'Новый код отправлен']);
    }
}