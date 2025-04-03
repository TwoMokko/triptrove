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
        $request->validate([
            'code' => 'required|digits:6',
        ]);

        $user = $request->user();

        // Проверка наличия записи верификации
        $verification = EmailVerification::where('user_id', $user->id)->first();

        if (!$verification) {
            return response()->json(['error' => 'Verification code not found'], 404);
        }

        // Проверка времени жизни кода
        if (Carbon::now()->gt($verification->expires_at)) {
            $verification->delete();
            return response()->json(['error' => 'Code expired'], 400);
        }

        // Проверка попыток
        if ($verification->attempts >= 3) {
            $verification->delete();
            return response()->json(['error' => 'Too many attempts'], 429);
        }

        // Проверка кода
        if (!Hash::check($request->code, $verification->code)) {
            $verification->increment('attempts');
            return response()->json(['error' => 'Invalid code'], 401);
        }

        // Успешная верификация
        $user->email_verified_at = Carbon::now();
        $user->save();
        $verification->delete();

        return response()->json(['message' => 'Email verified successfully']);
    }

    /**
     * Повторная отправка кода (POST /api/resend)
     */
    public function resend(Request $request)
    {
        $user = $request->user();

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
