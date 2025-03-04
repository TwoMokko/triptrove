import { createMemoryHistory, createRouter } from 'vue-router'

import HomePage from '../pages/HomePage.vue'
import ProfilePage from '../pages/ProfilePage.vue'

const routes = [
    { path: '/', component: HomePage },
    { path: '/profile', component: ProfilePage }
]

export const router = createRouter({
    history: createMemoryHistory(),
    routes
})
