<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VerificationCodeMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request)
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

        return response()->json(['message' => 'Verification code sent']);

//        return response()->json(['message' => 'User registered successfully'], 201);
    }

    private function generateAndSendCode(User $user)
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
    public function login(Request $request)
    {
        $request->validate([
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = User::where('login', $request->login)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['The provided credentials are incorrect.'],
            ]);
        }

        $token = $user->createToken('auth_token', ['*'], now()->addDays(7))->plainTextToken;

        return response()->json(['token' => $token]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }


}
