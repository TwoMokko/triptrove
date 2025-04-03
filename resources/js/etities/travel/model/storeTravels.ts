import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import {
    fetchTravels,
    createTravel,
    updateTravel,
    deleteTravel,
    fetchSharedTravels
} from '../api/travels'
import type { travelData } from "@/app/types/types"

export const useTravelsStore = defineStore('travels', () => {
    // State
    const travels = ref<travelData[]>([])
    const sharedTravels = ref<travelData[]>([])

    const currentTravel = ref<travelData | null>(null)
    const isLoading = ref(false)
    const error = ref<string | null>(null)

    // Getters
    const hasTravels = computed(() => travels.value.length > 0)
    const getTravelById = computed(() => (id: number) =>
        travels.value.find(travel => travel.id === id)
    )

    // Actions
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

    const editTravel = async (travelId: number, travelData: Partial<travelData>) => {

        console.log('edit: ', travelData)

        isLoading.value = true
        try {
            const updatedTravel = await updateTravel(travelId, travelData)
            const index = travels.value.findIndex(travel => travel.id === travelId)
            if (index !== -1) {
                travels.value[index] = { ...travels.value[index], ...updatedTravel.data }
            }
            return updatedTravel
        } catch (err) {
            error.value = 'Ошибка обновления путешествия'
            console.error(err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const removeTravel = async (travelId: number) => {
        isLoading.value = true
        try {
            await deleteTravel(travelId)
            travels.value = travels.value.filter(travel => travel.id !== travelId)
        } catch (err) {
            error.value = 'Ошибка удаления путешествия'
            console.error(err)
            throw err
        } finally {
            isLoading.value = false
        }
    }

    const setCurrentTravel = (travel: travelData | null) => {
        currentTravel.value = travel
    }

    return {
        // State
        travels,
        sharedTravels,
        currentTravel,
        isLoading,
        error,

        // Getters
        hasTravels,
        getTravelById,

        // Actions
        getTravels,
        getSharedTravels,
        addTravel,
        editTravel,
        removeTravel,
        setCurrentTravel
    }
})
