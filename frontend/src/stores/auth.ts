import { computed, ref } from 'vue'
import { defineStore } from 'pinia'
import axios from 'axios'
import {
  getCurrentUser,
  login as loginRequest,
  logout as logoutRequest,
  register as registerRequest,
  type AuthUser,
} from '../api/auth'
import { useImportStore } from './imports'

export const useAuthStore = defineStore('auth', () => {
  const user = ref<AuthUser | null>(null)
  const initialized = ref(false)
  const isAuthenticated = computed(() => user.value !== null)

  function clearSession() {
    user.value = null
    useImportStore().clearTracking()
  }

  function listenForImportNotifications() {
    if (user.value) useImportStore().listenForUser(user.value.id)
  }

  async function initialize() {
    if (initialized.value) return
    initialized.value = true

    try {
      user.value = await getCurrentUser()
      listenForImportNotifications()
    } catch (error) {
      if (axios.isAxiosError(error) && error.response?.status === 401) {
        clearSession()
        return
      }

      initialized.value = false
      throw error
    }
  }

  async function signIn(email: string, password: string) {
    user.value = await loginRequest(email, password)
    initialized.value = true
    listenForImportNotifications()
  }

  async function signUp(name: string, email: string, password: string, passwordConfirmation: string) {
    user.value = await registerRequest(name, email, password, passwordConfirmation)
    initialized.value = true
    listenForImportNotifications()
  }

  async function signOut() {
    try {
      await logoutRequest()
    } finally {
      clearSession()
      initialized.value = true
    }
  }

  return {
    user,
    isAuthenticated,
    initialize,
    signIn,
    signUp,
    signOut,
    clearSession,
  }
})
