import { createMemoryHistory, createRouter } from 'vue-router'

import HomePage from '../pages/home/HomePage.vue'
import ProfilePage from '../pages/profile/ProfilePage.vue'
import LoginPage from "../pages/login/ui/LoginPage.vue";
import RegisterPage from "../pages/login/ui/RegisterPage.vue";
import LogoutPage from "../pages/login/ui/LogoutPage.vue";

const routes = [
    {
        name: 'home',
        path: '/',
        component: HomePage
    },
    {
        name: 'profile',
        path: '/profile',
        component: ProfilePage,
        meta: { requiresAuth: true/*, role: 'admin'*/ }
    },
    {
        name: 'login',
        path: '/login',
        component: LoginPage
    },
    {
        name: 'register',
        path: '/register',
        component: RegisterPage
    },
    {
        name: 'logout',
        path: '/logout',
        component: LogoutPage
    },
    // {
    //     name: 'forbidden',
    //     path: '/forbidden',
    //     component: Компонент "Доступ запрещен"
    // },
]

export const router = createRouter({
    history: createMemoryHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!localStorage.getItem('auth_token')
    const userRole = localStorage.getItem('user_role') // Получаем роль пользователя

    if (to.meta.requiresAuth && !isAuthenticated) {
        next('/login')
    // } else if (to.meta.role && to.meta.role !== userRole) {
    //     next('/forbidden') // Перенаправляем на страницу "Доступ запрещён"
    } else {
        next()
    }
})
