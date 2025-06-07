<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\TravelController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\PhotoController;
use Illuminate\Support\Facades\Route;

// Аутентификация
Route::prefix('auth')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);
});

// Верификация
Route::prefix('email')->group(function () {
    Route::post('verify', [VerificationController::class, 'verify'])->name('verification.verify');
    Route::post('resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

// Пользователи (User)
Route::prefix('users')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('users.index');
    Route::post('/', [UserController::class, 'store'])->name('users.store');
    Route::get('search', [UserController::class, 'search'])->name('users.search'); // Получить всех пользователей по строке поиска
    Route::get('friends', [UserController::class, 'friends'])->name('users.friends');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('me', [UserController::class, 'me'])->name('users.me');
        Route::post('me/avatar', [UserController::class, 'updateAvatar'])->name('users.updateAvatar');
        Route::post('me/name', [UserController::class, 'updateName'])->name('users.updateName');
    });

    Route::get('{user}', [UserController::class, 'show'])->name('users.show');
    Route::put('{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('{user}', [UserController::class, 'destroy'])->name('users.destroy');
});


//// Путешествия (Travel)
//Route::prefix('travels')->group(function () {
//    // Публичные маршруты
//    Route::get('/', [TravelController::class, 'index'])->name('travels.index');
//    Route::get('published', [TravelController::class, 'published'])->name('travels.published');
//    Route::get('tagged', [TravelController::class, 'tagged'])->name('travels.tagged');
//    Route::get('{travel}', [TravelController::class, 'show'])->name('travels.show');
//    Route::post('with-users', [TravelController::class, 'withUsers'])->name('travels.withUsers');
//
//    // Защищенные маршруты (требуют аутентификации)
//    Route::middleware('auth:sanctum')->group(function () {
//        // Основные CRUD операции
//        Route::post('/', [TravelController::class, 'store'])->name('travels.store');
//        Route::put('{travel}', [TravelController::class, 'update'])->name('travels.update');
//        Route::delete('{travel}', [TravelController::class, 'destroy'])->name('travels.destroy');
//
//        // Сортировка
//        Route::patch('order', [TravelController::class, 'updateOrder'])->name('travels.order');
//
//        // Персональные путешествия
//        Route::get('mine', [TravelController::class, 'mine'])->name('travels.mine');
//
//        // Управление участниками
//        Route::get('{travel}/participants', [TravelController::class, 'participants'])->name('travels.participants');
//        Route::post('{travel}/participants', [TravelController::class, 'attachParticipant'])->name('travels.participants.attach');
//        Route::delete('{travel}/participants/{user}', [TravelController::class, 'detachParticipant'])->name('travels.participants.detach');
//
//        // Обложка
//        Route::post('{travel}/cover', [TravelController::class, 'updateCover'])->name('travels.cover.update');
//
//        // Фотографии
//        Route::post('{travel}/photos', [TravelController::class, 'storePhoto'])->name('travels.photos.store');
//    });
//});


// TODO: переписать с middleware AUTH (как выше)
// Путешествия (Travel)
Route::prefix('travels')->group(function () {
    Route::get('/', [TravelController::class, 'index'])->name('travels.index');
    Route::get('published', [TravelController::class, 'published'])->name('travels.published');
//    Route::get('shared', [TravelController::class, 'shared']);
    Route::get('tagged', [TravelController::class, 'tagged']);

    Route::patch('order', [TravelController::class, 'updateOrder']);
    Route::get('mine', [TravelController::class, 'mine']);
    Route::get('participants', [TravelController::class, 'participants']);
    // get переписать
    Route::post('with-users', [TravelController::class, 'withUsers'])->name('withUsers');

    Route::middleware('auth:sanctum')->group(function () {
//        Route::post('/', [TravelController::class, 'store']);
//        Route::post('order', [TravelController::class, 'updateOrder']);
//        Route::get('mine', [TravelController::class, 'mine']);

        // Управление участниками
//        Route::get('{travel}/participants', [TravelController::class, 'participants']);
//        Route::post('{travel}/participants', [TravelController::class, 'attachParticipant']);
//        Route::delete('{travel}/participants/{user}', [TravelController::class, 'detachParticipant']);

        // Обложка
        Route::post('{travel}/cover', [TravelController::class, 'updateCover']);

        // Фотографии (тут или в photos)
//        Route::post('{travel}/photos', [TravelController::class, 'updateCover']);
    });

    Route::get('{travel}', [TravelController::class, 'show']);
    Route::post('{travel}', [TravelController::class, 'store']);
    Route::put('{travel}', [TravelController::class, 'update']);
    Route::delete('{travel}', [TravelController::class, 'destroy']);


});


