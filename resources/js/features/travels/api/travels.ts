import { travelData } from "@/app/types/types"
import { api } from "../../../app/api/api";

export const queryPublishedTravels = async (page?: number) => {
    try {
        const response = await api.get('/travels/published', {
            query: { page: page ?? 1 },
            watch: false
        })
        return response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
        throw error
    }
}
export const queryTravel = async (travelId: number): travelData => {
    try {
        const response = await api.get(`/travels/${travelId}`)
        return response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
        throw error
    }
}
export const queryTravels = async (userId: number): travelData[] => {
    try {
        const response = await api.get('/travels/mine', {
            params: {
                user_id: userId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error fetching travels:', error)
        throw error
    }
}
export const queryTravelsWithUsers = async (userIds: number[]): travelData[] => {
    try {
        // возможно переписать на get
        const response = await api.post('/travels/with-users', {
            user_ids: userIds
        })
        return response.data.data
    } catch (error) {
        console.error('Error fetching travels with users:', error)
        throw error
    }
}

export const createTravel = async (travelData: travelData) => {
    try {
        const response = await api.post('/travels', travelData)
        console.log('Travel created:', response.data)
        return response.data
    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

// export const createTravel = async (userId: number) => {
//     try {
//         const response = await api.post('/travels', {
//             user_id: userId,
//         })
//         console.log('Travel created:', response.data)
//         return response.data
//     } catch (error) {
//         console.error('Error creating travel:', error)
//         throw error
//     }
// }

export const updateTravel = async (travelId: number, travelData: travelData, userId: number) => {
    try {
        const response = await api.put(`/travels/${travelId}`, {
            ...travelData,
            current_user_id: userId
        })
        console.log('Travel updated:', response.data)
        return response.data
    } catch (error) {
        console.error('Error updating travel:', error)
        if (error.response) {
            console.log(error.response.data.message); // Ошибка от сервера
            throw error.response
        } else {
            console.log('Network error'); // Ошибка сети
        }
    }
}

export const deleteTravel = async (travelId: number, userId: number) => {
    try {
        const response = await api.delete(`/travels/${travelId}`, {
            params: {
                user_id: userId
            }
        })
        console.log('Travel deleted:', response.data)

    } catch (error) {
        console.error('Error deleting travel:', error)
        throw error
    }
}

export const querySharedTravels = async (userId: number) => {
    try {
        const response = await api.get(`/travels/participants`, {
            params: {
                user_id: userId,
            },
        })
        return response.data.data
    } catch (error) {
        console.error('Error fetching shared users:', error)
        throw error
    }
}

export const querySharedUsers = async (travelId: number) => {
    try {
        const response = await api.get(`/getSharedUsers`, {
            params: {
                travel_id: travelId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error fetching shared users:', error)
        throw error
    }
}

export const queryFriendUsers = async (userId: number) => {
    try {
        const response = await api.get(`/user/friends`, {
            params: {
                user_id: userId,
            },
        })
        return response.data.data
    } catch (error) {
        console.error('Error fetching shared users:', error)
        throw error
    }
}

export const queryAttachUser = async (travelId: number, userId: number) => {
    try {
        const response = await api.post( `/travels/participants`, {
            user_id: userId,
            travel_id: travelId
        })
        return response.data
    } catch (error) {
        console.error('Error attach user:', error)
        error = error.response?.data?.message || 'Failed to attach users';
        throw error
    }
}

export const queryDetachUser = async (travelId: number, userId: number) => {
    try {
        const response = await api.delete( `/travels/participants`, {
            params: {
                travel_id: travelId,
                user_id: userId,
            },
        })
        return response.data
    } catch (error) {
        console.error('Error detach user:', error)
        error = error.response?.data?.message || 'Failed to detach users';
        throw error
    }
}

export const uploadPhoto = async (travelId, formData) => {
    try {
        const response = await api.post(`/travels/${travelId}/photos`, formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        })
        return response.data
    }
    catch (err) {
        throw err
    }
}

export const queryUpdateTravelsOrder = async (items, signal) => {
    try {
        const response = await api.patch('/travels/order', { items: items }, { signal: signal })
        return response.data
    }
    catch (err) {
        throw err
    }
}

