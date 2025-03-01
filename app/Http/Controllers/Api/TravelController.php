<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Travel;
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'place' => 'required|max:255',
            'date' => 'required',
            'mode_of_transport' => 'required',
            'good_impression' => 'required',
            'bad_impression' => 'required',
            'general_impression' => 'required',
            'user_id' => 'required',
        ]);

        $travel = Travel::create($request->all());
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
        $travel = Travel::find($id);

        if (!$travel) {
            return response()->json([
                'message' => 'Travel not found',
            ], Response::HTTP_NOT_FOUND);
        }

        // Удаляем запись
        $travel->delete();

        // Возвращаем успешный ответ
        return response()->json([
            'message' => 'Travel deleted successfully',
        ], Response::HTTP_OK);
    }
}
