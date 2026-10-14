<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { errorMessage } from '../api/http'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const router = useRouter()
const menu = ref(false)
const logoutError = ref('')

const links = [
  { title: 'Visão geral', to: '/dashboard', icon: 'mdi-view-dashboard-outline' },
  { title: 'Lançamentos', to: '/transactions', icon: 'mdi-swap-horizontal' },
  { title: 'Importar CSV', to: '/imports', icon: 'mdi-file-upload-outline' },
]

async function logout() {
  logoutError.value = ''
  try {
    await auth.signOut()
    await router.replace('/login')
  } catch (requestError) {
    logoutError.value = errorMessage(requestError, 'Não foi possível encerrar a sessão.')
    await router.replace({ path: '/login', query: { logoutError: logoutError.value } })
  }
}
</script>

<template>
  <v-app-bar class="app-toolbar" elevation="0">
    <v-container class="toolbar-content d-flex align-center" fluid>
      <router-link to="/dashboard" class="brand-lockup toolbar-brand">
        <span>amar assist</span>
      </router-link>

      <nav class="main-nav">
        <v-btn
          v-for="link in links"
          :key="link.to"
          :to="link.to"
          :prepend-icon="link.icon"
          :aria-label="link.title"
          variant="text"
          color="primary"
          class="nav-link"
        >
          {{ link.title }}
        </v-btn>
      </nav>

      <v-spacer />
      <v-menu v-model="menu" location="bottom end">
        <template #activator="{ props }">
          <v-btn v-bind="props" variant="text" class="user-menu">
            <v-avatar color="primary" size="34" class="mr-2">
              <span class="text-white">{{ auth.user?.name?.charAt(0).toUpperCase() || 'U' }}</span>
            </v-avatar>
            <span class="user-name">{{ auth.user?.name }}</span>
            <v-icon icon="mdi-chevron-down" />
          </v-btn>
        </template>
        <v-list min-width="210" rounded="lg">
          <v-list-item :title="auth.user?.name" :subtitle="auth.user?.email" />
          <v-divider />
          <v-list-item
            prepend-icon="mdi-logout"
            title="Sair da conta"
            @click="logout"
          />
        </v-list>
      </v-menu>
    </v-container>
  </v-app-bar>
</template>
