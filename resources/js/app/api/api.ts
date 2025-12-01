import axios from 'axios';
import { useAuthStore } from "../../processes/auth/model/store";

interface ApiResponse<T = any> {
    data: T;
}
const api: ApiResponse = axios.create({
    baseURL: import.meta.env.VITE_API_URL,
    withCredentials: true, // Для Sanctum кук
    headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
    },
});

// Интерцептор для добавления токена
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    const tempToken = localStorage.getItem('temp_token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    } else if (tempToken) {
        config.headers.Authorization = `Bearer ${tempToken}`;
    }

    // if (token) config.headers.Authorization = `Bearer ${token}`;

    if (config.data instanceof FormData) {
        delete config.headers['Content-Type'];
    }

    return config;
});

// Интерцептор для обработки 401 ошибки
api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token'); // Всё!
        }
        return Promise.reject(error);
    }
);

export { api };