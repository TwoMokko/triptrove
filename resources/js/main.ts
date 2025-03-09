import '../css/app.css'
import { createApp } from "vue"
import { router } from "./app/router"
import { createPinia } from 'pinia'
import App from './app/App.vue'

createApp(App)
    .use(createPinia())
    .use(router)
    .mount('#root')
