import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchUserByToken, fetchUsers, uploadPhoto } from '../api/users'
import { userData } from "../../../app/types/types"
import { useAuthStore } from "../../auth"

export const useUsersStore = defineStore('users', () => {
    const users = ref<userData[]>([])
    const currentUser = ref<userData>(null)

    const authStore = useAuthStore()

    const getUsers = async (searchQuery = '') => {
        users.value = await fetchUsers(searchQuery)
    }

    const getUserByToken = async (token: string) => {
        currentUser.value = await fetchUserByToken(token)
    }

    const resetCurrentUser = () => {
        currentUser.value = null
    }

    const updateAvatar = async (file) => {
        try {
            const formData = new FormData()
            formData.append('photo', file)
            currentUser.value.avatar = await uploadPhoto(formData, authStore.token)
        }
        catch (err) {
            throw err
        }
    }

    return {
        users,
        currentUser,
        // getUsers,
        getUserByToken,
        resetCurrentUser,
        updateAvatar,
    }
})
