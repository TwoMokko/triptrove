import { defineStore } from 'pinia'
import { ref } from 'vue'
import {
    fetchTravels,
    createTravel,
    updateTravel,
    deleteTravel,
    fetchSharedTravels
} from '../api/travels'
import { travelData } from "@/app/types/types"

export const useTravelsStore = defineStore('travels', () => {
    const travels = ref<travelData[]>([])
    const sharedTravels = ref([])

    const getTravels = async (userId) => {
        travels.value = fetchTravels(userId)
    }

    const getSharedTravels = async (userId) => {
        sharedTravels.value = await fetchSharedTravels(userId)
    }

    const addTravel = async (travelData: travelData) => {
        const newTravel = await createTravel(travelData)
        travels.value.push(newTravel)
    }

    const editTravel = async (travelId: number, travelData: travelData) => {
        const updatedTravel = await updateTravel(travelId, travelData)
        const index = travels.value.findIndex(t => t.id === travelId)
        if (index !== -1) {
            travels.value[index] = updatedTravel
        }
    }

    const removeTravel = async (travelId: number) => {
        await deleteTravel(travelId)
        travels.value = travels.value.filter(t => t.id !== travelId)
    }

    return {
        travels,
        sharedTravels,
        getTravels,
        getSharedTravels,
        addTravel,
        editTravel,
        removeTravel,
    }
})
