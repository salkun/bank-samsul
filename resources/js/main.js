import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { useAuthStore } from './stores/useAuthStore'
import router from './router'
import App from './App.vue'

import 'bootstrap/dist/css/bootstrap.min.css'
import 'bootstrap'

const app = createApp(App)
const pinia = createPinia()

app.use(pinia)
app.use(router)

// 🟢 Set & inisialisasi token SEBELUM mount
const auth = useAuthStore(pinia)
auth.init()

app.mount('#app')
