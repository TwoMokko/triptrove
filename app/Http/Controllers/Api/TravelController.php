<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class TravelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $travels = Travel::all();
        return response()->json($travels);
    }

    public function getTravelsByUserID(Request $request)
    {
        // Получаем user_id из запроса (например, из query-параметра)
        $userId = $request->query('user_id');

        // Если user_id не передан, возвращаем все записи (или ошибку, в зависимости от логики)
        if (!$userId) {
            return response()->json([
                'message' => 'User ID is required',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Получаем все записи с определённым user_id и сортируем их по order
//        $travels = Travel::where('user_id', $userId)
//            ->orderBy('order')
//            ->get();

        $user = User::find($userId);
        $createdTravels = $user->createdTravels;

        // Возвращаем результат
        return response()->json($createdTravels);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Создаём путешествие с дефолтными значениями
        $travel = Travel::create([
            'user_id' => $request->user_id,
            'place' => '', // Дефолтное название
            'date' => '', // Дата
            'mode_of_transport' => '', // Дефолтный транспорт
            'order' => Travel::where('user_id', $request->user_id)->max('order') + 1 ?? 1,
            'published' => false,
            // Остальные текстовые поля можно оставить пустыми или задать дефолты
            'good_impression' => '',
            'bad_impression' => '',
            'general_impression' => ''
        ]);

        return response()->json($travel, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Travel $travel)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $travel = Travel::find($id);

        // Если запись не найдена, возвращаем ошибку 404
        if (!$travel) {
            return response()->json([
                'message' => 'Travel not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Валидация данных
        $validatedData = $request->validate([
            'published' => 'sometimes|boolean',
            'place' => 'sometimes|string|max:255',
            'date' => 'sometimes|string',
            'mode_of_transport' => 'sometimes|string',
            'good_impression' => 'sometimes|string',
            'bad_impression' => 'sometimes|string',
            'general_impression' => 'sometimes|string',
            'user_id' => 'sometimes|integer'
        ]);

        // Обновляем запись
        $travel->update($validatedData);

        // Возвращаем успешный ответ
        return response()->json([
            'message' => 'Travel updated successfully',
            'data' => $travel,
        ], Response::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        // Находим запись
        $travel = Travel::find($id);

        if (!$travel) {
            return response()->json([
                'message' => 'Travel not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Получаем order и user_id удаляемой записи
        $deletedOrder = $travel->order;
        $userId = $travel->user_id;

        // Удаляем запись
        $travel->delete();

        // Сдвигаем все order, которые выше удалённого, на 1 вниз
        Travel::where('user_id', $userId)
            ->where('order', '>', $deletedOrder)
            ->decrement('order'); // Уменьшаем order на 1

        // Возвращаем успешный ответ
        return response()->json([
            'message' => 'Travel deleted successfully',
        ], Response::HTTP_OK);
    }

    public function getAllPublishedTravels(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем опубликованные путешествия с пагинацией
        $travels = Travel::with('creator:id,name,login')
            ->where('published', true)
            ->orderBy('order', 'desc')
            ->paginate(20);

        // Группируем по создателям
        $grouped = $travels->groupBy('user_id')->map(function ($userTravels, $userId) {
            $creator = $userTravels->first()->creator;

            return [
                'id' => $creator->id,
                'name' => $creator->name,
                'login' => $creator->login,
                'travels' => $userTravels->map(function ($travel) {
                    return $travel->makeHidden(['user_id'])->toArray();
                })->values()->toArray()
            ];
        })->values();

        // Добавляем метаданные пагинации
        $response = [
            'data' => $grouped,
            'meta' => [
                'current_page' => $travels->currentPage(),
                'last_page' => $travels->lastPage(),
                'per_page' => $travels->perPage(),
                'total' => $travels->total(),
            ],
            'links' => [
                'first' => $travels->url(1),
                'last' => $travels->url($travels->lastPage()),
                'prev' => $travels->previousPageUrl(),
                'next' => $travels->nextPageUrl(),
            ],
        ];

        return response()->json($response);
    }

//    public function getTravelForUser($user_id): \Illuminate\Http\JsonResponse
//    {
//        $user = User::find($user_id);
//        $travels = $user->travels;
//
//        return response()->json($travels);
//    }
//
    public function getTravelsForUser(Request $request): \Illuminate\Http\JsonResponse
    {
        // Получаем все путешествия с создателями
        $travels = Travel::with('creator:id,name,login')
            ->whereHas('users', function($query) use ($request) {
                $query->where('users.id', $request->user_id);
            })
            ->get();

        // Группируем по создателям
        $grouped = $travels->groupBy('user_id')->map(function ($userTravels, $userId) {
            $creator = $userTravels->first()->creator;

            return [
                'id' => $creator->id,
                'name' => $creator->name,
                'login' => $creator->login,
                'travels' => $userTravels->map(function ($travel) {
                    return $travel->makeHidden(['created_at', 'updated_at'])->toArray();
//                    return $travel->toArray();
                })->values()->toArray()
            ];
        })->values();

        return response()->json($grouped);
    }

    public function getUsersForTravel(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'travel_id' => 'required|integer|exists:travels,id'
        ]);

        $users = DB::table('users')
            ->join('travel_user', 'users.id', '=', 'travel_user.user_id')
            ->where('travel_user.travel_id', $request->travel_id)
            ->get();

        return response()->json($users);
    }

    public function attachUserToTravel(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'travel_id' => 'required|integer|exists:travels,id'
        ]);

        $travel = Travel::findOrFail($request->travel_id);
        $userId = $request->user_id;

        // Проверяем, не существует ли уже такая связь
        if (!$travel->users()->where('user_id', $userId)->exists()) {
            $travel->users()->attach($userId);

            return response()->json([
                'message' => 'User successfully attached to travel',
                'attached' => true
            ], 200);
        }

        return response()->json([
            'message' => 'User is already attached to this travel',
            'attached' => false
        ], 200);
    }


    public function detachUser(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'travel_id' => 'required|integer|exists:travels,id'
        ]);

        $travel = Travel::findOrFail($request->travel_id);
        $userId = $request->user_id;

        // Проверяем, существует ли такая связь
        if ($travel->users()->where('user_id', $userId)->exists()) {
            $travel->users()->detach($userId);

            return response()->json([
                'message' => 'User successfully detached from travel',
                'detached' => true
            ], 200);
        }

        return response()->json([
            'message' => 'User is not attached to this travel',
            'detached' => false
        ], 200);
    }
}
