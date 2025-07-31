import '../css/app.css'
import { createApp } from "vue"
import { createPinia } from 'pinia'
import { router } from "./app/providers/router"
import App from './app/App.vue'

createApp(App)
    .use(createPinia())
    .use(router)
    .mount('#root')
