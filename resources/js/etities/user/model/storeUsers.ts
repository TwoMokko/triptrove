import { defineStore } from 'pinia'
import { ref } from 'vue'
import {queryUpdateName, queryUserByToken, queryUsers, uploadPhoto} from '../api/users'
import { userData } from "../../../app/types/types"
import { useAuthStore } from "../../auth"

export const useUsersStore = defineStore('users', () => {
    const users = ref<userData[]>([])
    const currentUser = ref<userData>(null)

    const authStore = useAuthStore()

    const getUsers = async (searchQuery = '') => {
        users.value = await queryUsers(searchQuery)
    }

    const getUserByToken = async (token: string) => {
        currentUser.value = await queryUserByToken(token)
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

    const updateName = async (newName: string) => {
        try {
            console.log('before', currentUser.value.name)
            const result = await queryUpdateName(newName, authStore.token)
            // currentUser.value.name = result.user.name
            // console.log('after', currentUser.value.name)
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
        updateName,
    }
})
