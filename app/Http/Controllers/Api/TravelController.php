<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

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
    public function store(Request $request)
    {
        $request->validate([
            'published' => 'sometimes|boolean',
            'place' => 'required|max:255',
            'date' => 'required',
            'mode_of_transport' => 'required',
            'good_impression' => 'required',
            'bad_impression' => 'required',
            'general_impression' => 'required',
            'user_id' => 'required',
        ]);

        // Получаем user_id из запроса
        $userId = $request->input('user_id');

        // Находим максимальное значение поля `order` для записей с определённым user_id
        $maxOrder = Travel::where('user_id', $userId)->max('order');

        // Если записей нет, устанавливаем order = 1, иначе увеличиваем на 1
        $order = $maxOrder ? $maxOrder + 1 : 1;

        // Добавляем вычисленное значение `order` в данные запроса
        $data = $request->all();
        $data['order'] = $order;

        // Создаем запись
        $travel = Travel::create($data);
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

    public function getTravelForUser($user_id)
    {
        $user = User::find($user_id);
        $travels = $user->travels;

        return response()->json($travels);
    }

    public function getUsersForTravel($travel_id)
    {
        $travel = Travel::find($travel_id);
        $users = $travel->users;

        return response()->json($users);
    }

    public function attachUser(Request $request, Travel $travel)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        $travel->users()->syncWithoutDetaching([$request->user_id]);
        return response()->json(['message' => 'User attached']);
    }

    public function detachUser(Travel $travel, User $user)
    {
        $travel->users()->detach($user->id);
        return response()->json(['message' => 'User detached']);
    }
}
