<script setup lang="ts">
import type { ImportRecord, ImportStatus } from '../types/import'
import { translateApiMessage } from '../api/http'

defineProps<{
  importRecord: ImportRecord
  loading?: boolean
}>()

const emit = defineEmits<{ action: ['pause' | 'resume' | 'cancel'] }>()

const statusLabels: Record<ImportStatus, string> = {
  pending: 'Na fila',
  processing: 'Processando',
  paused: 'Pausada',
  completed: 'Concluída',
  completed_with_errors: 'Concluída com erros',
  failed: 'Falhou',
  cancelled: 'Cancelada',
}

const statusColors: Record<ImportStatus, string> = {
  pending: 'info',
  processing: 'primary',
  paused: 'warning',
  completed: 'success',
  completed_with_errors: 'warning',
  failed: 'error',
  cancelled: 'secondary',
}

</script>

<template>
  <v-card class="content-card progress-card" elevation="0">
    <div class="progress-heading">
      <div class="progress-file-icon"><v-icon icon="mdi-file-delimited-outline" /></div>
      <div class="progress-title">
        <h2>Importação #{{ importRecord.id }}</h2>
        <p>{{ importRecord.created_at ? new Date(importRecord.created_at).toLocaleString('pt-BR') : 'Enviada agora' }}</p>
      </div>
      <v-chip :color="statusColors[importRecord.status]" size="small" variant="tonal">
        {{ statusLabels[importRecord.status] }}
      </v-chip>
    </div>

    <div class="progress-body">
      <div class="progress-stats">
        <span>Linhas processadas</span>
        <strong>
          {{ importRecord.processed_rows }}
          <template v-if="importRecord.total_rows > 0"> de {{ importRecord.total_rows }}</template>
        </strong>
      </div>
      <v-progress-linear
        :model-value="importRecord.total_rows ? (importRecord.processed_rows / importRecord.total_rows) * 100 : 8"
        :indeterminate="importRecord.status === 'pending' || (importRecord.status === 'processing' && !importRecord.total_rows)"
        color="primary"
        rounded
        height="8"
      />
      <div class="result-counts">
        <span class="text-success">{{ importRecord.imported_rows }} importadas</span>
        <span class="text-error">{{ importRecord.failed_rows }} com erro</span>
      </div>
    </div>

    <div v-if="importRecord.errors.length" class="error-list">
      <h3>Detalhes dos erros</h3>
      <v-alert
        v-for="(item, index) in importRecord.errors"
        :key="`${item.row}-${index}`"
        type="warning"
        variant="tonal"
        density="compact"
        class="mb-2"
      >
        <strong v-if="item.row">Linha {{ item.row }}: </strong>{{ translateApiMessage(item.message) }}
      </v-alert>
    </div>

    <div
      v-if="['pending', 'processing', 'paused'].includes(importRecord.status)"
      class="progress-actions"
    >
      <v-btn
        v-if="importRecord.status === 'processing'"
        variant="outlined"
        prepend-icon="mdi-pause"
        :loading="loading"
        @click="emit('action', 'pause')"
      >
        Pausar
      </v-btn>
      <v-btn
        v-if="importRecord.status === 'paused'"
        color="primary"
        variant="tonal"
        prepend-icon="mdi-play"
        :loading="loading"
        @click="emit('action', 'resume')"
      >
        Retomar
      </v-btn>
      <v-btn
        color="error"
        variant="text"
        prepend-icon="mdi-close-circle-outline"
        :loading="loading"
        @click="emit('action', 'cancel')"
      >
        Cancelar importação
      </v-btn>
    </div>
  </v-card>
</template>

<style scoped>
.progress-card {
  padding: 22px 24px;
}

.progress-heading {
  display: flex;
  align-items: center;
  gap: 14px;
}

.progress-file-icon {
  display: grid;
  width: 44px;
  height: 44px;
  place-items: center;
  border-radius: 13px;
  background: #eaf4ef;
  color: #167c68;
}

.progress-title {
  flex: 1;
}

.progress-title h2 {
  margin: 0;
  color: #263c34;
  font-size: 15px;
  font-weight: 800;
}

.progress-title p {
  margin: 3px 0 0;
  color: #849089;
  font-size: 11px;
}

.progress-body {
  margin-top: 24px;
}

.progress-stats,
.result-counts {
  display: flex;
  justify-content: space-between;
  margin-bottom: 8px;
  color: #74817b;
  font-size: 12px;
}

.progress-stats strong {
  color: #33483e;
}

.result-counts {
  margin-top: 10px;
  margin-bottom: 0;
}

.error-list {
  margin-top: 20px;
}

.error-list h3 {
  margin-bottom: 10px;
  color: #56645d;
  font-size: 12px;
}

.progress-actions {
  display: flex;
  flex-wrap: wrap;
  gap: 8px;
  margin-top: 20px;
  padding-top: 16px;
  border-top: 1px solid #edf0ed;
}

.progress-actions .v-btn {
  text-transform: none;
}
</style>
