<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\EmailVerification;
use App\Http\Controllers\Controller;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationCodeMail;

class VerificationController extends Controller
{
    /**
     * Подтверждение email (POST /api/verify)
     */
    public function verify(Request $request)
    {
//        $request->validate([
//            'code' => 'required|digits:6',
//        ]);
        if (strlen($request->code) != 6) {
            return response()->json(['error' => 'Код должен быть 6-значным'], 422);
        }

        if (!$request->login) return response()->json(['error' => 'Login не найден'], 404);
        $user = $request->user() ?? User::where('login', $request->login)->first();

        // Проверка наличия записи верификации
        $verification = EmailVerification::where('user_id', $user->id)->first();

        if (!$verification) {
            return response()->json(['error' => 'Код не найден'], 404);
        }

        // Проверка времени жизни кода
        if (Carbon::now()->gt($verification->expires_at)) {
            $verification->delete();
            return response()->json(['error' => 'Код просрочен, получите новый код'], 400);
        }

        // Проверка попыток
        if ($verification->attempts >= 3) {
            $verification->delete();
            return response()->json(['error' => 'Слишком много попыток, получите новый код'], 429);
        }

        if (!Hash::check($request->code, $verification->code)) {
            $verification->increment('attempts'); // Увеличиваем только при ошибке
            return response()->json([
                'error' => 'Неверный код, осталось попыток: ' . 3 - $verification->attempts,
            ], 401);
        }

        // Успешная верификация
        $user->email_verified_at = Carbon::now();
        $user->save();
        $verification->delete();

        return response()->json(['message' => 'Email verified successfully'], 201);
    }

    /**
     * Повторная отправка кода (POST /api/resend)
     */
    public function resend(Request $request)
    {
        if (!$request->login) return response()->json(['error' => 'Login не найден'], 404);
        $user = $request->user() ?? User::where('login', $request->login)->first();

        // Проверка уже подтвержденного email
        if ($user->email_verified_at) {
            return response()->json(['error' => 'Email already verified'], 400);
        }

        // Генерация нового кода
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        $hashedCode = Hash::make($code);

        // Обновление записи верификации
        EmailVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'code' => $hashedCode,
                'expires_at' => Carbon::now()->addHour(),
                'attempts' => 0
            ]
        );

        // Отправка email
        Mail::to($user->email)->send(new VerificationCodeMail($code));

        return response()->json(['message' => 'New code sent']);
    }
}
