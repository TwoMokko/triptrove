<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): \Illuminate\Http\JsonResponse
    {
        $users = User::all();
        return response()->json($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'login' => 'required|string|max:255|unique:users',
            'name' => 'required|max:255',
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::create($request->all());
        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        //
    }

    public function getUserByToken(): \Illuminate\Http\JsonResponse
    {
        // Получаем аутентифицированного пользователя
        $user = Auth::user();

        if ($user) {
            // Возвращаем данные пользователя
            return response()->json([
                'user' => $user,
            ]);
        }

        // Если пользователь не найден
        return response()->json([
            'error' => 'Пользователь не найден',
        ], 404);
    }

    public function getUsersFromSearchString(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_id = $request->query('user_id');
        $user_search = $request->query('user_search');
        $searchTerm = "%".trim($user_search)."%";

        return response()->json(
            User::where(function($query) use ($searchTerm) {
                $query->where('name', 'LIKE', $searchTerm)
                    ->orWhere('login', 'LIKE', $searchTerm);
            })
                ->where('id', '!=', $user_id)
                ->limit(10)
                ->get()
        );
    }
}
