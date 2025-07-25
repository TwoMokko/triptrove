<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPhotoRequest;
use App\Models\User;
use App\Services\PhotoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            'avatar' => 'users/avatars/default-user.svg'
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

    public function me(): \Illuminate\Http\JsonResponse
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

    public function search(Request $request): \Illuminate\Http\JsonResponse
    {
        $user_id = $request->query('user_id');
        $creator_user_id = $request->query('creator_user_id');
        $user_search = $request->query('user_search');
        $searchTerm = "%".trim($user_search)."%";

        return response()->json(
            User::select('id', 'login', 'name')
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', $searchTerm)
                        ->orWhere('login', 'LIKE', $searchTerm);
            })
                ->where('id', '!=', $user_id)
                ->limit(10)
                ->get()
        );
    }

    public function updateAvatar(UploadPhotoRequest $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user(); // Получаем текущего аутентифицированного пользователя

            $uploadService = new PhotoUploadService(
                $request->file('photo'),
                'users',
                'avatars'
            );

            $result = $uploadService->uploadAndSaveToDB(
                model: $user,
                dbField: 'avatar',
                extraData: ['avatar_updated_at' => now()],
                deleteOld: true
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'user' => $result['model']
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateName(Request $request)
    {
        DB::beginTransaction();

        try {
            $user = $request->user();

            $validatedData = $request->validate([
                'name' => 'required|string|max:255|min:2',
            ]);

            $user->name = $validatedData['name'];
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Имя успешно обновлено',
                'user' => $user->only(['id', 'name', 'email']), // Возвращаем только нужные поля
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении имени: ' . $e->getMessage()
            ], 400);
        }
    }


    function friends(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        // Получаем всех пользователей, с которыми есть общие путешествия
        // (как участников, так и создателей)
        $friends = User::where(function($query) use ($request) {
            // Участники общих путешествий
            $query->whereHas('sharedTravels', function($q) use ($request) {
                $q->whereHas('users', function($q) use ($request) {
                    $q->where('users.id', $request->user_id);
                });
            })
                // Или создатели путешествий, где есть наш пользователь
                ->orWhereHas('createdTravels', function($q) use ($request) {
                    $q->whereHas('users', function($q) use ($request) {
                        $q->where('users.id', $request->user_id);
                    });
                });
        })
            ->with(['sharedTravels' => function($query) use ($request) {
                $query->whereHas('users', function($q) use ($request) {
                    $q->where('users.id', $request->user_id);
                })
                    ->with(['users' => function($query) use ($request) {
                        $query->where('users.id', '!=', $request->user_id)
                            ->select('users.id', 'users.name', 'users.login', 'users.avatar');
                    }]);
            }])
            ->where('id', '!=', $request->user_id)
            ->get(['id', 'name', 'login']);

        $uniqueGroups = collect();

        foreach ($friends as $friend) {
            foreach ($friend->sharedTravels as $travel) {
                $group = $travel->users->sortBy('id')->values();

                if ($group->isNotEmpty()) {
                    $groupKey = $group->pluck('id')->implode('-');
                    $uniqueGroups->put($groupKey, $group->toArray());
                }
            }
        }

        return response()->json($uniqueGroups->values()->toArray());
    }
//    function friends(Request $request): JsonResponse
//    {
//        $request->validate([
//            'user_id' => 'required|integer|exists:users,id'
//        ]);
//
//        // Получаем всех создателей, у которых есть путешествия с участием нашего пользователя
//        $creators = User::whereHas('createdTravels.users', function($query) use ($request) {
//            $query->where('users.id', $request->user_id);
//        })
//            ->with(['createdTravels' => function($query) use ($request) {
//                $query->with(['users:id,name,login'])
//                    ->whereHas('users', function($q) use ($request) {
//                        $q->where('users.id', $request->user_id);
//                    })
//                    ->orderBy('order');
//            }])
//            ->get(['id', 'name', 'login']);
//
//        return response()->json($creators);
//    }
}
