<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UploadPhotoRequest;
use App\Models\Travel;
use App\Models\User;
use App\Services\PhotoUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\JsonResponse;

class TravelController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $travels = Travel::all();
        return response()->json($travels);
    }

    public function mine(Request $request): JsonResponse
    {
        $userId = $request->query('user_id');

        if (!$userId) {
            return response()->json([
                'message' => 'User ID is required',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Получаем пользователя с его созданными путешествиями и участниками
        $user = User::with(['createdTravels.users:id,name,login', 'createdTravels' => function($query) {
            $query->orderBy('order');
        }])->findOrFail($userId);

        // Формируем ответ со всеми полями travels
        $response = $user->createdTravels->map(function ($travel) {
            return array_merge(
                $travel->toArray()
            );
        });

        return response()->json($response);
    }



    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Валидация
        $data = $request->validate([
            'published' => 'sometimes|boolean',
            'place' => 'required|max:255',
            'when' => 'required',
            'amount' => 'required',
            'mode_of_transport' => 'required',
            'accommodation' => 'required',
            'advice' => 'required',
            'entertainment' => 'required',
            'general_impression' => 'required',
            'user_id' => 'required|exists:users,id', // Добавил проверку существования пользователя
        ]);

        // Создание записи
        $data['order'] = Travel::where('user_id', $data['user_id'])->max('order') + 1 ?? 1;

        // Создаем путешествие
        $travel = Travel::create($data);

        // Получаем ID текущего пользователя (создателя)
        $creatorId = $data['user_id'];

        // Обработка пользователей
        $usersToAttach = [$creatorId]; // Всегда добавляем создателя

        if ($request->has('users') && is_array($request->users)) {
            $usersToAttach = array_merge(
                $usersToAttach,
                collect($request->users)
                    ->pluck('id')
                    ->reject(function ($id) use ($creatorId) {
                        // Исключаем создателя, если он случайно в списке
                        return $id == $creatorId;
                    })
                    ->toArray()
            );
        }

        // Одной операцией добавляем всех пользователей
        $travel->users()->sync($usersToAttach);

        // Загружаем отношения для ответа
        $travel->load('users:id,name,login');

        return response()->json($travel, 201);
    }
//    public function store(Request $request): JsonResponse
//    {
//        $request->validate([
//            'user_id' => 'required|exists:users,id',
//        ]);
//
//        // Создаём путешествие с дефолтными значениями
//        $travel = Travel::create([
//            'user_id' => $request->user_id,
//            'place' => '', // Дефолтное название
//            'date' => '', // Дата
//            'mode_of_transport' => '', // Дефолтный транспорт
//            'order' => Travel::where('user_id', $request->user_id)->max('order') + 1 ?? 1,
//            'published' => false,
//            // Остальные текстовые поля можно оставить пустыми или задать дефолты
//            'good_impression' => '',
//            'bad_impression' => '',
//            'general_impression' => ''
//        ]);
//
//        return response()->json($travel, 201);
//    }


    /**
     * Display the specified resource.
     */
    public function show(Travel $id)
    {
        return response()->json($id);
    }
//    public function show($id)
//    {
//        $travel = Travel::findOrFail($id);
//        return response()->json($travel);
//    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Начинаем транзакцию
        DB::beginTransaction();

        try {
            $travel = Travel::with('users')->find($id);

            if (!$travel) {
                return response()->json([
                    'message' => 'Travel not found',
                ], Response::HTTP_NOT_FOUND);
            }

            $requestUserId = $request->input('current_user_id');
            $canEdit = $travel->user_id == $requestUserId ||
                $travel->users->contains('id', $requestUserId);

            if (!$canEdit) {
                return response()->json([
                    'message' => 'Вы не можете редактировать эту запись',
                ], Response::HTTP_FORBIDDEN);
            }

            // Валидация основных данных
            $validatedData = $request->validate([
                'published' => 'sometimes|boolean',
                'place' => 'required|max:255',
                'when' => 'required',
                'amount' => 'required',
                'mode_of_transport' => 'required',
                'accommodation' => 'required',
                'advice' => 'required',
                'entertainment' => 'required',
                'general_impression' => 'required',
//                'users' => 'sometimes|array',
//                'users.*.id' => 'required|integer|exists:users,id'
            ]);

            // Обновляем основные поля путешествия
            $travel->update($validatedData);

            // Обработка связанных пользователей (если они есть в запросе)
            if ($request->has('users')) {
                $newUserIds = collect($request->users)->pluck('id')->toArray();
                $currentUserIds = $travel->users->pluck('id')->toArray();

                // Пользователи для добавления
                $usersToAttach = array_diff($newUserIds, $currentUserIds);
                // Пользователи для удаления
                $usersToDetach = array_diff($currentUserIds, $newUserIds);

                if (!empty($usersToAttach)) {
                    $travel->users()->attach($usersToAttach);
                }

                if (!empty($usersToDetach)) {
                    $travel->users()->detach($usersToDetach);
                }
            }

            // Загружаем обновленные данные
            $travel->load('users:id,name,login');

            // Фиксируем транзакцию
            DB::commit();

            return response()->json([
                'message' => 'Travel updated successfully',
                'data' => $travel,
            ], Response::HTTP_OK);

        } catch (\Exception $e) {
            // Откатываем транзакцию при ошибке
            DB::rollBack();

            return response()->json([
                'message' => 'Error updating travel: ' . $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $userIdCreator = $request->user_id;

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

        if ($userId != $userIdCreator) {
            return response()->json([
                'message' => 'Вы не можете удалять эту запись',
            ], Response::HTTP_FORBIDDEN);
        }

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

    public function published(Request $request): JsonResponse
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
                    return [
                        'id' => $travel->id,
                        'place' => $travel->place,
                        'when' => $travel->when,
                        'amount' => $travel->amount,
                        'mode_of_transport' => $travel->mode_of_transport,
                        'accommodation' => $travel->accommodation,
                        'advice' => $travel->advice,
                        'entertainment' => $travel->entertainment,
                        'general_impression' => $travel->general_impression,
                        'order' => $travel->order,
                        'published' => $travel->published,
                        'created_at' => $travel->created_at,
                        'updated_at' => $travel->updated_at,
                        'users' => $travel->users->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'login' => $user->login,
                                'avatar' => $user->avatar,
                            ];
                        })->toArray()
                    ];
                })->toArray()
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

    public function participants(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id'
        ]);

        // Получаем всех создателей, у которых есть путешествия с участием нашего пользователя
        $creators = User::whereHas('createdTravels.users', function($query) use ($request) {
            $query->where('users.id', $request->user_id);
        })
            ->with(['createdTravels' => function($query) use ($request) {
                $query->with(['users:id,name,login'])
                    ->whereHas('users', function($q) use ($request) {
                        $q->where('users.id', $request->user_id);
                    })
                    ->orderBy('order');
            }])
            ->get(['id', 'name', 'login']);

        // Формируем ответ
        $response = $creators->map(function ($creator) {
            return [
                'id' => $creator->id,
                'name' => $creator->name,
                'login' => $creator->login,
                'avatar' => $creator->avatar,
                'travels' => $creator->createdTravels->map(function ($travel) {
                    return [
                        'id' => $travel->id,
                        'place' => $travel->place,
                        'when' => $travel->when,
                        'amount' => $travel->amount,
                        'mode_of_transport' => $travel->mode_of_transport,
                        'accommodation' => $travel->accommodation,
                        'advice' => $travel->advice,
                        'entertainment' => $travel->entertainment,
                        'general_impression' => $travel->general_impression,
                        'order' => $travel->order,
                        'published' => $travel->published,
                        'user_id' => $travel->user_id,
                        'created_at' => $travel->created_at,
                        'updated_at' => $travel->updated_at,
                        'users' => $travel->users->map(function ($user) {
                            return [
                                'id' => $user->id,
                                'name' => $user->name,
                                'login' => $user->login,
                                'avatar' => $user->avatar
                            ];
                        })->toArray()
                    ];
                })->toArray()
            ];
        });

        return response()->json($response);
    }

    public function updateCover(UploadPhotoRequest $request, Travel $travel)
    {
        if ($request->user()->id !== $travel->user_id) {
            abort(403, 'Unauthorized');
        }
        try {
            $uploadService = new PhotoUploadService(
                file: $request->file('photo'),
                folder: 'travels',
                subfolder: 'covers'
            );

            $result = $uploadService->uploadAndSaveToDB(
                model: $travel,
                dbField: 'cover', // Указываем название поля
                extraData: ['cover_updated_at' => now()],
                deleteOld: true
            );

            return response()->json([
                'success' => true,
                'travel' => $result['model']
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function addPhoto(UploadPhotoRequest $request, Travel $travel)
    {
        try {
            $uploadService = new PhotoUploadService(
                file: $request->file('photo'),
                folder: 'travels',
                subfolder: 'photos'
            );

            $result = $uploadService->uploadAndSaveToDB(
                model: $travel,
                dbField: 'cover', // Указываем название поля
                extraData: ['cover_updated_at' => now()],
                deleteOld: true
            );

            return response()->json([
                'success' => true,
                'photo' => $result['model']->photos()->latest()->first()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function updateOrder(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|integer|exists:travels,id',
            'items.*.order' => 'required|integer|min:1',
        ]);

        // Проверка сигнала отмены (AbortController)
        if ($request->hasHeader('Abort-Signal')) {
            return response()->json(['message' => 'Request aborted'], 499); // 499 - Client Closed Request
        }

        try {
            DB::transaction(function () use ($validated) {
                foreach ($validated['items'] as $item) {
                    DB::table('travels')
                        ->where('id', $item['id'])
                        ->update(['order' => $item['order']]);
                }
            });

            return response()->json(['message' => 'Order updated successfully']);

        } catch (\Exception $e) {
//            Log::error('Order update failed: ' . $e->getMessage());
            return response()->json(['message' => 'Update failed'], 500);
        }
    }


    public function withUsers(Request $request): JsonResponse
    {
        $userIds = $request->validate([
            'user_ids' => 'array',
            'user_ids.*' => 'integer'
        ])['user_ids'];

        if (empty($userIds)) {
            return response()->json([]);
        }

        $travels = Travel::with(['users:id,name,login']) // Загружаем пользователей
        ->whereExists(function ($query) use ($userIds) {
            $query->select(DB::raw(1))
                ->from('travel_user')
                ->whereColumn('travel_user.travel_id', 'travels.id')
                ->whereIn('travel_user.user_id', $userIds)
                ->groupBy('travel_user.travel_id')
                ->havingRaw('COUNT(DISTINCT travel_user.user_id) = ?', [count($userIds)]);
        })
            ->whereNotExists(function ($query) use ($userIds) {
                $query->select(DB::raw(1))
                    ->from('travel_user')
                    ->whereColumn('travel_user.travel_id', 'travels.id')
                    ->whereNotIn('travel_user.user_id', $userIds);
            })
            ->get()
            ->map(function ($travel) {
                return array_merge(
                    $travel->toArray(),
                    ['users' => $travel->users] // Добавляем пользователей в ответ
                );
            });

        return response()->json($travels);
    }

    public function shared()
    {

    }

    public function tagged()
    {

    }

//    public function getUsersForTravel(Request $request): JsonResponse
//    {
//        $request->validate([
//            'travel_id' => 'required|integer|exists:travels,id'
//        ]);
//
//        $users = DB::table('users')
//            ->join('travel_user', 'users.id', '=', 'travel_user.user_id')
//            ->where('travel_user.travel_id', $request->travel_id)
//            ->get();
//
//        return response()->json($users);
//    }
//
//    public function attachParticipant(Request $request): JsonResponse
//    {
//        $request->validate([
//            'user_id' => 'required|integer|exists:users,id',
//            'travel_id' => 'required|integer|exists:travels,id'
//        ]);
//
//        $travel = Travel::findOrFail($request->travel_id);
//        $userId = $request->user_id;
//
//        // Проверяем, не существует ли уже такая связь
//        if (!$travel->users()->where('user_id', $userId)->exists()) {
//            $travel->users()->attach($userId);
//
//            return response()->json([
//                'message' => 'User successfully attached to travel',
//                'attached' => true
//            ], 200);
//        }
//
//        return response()->json([
//            'message' => 'User is already attached to this travel',
//            'attached' => false
//        ], 200);
//    }
//
//
//    public function detachParticipant(Request $request): JsonResponse
//    {
//        $request->validate([
//            'user_id' => 'required|integer|exists:users,id',
//            'travel_id' => 'required|integer|exists:travels,id'
//        ]);
//
//        $travel = Travel::findOrFail($request->travel_id);
//        $userId = $request->user_id;
//
//        // Проверяем, существует ли такая связь
//        if ($travel->users()->where('user_id', $userId)->exists()) {
//            $travel->users()->detach($userId);
//
//            return response()->json([
//                'message' => 'User successfully detached from travel',
//                'detached' => true
//            ], 200);
//        }
//
//        return response()->json([
//            'message' => 'User is not attached to this travel',
//            'detached' => false
//        ], 200);
//    }
}
