<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPhotoRequest;
use App\Models\User;
use App\Services\PhotoUploadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Вспомогательные методы для ответов (такие же как в TravelController)
     */
    private function success($data = null, string $message = null, int $code = Response::HTTP_OK): JsonResponse
    {
        $response = ['success' => true];
        if ($data !== null) $response['data'] = $data;
        if ($message) $response['message'] = $message;
        return response()->json($response, $code);
    }

    private function error(string $message, int $code = Response::HTTP_BAD_REQUEST, array $errors = null): JsonResponse
    {
        $response = ['success' => false, 'message' => $message];
        if ($errors) $response['errors'] = $errors;
        return response()->json($response, $code);
    }

    private function validationError($validator): JsonResponse
    {
        $errors = collect($validator->errors()->toArray())
            ->map(function ($messages, $field) {
                return ['field' => $field, 'message' => implode(', ', $messages)];
            })->values()->toArray();

        return $this->error('Ошибка валидации', Response::HTTP_UNPROCESSABLE_ENTITY, $errors);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        try {
            $users = User::all();
            return $this->success($users, 'Пользователи загружены успешно');
        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке пользователей', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'login' => 'required|string|max:255|unique:users',
                'name' => 'required|max:255',
                'email' => 'required|email|unique:users',
                'password' => 'required|min:6',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return $this->validationError($validator);
            }

            $user = User::create($request->all());
            DB::commit();

            return $this->success($user, 'Пользователь создан успешно', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при создании пользователя: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): JsonResponse
    {
        try {
            return $this->success($user, 'Пользователь загружен успешно');
        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке пользователя', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|max:255',
                'email' => 'sometimes|required|email|unique:users,email,' . $user->id,
                'login' => 'sometimes|required|string|max:255|unique:users,login,' . $user->id,
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return $this->validationError($validator);
            }

            $user->update($validator->validated());
            DB::commit();

            return $this->success($user, 'Пользователь обновлен успешно');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при обновлении пользователя: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user->delete();
            DB::commit();

            return $this->success(null, 'Пользователь удален успешно');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при удалении пользователя', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get current authenticated user
     */
    public function me(): JsonResponse
    {
        try {
            $user = Auth::user();

            if (!$user) {
                return $this->error('Пользователь не аутентифицирован', Response::HTTP_UNAUTHORIZED);
            }

            return $this->success(['user' => $user], 'Данные пользователя загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке данных пользователя', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Search users
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id',
                'user_search' => 'sometimes|string|min:1',
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator);
            }

            $userId = $request->query('user_id');
            $userSearch = $request->query('user_search');
            $searchTerm = "%" . trim($userSearch) . "%";

            $users = User::select('id', 'login', 'name', 'avatar')
                ->where(function($query) use ($searchTerm) {
                    $query->where('name', 'LIKE', $searchTerm)
                        ->orWhere('login', 'LIKE', $searchTerm);
                })
                ->where('id', '!=', $userId)
                ->limit(10)
                ->get();

            return $this->success($users, 'Результаты поиска загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при поиске пользователей: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update user avatar
     */
    public function updateAvatar(UploadPhotoRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $user = $request->user();

            if (!$user) {
                return $this->error('Пользователь не аутентифицирован', Response::HTTP_UNAUTHORIZED);
            }

            $uploadService = new PhotoUploadService(
                $request->file('photo'),
                'users',
                'avatars'
            );

            $result = $uploadService->uploadAndSaveToDB(
                model: $user,
                dbField: 'avatar',
                extraData: ['avatar_updated_at' => now()],
                deleteOld: true,
            );

            DB::commit();

            return $this->success($result['model'], 'Аватар обновлен успешно');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при обновлении аватара: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }


    /**
     * Update user name
     */
//    public function updateName(Request $request): JsonResponse
//    {
//        DB::beginTransaction();
//        try {
//            $user = $request->user();
//
//            if (!$user) {
//                return $this->error('Пользователь не аутентифицирован', Response::HTTP_UNAUTHORIZED);
//            }
//
//            $validator = Validator::make($request->all(), [
//                'name' => 'required|string|max:255|min:2',
//            ]);
//
//            if ($validator->fails()) {
//                DB::rollBack();
//                return $this->validationError($validator);
//            }
//
//            $user->name = $validator->validated()['name'];
//            $user->save();
//
//            DB::commit();
//
//            return $this->success(
//                $user->only(['id', 'name', 'email', 'avatar']),
//                'Имя успешно обновлено'
//            );
//
//        } catch (\Exception $e) {
//            DB::rollBack();
//            return $this->error('Ошибка при обновлении имени: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
//        }
//    }

    public function updateName(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|min:2',
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $user->name = $validated['name'];
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Имя успешно обновлено',
                'user' => $user->only(['id', 'name', 'email', 'login', 'avatar'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении имени: ' . $e->getMessage()
            ], 400);
        }
    }

    public function updateLogin(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:30',
                Rule::unique('users')->ignore($request->user()->id)
            ],
        ]);

        DB::beginTransaction();

        try {
            $user = $request->user();
            $user->login = $validated['login'];
            $user->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Логин успешно обновлен',
                'user' => $user->only(['id', 'name', 'email', 'login', 'avatar'])
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Ошибка при обновлении логина: ' . $e->getMessage()
            ], 400);
        }
    }

    public function checkLoginAvailability(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'login' => 'required|string|alpha_dash|min:3|max:30'
        ]);

        $exists = User::where('login', $validated['login'])
            ->where('id', '!=', $request->user()->id)
            ->exists();

        return response()->json([
            'available' => !$exists,
            'message' => $exists ? 'Этот логин уже занят' : 'Логин доступен'
        ]);
    }

    /**
     * Get user friends (users with shared travels)
     */
    public function friends(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return $this->validationError($validator);
            }

            $userId = $request->user_id;

            $friends = User::where(function($query) use ($userId) {
                $query->whereHas('sharedTravels', function($q) use ($userId) {
                    $q->whereHas('users', function($q) use ($userId) {
                        $q->where('users.id', $userId);
                    });
                })
                    ->orWhereHas('createdTravels', function($q) use ($userId) {
                        $q->whereHas('users', function($q) use ($userId) {
                            $q->where('users.id', $userId);
                        });
                    });
            })
                ->with(['sharedTravels' => function($query) use ($userId) {
                    $query->whereHas('users', function($q) use ($userId) {
                        $q->where('users.id', $userId);
                    })
                        ->with(['users' => function($query) use ($userId) {
                            $query->where('users.id', '!=', $userId)
                                ->select('users.id', 'users.name', 'users.login', 'users.avatar');
                        }]);
                }])
                ->where('id', '!=', $userId)
                ->get(['id', 'name', 'login', 'avatar']);

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

            return $this->success($uniqueGroups->values()->toArray(), 'Список друзей загружен успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке списка друзей: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}