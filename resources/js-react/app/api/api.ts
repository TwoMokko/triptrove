import axios from 'axios';

const api = axios.create({
    baseURL: 'https://trip-trove.ru/api', // Укажите URL вашего Laravel API
    // baseURL: 'http://localhost:8000/api', // Укажите URL вашего Laravel API
    // baseURL: 'http://127.0.0.1:8000/api', // Укажите URL вашего Laravel API
    headers: {
        'Content-Type': 'application/json',
    },
});

export default api;
