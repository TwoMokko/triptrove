import { travelData } from "@/app/types/types"
import api from "../../../app/api/api";

export const fetchTravels = async (userId: number): travelData[] => {
    try {
        const response = await api.get('/travelsFromUser', {
            params: {
                user_id: userId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
    }
}

export const createTravel = async (userId: number) => {
    try {
        const response = await api.post('/travels', {
            user_id: userId,
        })
        console.log('Travel created:', response.data)
        return response.data
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

export const updateTravel = async (travelId: number, travelData: travelData) => {
    try {
        const response = await api.put(`/travels/${travelId}`, travelData)
        console.log('Travel updated:', response.data)
        return response.data
    } catch (error) {
        console.error('Error updating travel:', error)
        if (error.response) {
            console.log(error.response.data.message); // Ошибка от сервера
        } else {
            console.log('Network error'); // Ошибка сети
        }
    }
}

export const deleteTravel = async (travelId: number) => {
    try {
        const response = await api.delete(`/travels/${travelId}`)
        console.log('Travel deleted:', response.data)

    } catch (error) {
        console.error('Error deleting travel:', error)
    }
}

export const fetchSharedTravels = (userId: number) => {

}

export const fetchSharedUsers = async (travelId: number) => {
    try {
        const response = await api.get(`/getSharedUsers`, {
            params: {
                travel_id: travelId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error fetching shared users:', error)
    }
}

export const fetchAttachUser = async (travelId: number, userId: number) => {
    try {
        const response = await api.post( `/attachUser`, {
            user_id: userId,
            travel_id: travelId
        })
        return response.data
    } catch (error) {
        console.error('Error attach user:', error)
        error.value = err.response?.data?.message || 'Failed to attach users';
        throw err
    }
}

export const fetchDetachUser = async (travelId: number, userId: number) => {
    try {
        const response = await api.delete( `/detachUser`, {
            params: {
                travel_id: travelId,
                user_id: userId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error detach user:', error)
        error.value = err.response?.data?.message || 'Failed to detach users';
        throw err
    }
}
