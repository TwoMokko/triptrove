import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from "../../entities/auth"

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

    // Не блокируем навигацию для auth-страниц даже если есть 401
    if (to.name === 'login' || to.name === 'register' || to.name === 'verify') {
        next()
        return
    }

    // Остальная логика остается прежней
    if (to.meta.guestOnly && isAuthenticated) {
        next({ name: 'profile' })
        return
    }

    if (to.meta.requiresAuth && !isAuthenticated) {
        next({
            name: 'login',
            query: { redirect: to.fullPath }
        })
        return
    }

    next()
})
