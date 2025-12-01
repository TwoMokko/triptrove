import { defineStore } from "pinia";
import { ref } from "vue";
import { User } from "../../../shared/types/api";
import { api } from "../../../app/api/api";

export const useUsersStore = defineStore('users', () => {
    const currentUser = ref<User | null>(null);

    const fetchCurrentUser = async () => {
        try {
            const response = await api.get('/user');
            currentUser.value = response.data.data.user;
            return currentUser.value;
        } catch (error) {
            // resetCurrentUser();
            throw error;
        }
    };

    const resetCurrentUser = () => {
        currentUser.value = null;
    }

    const fetchUpdateAvatar = async (file) => {
        try {
            const formData = new FormData();
            formData.append('photo', file)
            currentUser.value.avatar = await api.post('/user/avatar', formData);
        }
        catch (err) {
            throw err
        }
    }

    const updateName = async (name: string) => {
        try {
            const response = await api.put('/user/name', { name })

            if (response.data.success && currentUser.value) {
                currentUser.value.name = name
            }

            return response.data
        } catch (err) {
            console.error('Ошибка при обновлении профиля:', err)
            throw err
        }
    }

    const updateLogin = async (login: string) => {
        try {
            const response = await api.put('/user/login', { login })

            if (response.data.success && currentUser.value) {
                currentUser.value.login = login
            }

            return response.data
        } catch (err) {
            console.error('Ошибка при обновлении логина:', err)
            throw err
        }
    }

    return {
        currentUser,
        fetchCurrentUser,
        resetCurrentUser,
        fetchUpdateAvatar,
        updateName,
        updateLogin
    }
})