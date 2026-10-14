<script setup lang="ts">
import { ref } from 'vue'

defineProps<{ loading?: boolean }>()
const emit = defineEmits<{ upload: [File] }>()
const selectedFile = ref<File | null>(null)
const error = ref('')

function sendFile() {
  error.value = ''
  const file = selectedFile.value

  if (!file) {
    error.value = 'Selecione um arquivo para continuar.'
    return
  }

  if (!/\.(csv|txt)$/i.test(file.name)) {
    error.value = 'O arquivo deve estar no formato CSV.'
    return
  }

  if (file.size > 10 * 1024 * 1024) {
    error.value = 'O arquivo deve ter no máximo 10 MB.'
    return
  }

  emit('upload', file)
}
</script>

<template>
  <v-card class="content-card upload-card" elevation="0">
    <div class="upload-icon">
      <v-icon icon="mdi-file-delimited-outline" size="30" />
    </div>
    <h2>Importe seu arquivo CSV</h2>
    <p>Envie suas movimentações para cadastrar tudo de uma vez.</p>

    <v-alert v-if="error" type="error" variant="tonal" density="compact" class="mb-4">
      {{ error }}
    </v-alert>

    <v-file-input
      v-model="selectedFile"
      accept=".csv,text/csv,.txt,text/plain"
      label="Escolher arquivo"
      prepend-icon=""
      prepend-inner-icon="mdi-paperclip"
      variant="outlined"
      density="comfortable"
      show-size
      :disabled="loading"
      hide-details="auto"
      class="file-input"
      @update:model-value="error = ''"
    />
    <v-btn
      color="primary"
      prepend-icon="mdi-upload"
      size="large"
      :loading="loading"
      class="upload-button"
      @click="sendFile"
    >
      Enviar e importar
    </v-btn>

    <div class="format-help">
      <strong>Formato esperado</strong>
      <code>date,description,amount,type</code>
      <span>Tipos aceitos: income, expense, Receita ou Despesa. Tamanho máximo: 10 MB.</span>
    </div>
  </v-card>
</template>

<style scoped>
.upload-card {
  padding: 28px;
}

.upload-icon {
  display: grid;
  width: 54px;
  height: 54px;
  place-items: center;
  margin-bottom: 18px;
  border-radius: 17px;
  background: #eaf4ef;
  color: #167c68;
}

h2 {
  margin: 0;
  color: #263c34;
  font-size: 19px;
  font-weight: 800;
}

p {
  margin: 8px 0 22px;
  color: #74817b;
  font-size: 13px;
}

.upload-button {
  width: 100%;
  margin-top: 16px;
  border-radius: 11px;
  font-weight: 700;
  text-transform: none;
}

.format-help {
  display: flex;
  flex-direction: column;
  gap: 7px;
  margin-top: 24px;
  padding-top: 20px;
  border-top: 1px solid #edf0ed;
  color: #77847d;
  font-size: 12px;
  line-height: 1.5;
}

.format-help strong {
  color: #3a4c44;
}

.format-help code {
  width: fit-content;
  padding: 5px 8px;
  border-radius: 6px;
  background: #f3f6f3;
  color: #167c68;
  font-size: 11px;
}
</style>
