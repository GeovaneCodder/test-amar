import { createApp } from 'vue'
import { createPinia } from 'pinia'
import App from './App.vue'
import router from './router'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { pt } from 'vuetify/locale'
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import './style.css'
import { useAuthStore } from './stores/auth'
import { revokeLegacyBrowserToken } from './api/auth'

revokeLegacyBrowserToken()

const vuetify = createVuetify({
  components,
  directives,
  icons: { defaultSet: 'mdi' },
  locale: {
    locale: 'pt',
    fallback: 'pt',
    messages: { pt },
  },
  theme: {
    defaultTheme: 'amar',
    themes: {
      amar: {
        colors: {
          primary: '#167c68',
          secondary: '#d8a35d',
          background: '#f6f7f4',
          surface: '#ffffff',
          error: '#c34848',
          success: '#25836a',
        },
      },
    },
  },
})

const pinia = createPinia()
const app = createApp(App)

app.use(pinia)
app.use(router)
app.use(vuetify)

window.addEventListener('auth:unauthorized', () => {
  useAuthStore(pinia).clearSession()
  void router.replace('/login')
})

app.mount('#app')
