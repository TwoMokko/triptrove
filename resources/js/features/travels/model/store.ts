import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from '@/app/api/api';
import {Travel, userShort} from "../../../shared/types/api";
import { useUsersStore } from "../../../entities/user/model/store";
import {
    queryAttachUser, queryDetachUser,
    queryFriendUsers,
    querySharedTravels,
    querySharedUsers,
    queryTravelsWithUsers, queryUpdateTravelsOrder, uploadPhoto
} from "../api/travels";

export const useTravelsStore = defineStore('travels', () => {
    const myTravels = ref<Travel[]>([]);
    const allTravels = ref<Travel[]>([]);

    const travelsWithUsers = ref<travelData[]>([]);
    const sharedTravels = ref<{ id: number, name: string, login: string, travels: travelData[] }[]>([]);

    const currentTravel = ref<travelData | null>(null);
    const currentViewTravel = ref<travelData | null>(null)

    // вынести в store users
    const sharedUsers = ref<any[]>([])
    const usersFriend = ref<userShort[]>([])

    const hasTravels = computed(() => myTravels.value.length > 0)
    const hasSharedTravels = computed(() => sharedTravels.value.length > 0)
    const hasUsersFriend = computed(() => usersFriend.value.length > 0)
    const hasFriendsTravels = computed(() => travelsWithUsers.value.length > 0)
    const getTravelById = computed(() => (id: number) =>
        allTravels.value.find(travel => travel.id === id)
    )


    const isLoading = ref(false);
    const error = ref<string | null>(null);



    const usersStore = useUsersStore()

    // Public methods
    const fetchPublished = async () => {
        const response = await api.get('/travels/published');
        return response.data;
    };

    const fetchPublishedById = async (travelId: string) => {
        const response = await api.get(`/travels/${travelId}`);
        currentViewTravel.value = response.data.data
        return response.data;
    };

    const fetchMyTravels = async (userId: number) => {
        try {
            const response = await api.get('/travels/mine', {
                params: {
                    user_id: userId,
                },
            })
            console.log(response.data.data)
            myTravels.value = response.data.data;
            console.log('has', hasTravels.value)
        } catch (error) {
            console.error('Error fetching travels:', error)
            throw error
        }
    };

    const fetchParticipating = async () => {
        const response = await api.get('/travels/participants');
        return response.data;
    };

    const fetchTravelById = async (id: string) => {
        const response = await api.get(`/travels/${id}`);
        return response.data;
    };

    const fetchUpdateTravelCover = async (file: File, travelId: number): Promise<void> => {
        const formData = new FormData();
        formData.append('cover', file); // или 'photo', в зависимости от бэкенда
        formData.append('travel_id', travelId.toString()); // если нужно передать ID

        try {
            await api.post(`/travels/${travelId}/cover`, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            })
        }
        catch (err) {
            throw err
        }
    }

    const addTravel = async (travelData: Omit<travelData, 'id'>): Promise<void> => {
        // isLoading.value = true
        try {
            const newTravel = await api.post('/travels', travelData)
            myTravels.value.unshift(newTravel.data.data)
            return newTravel
        } catch (err) {
            // error.value = 'Ошибка создания путешествия'
            console.error(err)
            throw err
        } finally {
            // isLoading.value = false
        }
    }

    const editTravel = async (travelId: number, travelData: Partial<travelData>, userId: number) => {

        console.log('edit: ', travelData)

        try {
            const response = await api.put(`/travels/${travelId}`, {
                ...travelData,
                current_user_id: userId
            })
            const updatedTravel = response.data

            // Обновляем основной список
            const index = myTravels.value.findIndex(t => t.id === travelId)
            if (index !== -1) {
                myTravels.value[index] = { ...myTravels.value[index], ...updatedTravel.data }
            }

            // Обновляем travelsWithUsers (с проверкой)
            if (travelsWithUsers.value?.length) {
                const indexFr = travelsWithUsers.value.findIndex(t => t.id === travelId)
                if (indexFr !== -1) {
                    travelsWithUsers.value[indexFr] = {
                        ...travelsWithUsers.value[indexFr],
                        ...updatedTravel.data
                    }
                }
            }

            return updatedTravel
        } catch (err) {
            console.error('Ошибка обновления:', err)
            throw err
        }
    }

    const removeTravel = async (travelId: number, userId: number) => {
        // isLoading.value = true
        try {
            await api.delete(`/travels/${travelId}`, {
                params: {
                    user_id: userId
                }
            })
            myTravels.value = myTravels.value.filter(travel => travel.id !== travelId)
        } catch (err) {
            // error.value = 'Ошибка удаления путешествия'
            console.error(err)
            throw err
        } finally {
            // isLoading.value = false
        }
    }

    const setCurrentTravel = (travel: travelData | null) => {
        currentTravel.value = travel
    }






    const getTravelsWithUsers = async (userIds: number[]) => {
        // isLoading.value = true
        if (userIds.length == 1 && userIds[0] == usersStore.currentUser.id) userIds = []

        try {
            travelsWithUsers.value = await queryTravelsWithUsers(userIds)
        } catch (err) {
            error.value = 'Ошибка загрузки путешествий с другими пользователями'
            console.error(err)
        } finally {
            // isLoading.value = false
        }
    }

    const getSharedTravels = async (userId: number) => {
        isLoading.value = true
        try {
            sharedTravels.value = await querySharedTravels(userId)
            console.log('sh', sharedTravels.value)
        } catch (err) {
            error.value = 'Ошибка загрузки общих путешествий'
            console.error(err)
        } finally {
            isLoading.value = false
        }
    }
    const getSharedUsers = async () => {
        // isLoading.value = true
        try {
            sharedUsers.value = await querySharedUsers(currentTravel.value.id)
        } catch (err) {
            error.value = 'Ошибка загрузки общих пользователей'
            console.error(err)
        } finally {
            // isLoading.value = false
        }
    }
    const getFriendUsers = async () => {
        // isLoading.value = true
        try {
            usersFriend.value = await queryFriendUsers(usersStore.currentUser.id)
            console.log('friends', usersFriend.value)
        } catch (err) {
            error.value = 'Ошибка загрузки общих пользователей'
            console.error(err)
        } finally {
            // isLoading.value = false
        }
    }
    const attachUser = async (userId: number) => {
        const find = sharedUsers.value.find(itm => itm.user_id == userId)

        if (find) {
            console.log('уже есть')
            return
        }

        try {
            // isLoading.value = true
            const response = await queryAttachUser(currentTravel.value.id, userId)
            await getSharedUsers()
            console.log('what: ', response.data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to attach users';
            throw err
        } finally {
            // isLoading.value = false
        }
    }

    const detachUser = async (userId: number) => {
        try {
            // isLoading.value = true
            const response = await queryDetachUser(currentTravel.value.id, userId)
            await getSharedUsers()
            console.log('what: ', response.data)
            return response.data
        } catch (err) {
            // error.value = err.response?.data?.message || 'Failed to detach users';
            throw err
        } finally {
            // isLoading.value = false
        }
    }

    const uploadTravelCover = async (file: File, travelId: number) => {
        const formData = new FormData()
        formData.append('photo', file)
        try {
            this.currentTravel.cover = await uploadPhoto(travelId, formData)
        }
        catch (err) {
            throw err
        }
    }

    const updateTravelsOrder = (() => {
        // TODO: поменять логику (не обновлять sharedTravels, добавить возможность сортировки для путешесвий с определенным пользователем или группой)

        let debounceTimeout: ReturnType<typeof setTimeout> | null = null
        let abortController: AbortController | null = null
        let pendingUpdates: Array<{ id: string | number; order: number }> | null = null
        let pendingIsShared: boolean | null = null
        let currentCreatorId: number | undefined

        const executeUpdate = async () => {
            if (!pendingUpdates || pendingIsShared === null) return

            const updates = pendingUpdates
            const isShared = pendingIsShared
            const oldTravels = isShared ? [...sharedTravels.value.find(itm => itm.id == currentCreatorId).travels] : [...myTravels.value]

            abortController?.abort()
            abortController = new AbortController()

            try {
                console.log('here')
                await queryUpdateTravelsOrder(updates, abortController.signal)
                console.log('Order updated successfully:', updates)
            } catch (error) {
                console.error('Failed to update order:', error)
                if (isShared) sharedTravels.value.find(itm => itm.id == currentCreatorId).travels = oldTravels
                else travels.value = oldTravels
                // if (error.name !== 'AbortError') {
                //     if (isShared) sharedTravels.value.find(itm => itm.id == currentCreatorId).travels = oldTravels
                //     else travels.value = oldTravels
                // }
                throw error
            } finally {
                pendingUpdates = null
                pendingIsShared = null
                abortController = null
            }
        }

        return (updates: Array<{ id: string | number; order: number }>, isShared: boolean, creatorId?: number) => {
            currentCreatorId = creatorId
            if (isShared) {
                sharedTravels.value.find(itm => itm.id == currentCreatorId).travels = sharedTravels.value.find(itm => itm.id == currentCreatorId).travels
                    .map((item) => {
                        const update = updates.find((u) => u.id == item.id)
                        return update ? { ...item, order: update.order } : item
                    })
                    .sort((a, b) => a.order - b.order)
            } else {
                myTravels.value = myTravels.value
                    .map(item => {
                        const update = updates.find(u => u.id === item.id)
                        return update ? { ...item, order: update.order } : item
                    })
                    .sort((a, b) => a.order - b.order)
            }

            pendingUpdates = updates
            pendingIsShared = isShared

            if (debounceTimeout) clearTimeout(debounceTimeout)
            debounceTimeout = setTimeout(executeUpdate, 500)
        }
    })()

    return {
        myTravels,
        allTravels,
        currentTravel,
        travelsWithUsers,
        hasTravels,
        hasSharedTravels,
        hasUsersFriend,
        hasFriendsTravels,
        sharedUsers,
        currentViewTravel,
        getTravelById,
        usersFriend,
        fetchPublished,
        fetchPublishedById,
        fetchMyTravels,
        fetchParticipating,
        fetchTravelById,
        fetchUpdateTravelCover,
        addTravel,
        editTravel,
        removeTravel,
        setCurrentTravel,

        getTravelsWithUsers,
        getSharedTravels,
        getFriendUsers,
        attachUser,
        detachUser,
        uploadTravelCover,
        updateTravelsOrder
    };
});
