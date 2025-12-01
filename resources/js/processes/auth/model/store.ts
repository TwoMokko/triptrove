import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import { api } from '@/app/api/api';

export const useAuthStore = defineStore('auth', () => {
    const token = ref<string | null>(localStorage.getItem('token'));
    const tempToken = ref<string | null>(localStorage.getItem('temp_token'));
    const isAuthenticated = computed(() => !!token.value);
    const isVerifying = computed(() => !!tempToken.value);


    const register = async (userData: {
        name: string;
        email: string;
        password: string;
        password_confirmation: string;
    }) => {
        const response = await api.post('/register', userData);

        if (!response.data.temp_token) {
            throw new Error('Temp token не пришел server');
        }

        tempToken.value = response.data.temp_token;
        localStorage.setItem('temp_token', tempToken.value);

        return response.data;
    };

    const verifyEmail = async (verificationData: {
        code: string,
        login: string
    }) => {
        const response = await api.post('/email/verify', verificationData);

        // После успешной верификации сохраняем основной токен
        if (response.data.token) {
            token.value = response.data.token;
            localStorage.setItem('token', token.value);

            // Удаляем временный токен
            tempToken.value = null;
            localStorage.removeItem('temp_token');
        } else {
            throw new Error('Authentication token не пришел после verification');
        }

        return response.data;
    };

    const resendVerificationCode = async (login: string) => {
        const response = await api.post('/email/resend', { login });
        return response.data;
    };

    const login = async (credentials: { login: string; password: string }) => {
        const response = await api.post('/login', credentials);

        if (!response.data.token) {
            throw new Error('Authentication token не пришел');
        }

        token.value = response.data.token;
        localStorage.setItem('token', token.value);

        tempToken.value = null;
        localStorage.removeItem('temp_token');
    };

    const logout = async (silent: boolean = false) => {
        try {
            if (!silent)
                await api.post('/logout');
        } catch (error) {
            if (error.response?.status !== 401) {
                console.warn('Некритическая проблема выхода из системы: ', error.message)
            }
        } finally {
            clearAuthData();
        }
    };

    const clearAuthData = () => {
        token.value = null;
        localStorage.removeItem('token');
        tempToken.value = null;
        localStorage.removeItem('temp_token');
    };

    return {
        token,
        tempToken,
        isAuthenticated,
        isVerifying,
        login,
        logout,
        register,
        verifyEmail,
        resendVerificationCode,
        clearAuthData
    };
});