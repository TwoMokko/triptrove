import { defineStore } from 'pinia'
import { ref } from 'vue'
import { fetchUsers, fetchUserByToken } from '../api/users'
import { userData } from "../../../app/types/types"

export const useUsersStore = defineStore('users', () => {
    const users = ref<userData[]>([])
    const currentUser = ref<userData>(null)


    const getUsers = async (searchQuery = '') => {
        users.value = await fetchUsers(searchQuery)
    }

    const getUserByToken = async (token: string) => {
        currentUser.value = await fetchUserByToken(token)
    }

    return {
        users,
        currentUser,
        // getUsers,
        getUserByToken,
    }
})
