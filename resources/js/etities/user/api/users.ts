import { userData } from "../../../app/types/types";
import api from "../../../app/api/api";

export const fetchUserByToken = async (token: string): userData => {
    try {
        const response = await api.get('/usersByToken', {
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

export const fetchUsers = (searchQuery: string): userData[] => {
    console.log({searchQuery})
    return []
}
