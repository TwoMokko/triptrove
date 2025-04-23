import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
    createTravel,
    deleteTravel,
    fetchAttachUser,
    fetchDetachUser,
    fetchPublishedTravels,
    fetchSharedTravels,
    fetchSharedUsers,
    fetchTravels,
    updateTravel,
    uploadPhoto,
} from '../api/travels'
import { travelData, OrderUpdatePayload } from "../../../app/types/types"
import {debounce} from "../../../shared/lib/debounce"

export const useTravelsStore = defineStore('travels', () => {
    // State
    const publishedTravels = ref([])
    const travels = ref<travelData[]>([])
    const sharedTravels = ref<{ id: number, name: string, login: string, travels: travelData[] }[]>([])

    const currentTravel = ref<travelData | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // const usersShared = ref<{ travelId: number, users: userData[] }[]>()
    const sharedUsers = ref()

    // Getters
    const hasPublishedTravels = computed(() => publishedTravels.value.length > 0)
    const hasTravels = computed(() => travels.value.length > 0)
    const hasSharedTravels = computed(() => sharedTravels.value.length > 0)
    const getTravelById = computed(() => (id: number) =>
        travels.value.find(travel => travel.id === id)
    )

    // Actions
    const getPublishedTravels = async (page: number) => {
        isLoading.value = true
        try {
            publishedTravels.value = await fetchPublishedTravels(page)
        } catch (err) {
            error.value = 'Ошибка загрузки published путешествий'
            console.error(err)
        } finally {
            isLoading.value = false
        }
    }
    const getTravels = async (userId: number) => {
        isLoading.value = true
        try {
            travels.value = await fetchTravels(userId)
        } catch (err) {
            error.value = 'Ошибка загрузки путешествий'
            console.error(err)
        } finally {
            isLoading.value = false
        }
    }

    const getSharedTravels = async (userId: number) => {
        isLoading.value = true
        try {
            sharedTravels.value = await fetchSharedTravels(userId)
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
            sharedUsers.value = await fetchSharedUsers(currentTravel.value.id)
        } catch (err) {
            error.value = 'Ошибка загрузки общих пользователей'
            console.error(err)
        } finally {
            // isLoading.value = false
        }
    }

    const addTravel = async (travelData: Omit<travelData, 'id'>) => {
        isLoading.value = true
        try {
            const newTravel = await createTravel(travelData)
            travels.value.push(newTravel)
            return newTravel
        } catch (err) {
            error.value = 'Ошибка создания путешествия'
            console.error(err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    // const addTravel = async (userId: number) => {
    //     // isLoading.value = true
    //     try {
    //         const newTravel = await createTravel(userId)
    //         travels.value.push(newTravel)
    //         currentTravel.value = newTravel
    //         console.log({newTravel})
    //         return newTravel
    //     } catch (err) {
    //         error.value = 'Ошибка создания путешествия'
    //         console.error(err)
    //         throw err
    //     } finally {
    //         // isLoading.value = false
    //     }
    // }

    const editTravel = async (travelId: number, travelData: Partial<travelData>, userId: number) => {

        console.log('edit: ', travelData)

        // isLoading.value = true
        try {
            const updatedTravel = await updateTravel(travelId, travelData, userId)
            const index = travels.value.findIndex(travel => travel.id === travelId)
            if (index !== -1) {
                travels.value[index] = { ...travels.value[index], ...updatedTravel.data }
            }

            sharedTravels.value = sharedTravels.value.map(usersTravel => {
                return {
                    ...usersTravel,
                    travels: usersTravel.travels.map(travel => {
                        if (travel.id === travelId) {
                            return { ...travel, ...updatedTravel.data }
                        }
                        return travel
                    })
                }
            })
            return updatedTravel
        } catch (err) {
            // error.value = 'Ошибка обновления путешествия'
            console.error(err)
            throw err
        } finally {
            // isLoading.value = false
        }
    }

    const removeTravel = async (travelId: number, userId: number) => {
        // isLoading.value = true
        try {
            await deleteTravel(travelId, userId)
            travels.value = travels.value.filter(travel => travel.id !== travelId)
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

    const attachUser = async (userId: number) => {
        const find = sharedUsers.value.find(itm => itm.user_id == userId)

        if (find) {
            console.log('уже есть')
            return
        }

        try {
            // isLoading.value = true
            const response = await fetchAttachUser(currentTravel.value.id, userId)
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
            const response = await fetchDetachUser(currentTravel.value.id, userId)
            await getSharedUsers()
            console.log('what: ', response.data)
            return response.data
        } catch (err) {
            error.value = err.response?.data?.message || 'Failed to detach users';
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
        // Выносим debounce наружу, чтобы он создавался один раз
        const debouncedUpdate = debounce(async (updates: Array<{ id: string | number, order: number }>, oldTravels: travelData[], abortSignal) => {
            try {
                // await api.patch('/travels/update-order', { items: updates }, { signal: abortSignal })
                console.log('Order updated successfully:', updates)
            } catch (error) {
                console.error('Failed to update order:', error)
                if (error.name !== 'AbortError') {
                    travels.value = oldTravels
                }
                // Откатываем изменения при ошибке
                travels.value = oldTravels
                throw error
            }
        }, 3000)

        let abortController: AbortController | null = null

        return (updates: Array<{ id: string | number, order: number }>) => {
            abortController?.abort()
            abortController = new AbortController()

            const oldTravels = [...travels.value]

            // Оптимистичное обновление локального состояния
            travels.value = travels.value
                .map(item => {
                    const update = updates.find(u => u.id === item.id)
                    return update ? { ...item, order: update.order } : item
                })
                .sort((a, b) => a.order - b.order)

            // Запускаем отложенное обновление на сервере
            debouncedUpdate(updates, oldTravels, abortController.signal)
        }
    })()


    return {
        // State
        publishedTravels,
        travels,
        sharedTravels,
        currentTravel,
        isLoading,
        error,
        sharedUsers,

        // Getters
        hasPublishedTravels,
        hasTravels,
        hasSharedTravels,
        getTravelById,

        // Actions
        getPublishedTravels,
        getTravels,
        getSharedTravels,
        addTravel,
        editTravel,
        removeTravel,
        setCurrentTravel,
        attachUser,
        detachUser,
        getSharedUsers,
        uploadTravelCover,
        updateTravelsOrder
    }
})
