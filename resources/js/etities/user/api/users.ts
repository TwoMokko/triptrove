import { userData } from "../../../app/types/types"
import api from "../../../app/api/api"

export const queryUserByToken = async (token: string): userData => {
    try {
        const response = await api.get('/user', {
            headers: {
                Authorization: `Bearer ${token}`,
            },
        })
        console.log('User get:', response.data)
        return response.data.user

    } catch (error) {
        console.error('Error creating travel:', error)
    }
}

export const queryUsers = (searchQuery: string): userData[] => {
    console.log({searchQuery})
    return []
}

export const queryUpdateName = async (newName: string, token) => {
    try {
        const response = await api.post(
            '/profile/name',
            { name: newName },
            {
                headers: {
                    'Content-Type': 'application/json',  // Указываем JSON
                    'Authorization': `Bearer ${token}`
                },
            }
        )
        return response.data
    } catch (err) {
        throw err
    }
}

export const uploadPhoto = async (formData, token) => {
    try {
        const response = await api.post('/profile/avatar', formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'Authorization': `Bearer ${token}`
            },
        })
        return response.data.user.avatar
    } catch (err) {
        throw err
    }
}
