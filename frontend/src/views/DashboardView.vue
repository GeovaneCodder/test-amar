<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import { getDashboardSummary, type DashboardSummary } from '../api/dashboard'
import { errorMessage } from '../api/http'
import { listTransactions } from '../api/transactions'
import { useImportStore } from '../stores/imports'
import type { Transaction } from '../types/transaction'
import TransactionTable from '../components/TransactionTable.vue'

const importStore = useImportStore()
const summary = ref<DashboardSummary | null>(null)
const transactions = ref<Transaction[]>([])
const loading = ref(true)
const error = ref('')
let latestRequest = 0

async function loadDashboard() {
  const requestId = ++latestRequest
  loading.value = true
  error.value = ''

  try {
    const [dashboard, latestTransactions] = await Promise.all([
      getDashboardSummary(),
      listTransactions({ per_page: 5 }),
    ])
    if (requestId !== latestRequest) return
    summary.value = dashboard
    transactions.value = latestTransactions.data
  } catch (requestError) {
    if (requestId !== latestRequest) return
    error.value = errorMessage(requestError, 'Não foi possível carregar o painel.')
  } finally {
    if (requestId === latestRequest) loading.value = false
  }
}

function formatCurrency(value: string) {
  return Number(value).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })
}

onMounted(loadDashboard)
watch(() => importStore.completionRevision, () => void loadDashboard())
</script>

<template>
  <v-container class="page-container">
    <div class="page-heading">
      <div>
        <p class="eyebrow">SEU RESUMO FINANCEIRO</p>
        <h1>Visão geral</h1>
        <p>Acompanhe suas movimentações e mantenha tudo em equilíbrio.</p>
      </div>
    </div>

    <v-alert v-if="error" type="error" variant="tonal" closable class="mb-6" @click:close="loadDashboard">
      {{ error }}
    </v-alert>

    <v-row class="mb-5">
      <v-col cols="12" sm="4">
        <v-card class="metric-card" elevation="0">
          <div class="pa-5">
            <div class="metric-icon"><v-icon icon="mdi-trending-up" /></div>
            <div class="metric-label">Receitas</div>
            <div class="metric-value">
              <v-skeleton-loader v-if="loading" type="text" width="150" />
              <template v-else>{{ formatCurrency(summary?.income || '0.00') }}</template>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="metric-card expenses" elevation="0">
          <div class="pa-5">
            <div class="metric-icon"><v-icon icon="mdi-trending-down" /></div>
            <div class="metric-label">Despesas</div>
            <div class="metric-value">
              <v-skeleton-loader v-if="loading" type="text" width="150" />
              <template v-else>{{ formatCurrency(summary?.expenses || '0.00') }}</template>
            </div>
          </div>
        </v-card>
      </v-col>
      <v-col cols="12" sm="4">
        <v-card class="metric-card balance" elevation="0">
          <div class="pa-5">
            <div class="metric-icon"><v-icon icon="mdi-scale-balance" /></div>
            <div class="metric-label">Saldo</div>
            <div class="metric-value">
              <v-skeleton-loader v-if="loading" type="text" width="150" />
              <template v-else>{{ formatCurrency(summary?.balance || '0.00') }}</template>
            </div>
          </div>
        </v-card>
      </v-col>
    </v-row>

    <TransactionTable
      :transactions="transactions"
      :loading="loading"
      :page="1"
      :total-pages="1"
      :total="transactions.length"
      title="Últimos lançamentos"
    />
  </v-container>
</template>
