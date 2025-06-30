import axios from 'axios';

const api = axios.create({
    baseURL: 'http://localhost:8000/api', // Укажите URL вашего Laravel API
    // baseURL: 'http://127.0.0.1:8000/api', // Укажите URL вашего Laravel API
    headers: {
        'Content-Type': 'application/json',
    },
});

export default api;



// import axios from 'axios';
// import { useAuthStore } from "../../entities/auth";
// import {useLogout} from "../../pages/auth/model/logout";
//
// const api = axios.create({
//     baseURL: import.meta.env.VITE_API_URL || 'http://localhost:8000/api',
//     headers: {
//         'Content-Type': 'application/json',
//         'Accept': 'application/json',
//     },
//     // withCredentials: true, // Пока отключите!
// });
//
// // Интерцептор для токена
// api.interceptors.response.use(
//     response => response,
//     error => {
//         const isLogoutRequest = error.config?.__isLogoutRequest;
//         const isAuthRequest = error.config?.url?.includes('/auth');
//
//         // Не обрабатываем 401 для logout и auth-запросов
//         if (error.response?.status === 401 && !isLogoutRequest && !isAuthRequest) {
//             const logout = useLogout();
//             logout.doLogout().then(() => {
//                 // Используем window.location вместо router, чтобы избежать цикла
//                 window.location.href = '/login?session_expired=1';
//             });
//         }
//         return Promise.reject(error);
//     }
// );
// // Обработчик ошибок
// api.interceptors.response.use(
//     response => response,
//     error => {
//         const isLogoutRequest = error.config?.__isLogoutRequest
//         if (error.response?.status === 401 && !isLogoutRequest) {
//             const logout = useLogout();
//             logout.doLogout().then(() => window.location.href = '/login');
//
//         }
//         return Promise.reject(error);
//     }
// );
//
// export default api;

