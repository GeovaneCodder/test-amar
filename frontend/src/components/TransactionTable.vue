<script setup lang="ts">
import type { Transaction } from '../types/transaction'

defineProps<{
  transactions: Transaction[]
  loading?: boolean
  page: number
  totalPages: number
  total: number
  title?: string
}>()

const emit = defineEmits<{ page: [number] }>()

function formatDate(value: string) {
  const [year, month, day] = value.split('-')
  return `${day}/${month}/${year}`
}

function formatCurrency(value: string) {
  return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}
</script>

<template>
  <v-card class="content-card" elevation="0">
    <div class="table-heading">
      <div>
        <h2>{{ title || 'Lançamentos' }}</h2>
        <p>{{ total }} {{ total === 1 ? 'registro encontrado' : 'registros encontrados' }}</p>
      </div>
    </div>
    <v-progress-linear v-if="loading" indeterminate color="primary" />
    <v-table class="transaction-table">
      <thead>
        <tr>
          <th>Descrição</th>
          <th>Data</th>
          <th>Tipo</th>
          <th class="text-right">Valor</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="transaction in transactions" :key="transaction.id">
          <td class="description-cell">{{ transaction.description }}</td>
          <td>{{ formatDate(transaction.date) }}</td>
          <td>
            <v-chip
              size="small"
              :color="transaction.type === 'income' ? 'success' : 'deep-orange'"
              variant="tonal"
            >
              {{ transaction.type === 'income' ? 'Receita' : 'Despesa' }}
            </v-chip>
          </td>
          <td
            class="text-right amount-cell"
            :class="transaction.type === 'income' ? 'text-success' : 'text-deep-orange'"
          >
            {{ formatCurrency(transaction.amount) }}
          </td>
        </tr>
        <tr v-if="!loading && transactions.length === 0">
          <td colspan="4" class="empty-row">Nenhum lançamento encontrado.</td>
        </tr>
      </tbody>
    </v-table>
    <div v-if="totalPages > 1" class="table-pagination">
      <span>Página {{ page }} de {{ totalPages }}</span>
      <v-pagination
        :model-value="page"
        :length="totalPages"
        :total-visible="5"
        density="comfortable"
        @update:model-value="emit('page', $event)"
      />
    </div>
  </v-card>
</template>

<style scoped>
.table-heading {
  display: flex;
  justify-content: space-between;
  padding: 22px 24px 15px;
}

.table-heading h2 {
  margin: 0;
  color: #263c34;
  font-size: 17px;
  font-weight: 800;
}

.table-heading p {
  margin: 4px 0 0;
  color: #849089;
  font-size: 12px;
}

.transaction-table th {
  color: #78867e !important;
  font-size: 11px !important;
  font-weight: 700 !important;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

.transaction-table td {
  height: 60px !important;
  color: #596860;
  font-size: 13px;
}

.description-cell {
  color: #293d35 !important;
  font-weight: 600;
}

.amount-cell {
  font-weight: 700;
}

.empty-row {
  height: 130px !important;
  color: #849089 !important;
  text-align: center;
}

.table-pagination {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 10px 24px 16px;
  color: #849089;
  font-size: 12px;
}

@media (max-width: 600px) {
  .table-heading {
    padding-inline: 16px;
  }

  .table-pagination {
    flex-direction: column;
    gap: 8px;
  }
}
</style>
