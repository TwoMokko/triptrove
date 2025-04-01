<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TravelController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:sanctum')->get('/usersByToken', [UserController::class, 'getUserByToken']);

Route::get('/users', [UserController::class, 'index']); // Получить всех пользователей
Route::post('/users', [UserController::class, 'store']); // Создать нового пользователя
Route::get('/users/{id}', [TravelController::class, 'show']); // Получить пользователя по ID
Route::put('/users/{id}', [TravelController::class, 'update']); // Обновить пользователя
Route::delete('/users/{id}', [TravelController::class, 'destroy']); // Удалить пользователя

Route::get('/travels', [TravelController::class, 'index']); // Получить все путешествия по User ID для юсера (и созданные им и другими)
Route::get('/travelsFromUser', [TravelController::class, 'getTravelsByUserID']); // Получить все путешествия по User ID, созданные им
Route::get('/travelsFromOther', [TravelController::class, 'fromOther']); // Получить все путешествия по User ID, созданные другим возможные для редактирования
Route::get('/travelsFromTag', [TravelController::class, 'fromTag']); // Получить все путешествия для User ID или для любого по тегу
Route::post('/travels', [TravelController::class, 'store']); // Создать новое путешествие
Route::get('/travels/{id}', [TravelController::class, 'show']); // Получить путешествие по ID
Route::put('/travels/{id}', [TravelController::class, 'update']); // Обновить путешествие
Route::delete('/travels/{id}', [TravelController::class, 'destroy']); // Удалить путешествие


// TODO: разобраться с методами (что для чего и когда) и ыфше тоже
Route::get('/usersSearch', [UserController::class, 'getUsersFromSearchString']); // Получить всех пользователей по строке поиска
Route::get('/travels/{travel_id}/users', [TravelController::class, 'getUsersForTravel']);
Route::post('/travels/{travel_id}/users', [TravelController::class, 'attachUser']);
Route::delete('/travels/{travel_id}/users/{user}', [TravelController::class, 'detachUser']);
