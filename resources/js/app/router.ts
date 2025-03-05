import { createMemoryHistory, createRouter } from 'vue-router'

import HomePage from '../pages/home/HomePage.vue'
import ProfilePage from '../pages/profile/ProfilePage.vue'
import LoginPage from "../pages/login/ui/LoginPage.vue";
import RegisterPage from "../pages/login/ui/RegisterPage.vue";
import LogoutPage from "../pages/login/ui/LogoutPage.vue";

const routes = [
    { path: '/', component: HomePage },
    { path: '/profile', component: ProfilePage },
    { path: '/login', component: LoginPage },
    { path: '/register', component: RegisterPage },
    { path: '/logout', component: LogoutPage },
]

export const router = createRouter({
    history: createMemoryHistory(),
    routes
})

router.beforeEach((to, from, next) => {
    if (to.meta.requiresAuth && !localStorage.getItem('auth_token')) {
        next('/login')
    } else {
        next()
    }
})
