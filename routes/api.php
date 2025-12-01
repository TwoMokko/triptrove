<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\TravelController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PhotoController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

Route::prefix('user')->group(function () {
    Route::get('search', [UserController::class, 'search'])->name('users.search'); // Получить всех пользователей по строке поиска
    Route::get('friends', [UserController::class, 'friends'])->name('users.friends');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('', [UserController::class, 'me']);
        Route::post('avatar', [UserController::class, 'updateAvatar']);
        Route::put('name', [UserController::class, 'updateName']);
        Route::put('login', [UserController::class, 'updateLogin']);
        Route::post('check-login', [UserController::class, 'checkLoginAvailability']);
    });
});




// Верификация
Route::prefix('email')->group(function () {
    Route::post('verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('resend', [VerificationController::class, 'resend'])->name('verification.resend');
});




// Public routes (доступны без авторизации)
Route::prefix('travels')->group(function () {
    // Публичные путешествия - доступны всем
    Route::get('published', [TravelController::class, 'published']);

    // Просмотр конкретного публичного путешествия
    Route::get('published/{id}', [TravelController::class, 'showPublished']);


    // Protected routes (требуют авторизации)
    Route::middleware('auth:sanctum')->group(function () {
        // Специальные маршруты
        Route::get('mine', [TravelController::class, 'mine']); // Мои путешествия
        Route::get('participants', [TravelController::class, 'participants']); // Где я участник
        Route::patch('order', [TravelController::class, 'updateOrder']); // Сортировка

        // Поиск/фильтрация
        Route::post('with-users', [TravelController::class, 'withUsers']);

        // Медиа
        Route::post('{travel}/cover', [TravelController::class, 'updateCover']);
        Route::post('{travel}/photos', [TravelController::class, 'addPhoto']);

        // CRUD операций
        Route::apiResource('', TravelController::class)
            ->parameters(['' => 'travel'])
            ->names([
                'index' => 'travels.index',
                'store' => 'travels.store',
                'show' => 'travels.show',
                'update' => 'travels.update',
                'destroy' => 'travels.destroy'
            ]);
    });
});

