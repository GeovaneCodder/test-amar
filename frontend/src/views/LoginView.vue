<script setup lang="ts">
import { ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { errorMessage } from '../api/http'
import { useAuthStore } from '../stores/auth'

const auth = useAuthStore()
const route = useRoute()
const router = useRouter()
const isRegister = ref(false)
const name = ref('')
const email = ref('')
const password = ref('')
const passwordConfirmation = ref('')
const visiblePassword = ref(false)
const loading = ref(false)
const error = ref(typeof route.query.logoutError === 'string' ? route.query.logoutError : '')

async function submit() {
  error.value = ''
  loading.value = true

  try {
    if (isRegister.value) {
      await auth.signUp(name.value.trim(), email.value.trim(), password.value, passwordConfirmation.value)
    } else {
      await auth.signIn(email.value.trim(), password.value)
    }

    const redirect = typeof route.query.redirect === 'string' ? route.query.redirect : '/dashboard'
    await router.replace(redirect)
  } catch (requestError) {
    error.value = errorMessage(
      requestError,
      isRegister.value ? 'Não foi possível criar sua conta.' : 'Não foi possível entrar.',
    )
  } finally {
    loading.value = false
  }
}
</script>

<template>
  <main class="login-page">
    <section class="login-panel">
      <v-card class="login-card" elevation="0">
        <p class="eyebrow login-eyebrow">{{ isRegister ? 'COMECE POR AQUI' : 'BEM-VINDA DE VOLTA' }}</p>
        <h2>{{ isRegister ? 'Crie sua conta' : 'Acesse sua conta' }}</h2>
        <p class="login-subtitle">
          {{
            isRegister
              ? 'Preencha seus dados para começar a organizar suas finanças.'
              : 'Entre para acompanhar sua vida financeira.'
          }}
        </p>

        <v-alert v-if="error" type="error" variant="tonal" class="mb-5" density="compact">
          {{ error }}
        </v-alert>

        <v-form @submit.prevent="submit">
          <v-text-field
            v-if="isRegister"
            v-model="name"
            label="Nome completo"
            autocomplete="name"
            variant="outlined"
            :rules="[(value: string) => !!value?.trim() || 'Informe seu nome.']"
            required
          />
          <v-text-field
            v-model="email"
            label="E-mail"
            type="email"
            autocomplete="email"
            variant="outlined"
            :rules="[(value: string) => !!value || 'Informe seu e-mail.']"
            required
          />
          <v-text-field
            v-model="password"
            label="Senha"
            :type="visiblePassword ? 'text' : 'password'"
            :append-inner-icon="visiblePassword ? 'mdi-eye-off-outline' : 'mdi-eye-outline'"
            :autocomplete="isRegister ? 'new-password' : 'current-password'"
            variant="outlined"
            :rules="[
              (value: string) => !!value || 'Informe sua senha.',
              (value: string) => !isRegister || value.length >= 8 || 'Use pelo menos 8 caracteres.',
            ]"
            required
            @click:append-inner="visiblePassword = !visiblePassword"
          />
          <v-text-field
            v-if="isRegister"
            v-model="passwordConfirmation"
            label="Confirme sua senha"
            type="password"
            autocomplete="new-password"
            variant="outlined"
            :rules="[(value: string) => value === password || 'As senhas não correspondem.']"
            required
          />
          <v-btn
            type="submit"
            color="primary"
            size="large"
            block
            :loading="loading"
            class="login-submit"
          >
            {{ isRegister ? 'Criar conta' : 'Entrar' }}
          </v-btn>
        </v-form>

        <p class="login-switch">
          {{ isRegister ? 'Já tem uma conta?' : 'Ainda não tem uma conta?' }}
          <button type="button" @click="isRegister = !isRegister; error = ''">
            {{ isRegister ? 'Faça login' : 'Criar conta' }}
          </button>
        </p>
      </v-card>
    </section>
  </main>
</template>
