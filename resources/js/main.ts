import '../css/app.css'
import { createApp } from "vue"
import { router } from "./app/router";
import App from './app/App.vue'

createApp(App)
    .use(router)
    .mount('#root')
