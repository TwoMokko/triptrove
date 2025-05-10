import { defineStore } from 'pinia'
import { computed, ref } from 'vue'
import {
    createTravel,
    deleteTravel,
    queryAttachUser,
    queryDetachUser, queryFriendUsers,
    queryPublishedTravels,
    querySharedTravels,
    querySharedUsers,
    queryTravels, queryTravelsWithUsers, queryUpdateTravelsOrder,
    updateTravel, updateTravelsOrder,
    uploadPhoto,
} from '../api/travels'
import { travelData, OrderUpdatePayload } from "../../../app/types/types"
import { debounce } from "../../../shared/lib/debounce"
import { useUsersStore } from "../../user"

export const useTravelsStore = defineStore('travels', () => {
    // State
    const publishedTravels = ref([])
    const travels = ref<travelData[]>([])
    const travelsWithUsers = ref<travelData[]>(null)
    const sharedTravels = ref<{ id: number, name: string, login: string, travels: travelData[] }[]>([])

    const currentTravel = ref<travelData | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // const usersShared = ref<{ travelId: number, users: userData[] }[]>()
    const sharedUsers = ref()
    const usersFriend = ref([])

    const usersStore = useUsersStore()

    // Getters
    const hasPublishedTravels = computed(() => publishedTravels.value.length > 0)
    const hasTravels = computed(() => travels.value.length > 0)
    const hasSharedTravels = computed(() => sharedTravels.value.length > 0)
    const hasUsersFriend = computed(() => usersFriend.value.length > 0)
    const hasFriendsTravels = computed(() => travelsWithUsers.value.length > 0)
    const getTravelById = computed(() => (id: number) =>
        travels.value.find(travel => travel.id === id)
    )

    // Actions
    const getPublishedTravels = async (page: number) => {
        isLoading.value = true
        try {
            publishedTravels.value = await queryPublishedTravels(page)
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
            travels.value = await queryTravels(userId)
        } catch (err) {
            error.value = 'Ошибка загрузки путешествий'
            console.error(err)
        } finally {
            isLoading.value = false
        }
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
            console.log(usersFriend.value)
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

            // sharedTravels.value = sharedTravels.value.map(usersTravel => {
            //     return {
            //         ...usersTravel,
            //         travels: usersTravel.travels.map(travel => {
            //             if (travel.id === travelId) {
            //                 return { ...travel, ...updatedTravel.data }
            //             }
            //             return travel
            //         })
            //     }
            // })


            const indexFr = travelsWithUsers.value.findIndex(travel => travel.id === travelId)
            if (indexFr !== -1) {
                travelsWithUsers.value[indexFr] = { ...travelsWithUsers.value[indexFr], ...updatedTravel.data }
            }
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
            const oldTravels = isShared ? [...sharedTravels.value.find(itm => itm.id == currentCreatorId).travels] : [...travels.value]

            abortController?.abort()
            abortController = new AbortController()

            try {
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
                travels.value = travels.value
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
        // State
        publishedTravels,
        travels,
        travelsWithUsers,
        sharedTravels,
        currentTravel,
        isLoading,
        error,
        sharedUsers,
        usersFriend,

        // Getters
        hasPublishedTravels,
        hasTravels,
        hasSharedTravels,
        hasUsersFriend,
        hasFriendsTravels,
        getTravelById,

        // Actions
        getPublishedTravels,
        getTravels,
        getTravelsWithUsers,
        getSharedTravels,
        addTravel,
        editTravel,
        removeTravel,
        setCurrentTravel,
        attachUser,
        detachUser,
        getSharedUsers,
        getFriendUsers,
        uploadTravelCover,
        updateTravelsOrder,
    }
})
