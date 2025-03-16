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

export const createTravel = async (travelData: travelData) => {
    try {
        const response = await api.post('/travels', travelData)
        console.log('Travel created:', response.data)
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

export const updateTravel = async (travelId: number, travelData: travelData) => {
    try {
        const response = await api.put(`/travels/${travelId}`, travelData)
        console.log('Travel updated:', response.data)
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
