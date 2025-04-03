import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from "../../etities/auth"

import HomePage from '../../pages/home/HomePage.vue'
import ProfilePage from '../../pages/profile/ProfilePage.vue'
import LoginPage from "../../pages/auth/ui/LoginPage.vue"
import RegisterPage from "../../pages/auth/ui/RegisterPage.vue"
import TravelsPage from "../../pages/travels/TravelsPage.vue"
import VerifyPage from "../../pages/auth/ui/VerifyPage.vue"

const routes = [
    {
        name: 'home',
        path: '/',
        component: HomePage,
    },
    {
        name: 'travels',
        path: '/travels',
        component: TravelsPage,
        meta: {
            requiresAuth: true,
            /* role: 'admin'*/
        }
    },
    {
        name: 'profile',
        path: '/profile',
        component: ProfilePage,
        meta: {
            requiresAuth: true,
            /* role: 'admin'*/
        }
    },
    {
        name: 'login',
        path: '/login',
        component: LoginPage,
        meta: {
            layout: 'auth'
        }
    },
    {
        name: 'register',
        path: '/register',
        component: RegisterPage,
        meta: {
            layout: 'auth'
        }
    },
    {
        name: 'verify',
        path: '/verify',
        component: VerifyPage,
        meta: {
            layout: 'auth'
        }
    },
    // {
    //     name: 'forbidden',
    //     path: '/forbidden',
    //     component: Компонент "Доступ запрещен"
    // },
]

export const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const userRole = localStorage.getItem('user_role') // Получаем роль пользователя
    const authStore = useAuthStore()
    if (to.meta.requiresAuth && !authStore.isAuth) {
        next('/login')
    // } else if (to.meta.role && to.meta.role !== userRole) {
    //     next('/forbidden') // Перенаправляем на страницу "Доступ запрещён"
    } else {
        next()
    }
})
