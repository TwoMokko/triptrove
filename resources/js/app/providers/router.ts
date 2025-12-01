import { createRouter, createWebHistory, RouteLocationNormalized } from 'vue-router'
import { useAuthStore } from "../../processes/auth/model/store"
import { useUsersStore } from "../../entities/user/model/store";
import HomePage from "../../pages/home/HomePage.vue";
import LoginPage from "../../pages/auth/LoginPage.vue";
import TravelsPage from "../../pages/travel/ui/TravelsPage.vue";
import NotFoundPage from "../../pages/notFoundPage.vue";
import RegisterPage from "../../pages/auth/RegisterPage.vue";
import VerifyPage from "../../pages/auth/VerifyPage.vue";
import ProfilePage from "../../pages/profile/ProfilePage.vue";
import TravelEditPage from "../../pages/travel/ui/TravelEditPage.vue";
import TravelViewPage from "../../pages/travel/ui/TravelViewPage.vue";
import TemplatesPage from "../../pages/travel/ui/TemplatesPage.vue";
import ArchivePage from "../../pages/travel/ui/ArchivePage.vue";



const routes = [
    {
        name: 'home',
        path: '/',
        component: HomePage,
    },
    {
        name: 'login',
        path: '/login',
        component: LoginPage,
        meta: {
            layout: 'auth',
            guestOnly: true
        }
    },{
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
    {
        name: 'travels',
        path: '/travels',
        component: TravelsPage,
        meta: {
            requiresAuth: true,
        }
    },
    { path: '/travels/archive', name: 'travelArchive', component: ArchivePage, meta: { requiresAuth: true } },
    { path: '/travels/templates', name: 'travelTemplates', component: TemplatesPage, meta: { requiresAuth: true } },
    { path: '/travels/:id', name: 'travelView', component: TravelViewPage },
    { path: '/travels/:id/edit', name: 'travelEdit', component: TravelEditPage, meta: { requiresAuth: true } },
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
        path: '/:pathMatch(.*)*',
        name: 'not-found',
        component: NotFoundPage
    },
]

export const router = createRouter({
    history: createWebHistory(),
    routes
})

router.beforeEach(async (to: RouteLocationNormalized) => {
    const authStore = useAuthStore();
    const usersStore = useUsersStore();
    const isAuthenticated = authStore.isAuthenticated;

    // Если токен есть, но пользователь не загружен
    if (isAuthenticated && !usersStore.currentUser) {
        try {
            await usersStore.fetchCurrentUser();
        } catch (error) {
            authStore.clearAuthData();
            usersStore.resetCurrentUser();

            if (to.meta.requiresAuth) {
                return { name: 'login', query: { redirect: to.fullPath } };
            }
        }
    }

    // Страницы только для авторизованных
    if (to.meta.requiresAuth && !isAuthenticated) {
        return {
            name: 'login',
            query: { redirect: to.fullPath }
        };
    }

    // Страницы только для гостей (логин/регистрация)
    if (to.meta.guestOnly && isAuthenticated) {
        return { name: 'home' };
    }

    // Доп. проверка ролей (если нужно)
    // if (to.meta.role && authStore.user?.role !== to.meta.role) {
    //   return { name: 'forbidden' };
    // }
});


