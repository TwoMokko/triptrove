import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from "../../etities/auth"

import HomePage from '../../pages/home/HomePage.vue'
import ProfilePage from '../../pages/profile/ProfilePage.vue'
import LoginPage from "../../pages/auth/ui/LoginPage.vue"
import RegisterPage from "../../pages/auth/ui/RegisterPage.vue"
import TravelsPage from "../../pages/travels/TravelsPage.vue"
import VerifyPage from "../../pages/auth/ui/VerifyPage.vue"
import NotFoundPage from "../../pages/notFound/notFoundPage.vue"

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
            layout: 'auth',
            guestOnly: true
        }
    },
    {
        name: 'register',
        path: '/register',
        component: RegisterPage,
        meta: {
            layout: 'auth',
            guestOnly: true
        }
    },
    {
        name: 'verify',
        path: '/verify',
        component: VerifyPage,
        meta: {
            layout: 'auth',
            guestOnly: true
        }
    },
    // {
    //     name: 'forbidden',
    //     path: '/forbidden',
    //     component: Компонент "Доступ запрещен"
    // },
    {
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundPage
    },
]

export const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    const authStore = useAuthStore()
    const isAuthenticated = authStore.isAuth

    // Защита страниц только для гостей
    if (to.meta.guestOnly && isAuthenticated) {
        next({ name: 'profile' }) // Перенаправляем в личный кабинет
        return // Важно: прекращаем дальнейшую обработку
    }

    // Защита авторизованных страниц
    if (to.meta.requiresAuth && !isAuthenticated) {
        // Сохраняем исходный маршрут для редиректа после входа
        next({
            name: 'login',
            query: { redirect: to.fullPath }
        })
        return
    }

    // Проверка ролей (если нужно)
    // if (to.meta.role) {
    //     const userRole = authStore.user?.role // Получаем роль из хранилища
    //     if (userRole !== to.meta.role) {
    //         next({ name: 'forbidden' })
    //         return
    //     }
    // }

    // Продолжаем навигацию
    next()
})
