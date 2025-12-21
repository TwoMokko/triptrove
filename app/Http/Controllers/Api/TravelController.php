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
use Illuminate\Support\Facades\Validator;

class TravelController extends Controller
{
    /**
     * Вспомогательные методы для ответов
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
            $travels = Travel::all();
            return $this->success($travels, 'Путешествия загружены успешно');
        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке путешествий', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function mine(Request $request): JsonResponse
    {
        try {
            $userId = $request->query('user_id');

            if (!$userId) {
                return $this->error('ID пользователя обязательно', Response::HTTP_BAD_REQUEST);
            }

            $user = User::with(['createdTravels.users:id,name,login', 'createdTravels' => function($query) {
                $query->orderBy('order');
            }])->find($userId);

            if (!$user) {
                return $this->error('Пользователь не найден', Response::HTTP_NOT_FOUND);
            }

            $response = $user->createdTravels->map(function ($travel) {
                return array_merge($travel->toArray());
            });

            return $this->success($response, 'Ваши путешествия загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке ваших путешествий', Response::HTTP_INTERNAL_SERVER_ERROR);
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
                'published' => 'sometimes|boolean',
                'place' => 'required|max:255',
                'when' => 'required',
                'amount' => 'required',
                'mode_of_transport' => 'required',
                'accommodation' => 'required',
                'advice' => 'required',
                'entertainment' => 'required',
                'general_impression' => 'required',
                'user_id' => 'required|exists:users,id',
            ]);

            if ($validator->fails()) {
                DB::rollBack();
                return $this->validationError($validator);
            }

            $data = $validator->validated();
            $data['order'] = Travel::where('user_id', $data['user_id'])->max('order') + 1 ?? 1;

            $travel = Travel::create($data);
            $creatorId = $data['user_id'];
            $usersToAttach = [$creatorId];

            if ($request->has('users') && is_array($request->users)) {
                $usersToAttach = array_merge(
                    $usersToAttach,
                    collect($request->users)
                        ->pluck('id')
                        ->reject(fn($id) => $id == $creatorId)
                        ->toArray()
                );
            }

            $travel->users()->sync($usersToAttach);
            $travel->load('users:id,name,login');

            DB::commit();
            return $this->success($travel, 'Путешествие создано успешно', Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при создании путешествия: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id): JsonResponse
    {
        try {
            $travel = Travel::find($id);
            if (!$travel) {
                return $this->error('Путешествие не найдено', Response::HTTP_NOT_FOUND);
            }
            return $this->success($travel, 'Путешествие загружено успешно');
        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке путешествия', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $travel = Travel::with('users')->find($id);
            if (!$travel) {
                return $this->error('Путешествие не найдено', Response::HTTP_NOT_FOUND);
            }

            $requestUserId = $request->input('current_user_id');
            $canEdit = $travel->user_id == $requestUserId || $travel->users->contains('id', $requestUserId);

            if (!$canEdit) {
                return $this->error('Вы не можете редактировать эту запись', Response::HTTP_FORBIDDEN);
            }

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
            ]);

            $travel->update($validatedData);

            if ($request->has('users')) {
                $newUserIds = collect($request->users)->pluck('id')->toArray();
                $currentUserIds = $travel->users->pluck('id')->toArray();

                $usersToAttach = array_diff($newUserIds, $currentUserIds);
                $usersToDetach = array_diff($currentUserIds, $newUserIds);

                if (!empty($usersToAttach)) $travel->users()->attach($usersToAttach);
                if (!empty($usersToDetach)) $travel->users()->detach($usersToDetach);
            }

            $travel->load('users:id,name,login');
            DB::commit();

            return $this->success($travel, 'Путешествие обновлено успешно');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при обновлении путешествия: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        DB::beginTransaction();
        try {
            $request->validate(['user_id' => 'required|integer|exists:users,id']);

            $travel = Travel::find($id);
            if (!$travel) {
                return $this->error('Путешествие не найдено', Response::HTTP_NOT_FOUND);
            }

            if ($travel->user_id != $request->user_id) {
                return $this->error('Вы не можете удалять эту запись', Response::HTTP_FORBIDDEN);
            }

            $deletedOrder = $travel->order;
            $userId = $travel->user_id;
            $travel->delete();

            Travel::where('user_id', $userId)->where('order', '>', $deletedOrder)->decrement('order');
            DB::commit();

            return $this->success(null, 'Путешествие удалено успешно');

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->error('Ошибка при удалении путешествия', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function published(Request $request): JsonResponse
    {
        try {
            $travels = Travel::with('creator:id,name,login')
                ->where('published', true)
                ->orderBy('order', 'desc')
                ->paginate(20);

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

            $response = [
                'data' => $grouped,
                'meta' => $travels->toArray()['meta'],
                'links' => $travels->toArray()['links'],
            ];

            return $this->success($response, 'Опубликованные путешествия загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке опубликованных путешествий', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function participants(Request $request): JsonResponse
    {
        try {
            $request->validate(['user_id' => 'required|integer|exists:users,id']);

            $creators = User::whereHas('createdTravels.users', function($query) use ($request) {
                $query->where('users.id', $request->user_id);
            })->with(['createdTravels' => function($query) use ($request) {
                $query->with(['users:id,name,login'])
                    ->whereHas('users', function($q) use ($request) {
                        $q->where('users.id', $request->user_id);
                    })->orderBy('order');
            }])->get(['id', 'name', 'login']);

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

            return $this->success($response, 'Путешествия с участием загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при загрузке путешествий с участием', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function updateCover(UploadPhotoRequest $request, Travel $travel): JsonResponse
    {
        try {
            if ($request->user()->id !== $travel->user_id) {
                return $this->error('Неавторизованный доступ', Response::HTTP_FORBIDDEN);
            }

            $uploadService = new PhotoUploadService(
                $request->file('photo'),
                'travels',
                'covers'
            );

            $result = $uploadService->uploadAndSaveToDB(
                $travel,
                'cover',
                ['cover_updated_at' => now()],
                true
            );

            return $this->success($result['model'], 'Обложка обновлена успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при обновлении обложки: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function addPhoto(UploadPhotoRequest $request, Travel $travel): JsonResponse
    {
        try {
            $uploadService = new PhotoUploadService(
                $request->file('photo'),
                'travels',
                'photos'
            );

            $result = $uploadService->uploadAndSaveToDB(
                $travel,
                'cover',
                ['cover_updated_at' => now()],
                true
            );

            return $this->success($result['model']->photos()->latest()->first(), 'Фото добавлено успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при добавлении фото: ' . $e->getMessage(), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateOrder(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.id' => 'required|integer|exists:travels,id',
                'items.*.order' => 'required|integer|min:1',
            ]);

            if ($request->hasHeader('Abort-Signal')) {
                return $this->error('Запрос отменен', 499);
            }

            DB::transaction(function () use ($validated) {
                foreach ($validated['items'] as $item) {
                    DB::table('travels')->where('id', $item['id'])->update(['order' => $item['order']]);
                }
            });

            return $this->success(null, 'Порядок обновлен успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при обновлении порядка: ' . $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function withUsers(Request $request): JsonResponse
    {
        try {
            $userIds = $request->validate(['user_ids' => 'array', 'user_ids.*' => 'integer'])['user_ids'] ?? [];

            if (empty($userIds)) {
                return $this->success([]);
            }

            $travels = Travel::with(['users:id,name,login'])
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
                    return array_merge($travel->toArray(), ['users' => $travel->users]);
                });

            return $this->success($travels, 'Общие путешествия загружены успешно');

        } catch (\Exception $e) {
            return $this->error('Ошибка при поиске общих путешествий', Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function shared(): JsonResponse
    {
        // TODO: Implement
        return $this->success([], 'Функция в разработке');
    }

    public function tagged(): JsonResponse
    {
        // TODO: Implement
        return $this->success([], 'Функция в разработке');
    }
}
