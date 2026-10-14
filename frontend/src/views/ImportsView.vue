<script setup lang="ts">
import { ref } from 'vue'
import { errorMessage } from '../api/http'
import { uploadImport } from '../api/imports'
import CsvUpload from '../components/CsvUpload.vue'
import ImportProgress from '../components/ImportProgress.vue'
import { useImportStore } from '../stores/imports'

const imports = useImportStore()
const uploading = ref(false)
const error = ref('')

async function submitFile(file: File) {
  error.value = ''
  uploading.value = true

  try {
    imports.track(await uploadImport(file))
  } catch (requestError) {
    error.value = errorMessage(requestError, 'Não foi possível enviar o arquivo.')
  } finally {
    uploading.value = false
  }
}

async function changeStatus(action: 'pause' | 'resume' | 'cancel') {
  error.value = ''

  try {
    await imports.changeStatus(action)
  } catch (requestError) {
    error.value = errorMessage(requestError, 'Não foi possível atualizar a importação.')
  }
}
</script>

<template>
  <v-container class="page-container">
    <div class="page-heading">
      <div>
        <p class="eyebrow">IMPORTAÇÃO DE DADOS</p>
        <h1>Importar lançamentos</h1>
        <p>Envie um CSV e acompanhe o processamento em tempo real.</p>
      </div>
    </div>

    <v-alert v-if="error" type="error" variant="tonal" closable class="mb-5" @click:close="error = ''">
      {{ error }}
    </v-alert>

    <v-row>
      <v-col cols="12" md="5">
        <CsvUpload :loading="uploading" @upload="submitFile" />
      </v-col>
      <v-col cols="12" md="7">
        <ImportProgress
          v-if="imports.currentImport"
          :import-record="imports.currentImport"
          :loading="imports.transitioning"
          @action="changeStatus"
        />
        <v-card v-else class="content-card empty-import" elevation="0">
          <div class="empty-icon"><v-icon icon="mdi-tray-arrow-down" size="30" /></div>
          <h2>Nenhuma importação por aqui</h2>
          <p>Selecione um arquivo CSV para iniciar. O andamento aparecerá nesta área.</p>
        </v-card>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
.empty-import {
  display: flex;
  min-height: 280px;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 32px;
  text-align: center;
}

.empty-icon {
  display: grid;
  width: 58px;
  height: 58px;
  place-items: center;
  margin-bottom: 16px;
  border-radius: 18px;
  background: #edf3ef;
  color: #167c68;
}

.empty-import h2 {
  margin: 0;
  color: #33483e;
  font-size: 16px;
  font-weight: 800;
}

.empty-import p {
  max-width: 300px;
  margin: 8px 0 0;
  color: #849089;
  font-size: 13px;
  line-height: 1.6;
}
</style>
