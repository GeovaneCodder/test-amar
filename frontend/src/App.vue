<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import AppHeader from './components/AppHeader.vue'
import { useImportStore } from './stores/imports'

const route = useRoute()
const imports = useImportStore()
const showHeader = computed(() => route.meta.requiresAuth === true)
</script>

<template>
  <v-app>
    <AppHeader v-if="showHeader" />
    <v-main>
      <router-view />
    </v-main>
    <v-snackbar
      :model-value="imports.notification !== null"
      :color="imports.notification?.color"
      location="top right"
      :timeout="8000"
      @update:model-value="(visible) => !visible && imports.dismissNotification()"
    >
      {{ imports.notification?.message }}
      <template #actions>
        <v-btn variant="text" @click="imports.dismissNotification()">Fechar</v-btn>
      </template>
    </v-snackbar>
  </v-app>
</template>
