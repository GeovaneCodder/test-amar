<script setup lang="ts">
import { onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue'
import { createTransaction, listTransactions, type PaginatedTransactions } from '../api/transactions'
import { errorMessage } from '../api/http'
import { useImportStore } from '../stores/imports'
import TransactionTable from '../components/TransactionTable.vue'
import type { TransactionType } from '../types/transaction'

const importStore = useImportStore()
const pageData = ref<PaginatedTransactions>({
  data: [],
  meta: { current_page: 1, last_page: 1, per_page: 20, total: 0 },
})
const filters = reactive({
  search: '',
  type: '' as TransactionType | '',
  date_from: '',
  date_to: '',
})
const loading = ref(false)
const error = ref('')
const dialog = ref(false)
const saving = ref(false)
const formError = ref('')
const today = new Date()
const form = reactive({
  date: `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`,
  description: '',
  amount: '',
  type: 'income' as TransactionType,
})
let latestRequest = 0

async function loadTransactions(page = 1) {
  const requestId = ++latestRequest
  loading.value = true
  error.value = ''

  try {
    pageData.value = await listTransactions({
      page,
      per_page: 20,
      search: filters.search || undefined,
      type: filters.type || undefined,
      date_from: filters.date_from || undefined,
      date_to: filters.date_to || undefined,
    })
    if (requestId !== latestRequest) return
  } catch (requestError) {
    if (requestId !== latestRequest) return
    error.value = errorMessage(requestError, 'Não foi possível carregar os lançamentos.')
  } finally {
    if (requestId === latestRequest) loading.value = false
  }
}

async function submitTransaction() {
  formError.value = ''
  saving.value = true

  try {
    await createTransaction({ ...form })
    dialog.value = false
    form.description = ''
    form.amount = ''
    form.type = 'income'
    await loadTransactions(1)
  } catch (requestError) {
    formError.value = errorMessage(requestError, 'Não foi possível salvar o lançamento.')
  } finally {
    saving.value = false
  }
}

function clearFilters() {
  filters.search = ''
  filters.type = ''
  filters.date_from = ''
  filters.date_to = ''
}

let searchTimer: ReturnType<typeof setTimeout> | undefined
watch(
  () => [filters.search, filters.type, filters.date_from, filters.date_to],
  () => {
    clearTimeout(searchTimer)
    searchTimer = setTimeout(() => void loadTransactions(1), 250)
  },
)

onMounted(() => loadTransactions())
onBeforeUnmount(() => clearTimeout(searchTimer))
watch(() => importStore.completionRevision, () => void loadTransactions(1))
</script>

<template>
  <v-container class="page-container">
    <div class="page-heading">
      <div>
        <p class="eyebrow">MOVIMENTAÇÕES DA SUA CONTA</p>
        <h1>Lançamentos</h1>
        <p>Consulte, filtre e registre suas receitas e despesas.</p>
      </div>
      <v-btn color="primary" prepend-icon="mdi-plus" rounded="lg" class="text-none" @click="dialog = true">
        Novo lançamento
      </v-btn>
    </div>

    <v-alert v-if="error" type="error" variant="tonal" class="mb-5">{{ error }}</v-alert>

    <v-card class="content-card filters-card mb-5" elevation="0">
      <v-row align="center" density="compact">
        <v-col cols="12" md="4">
          <v-text-field
            v-model="filters.search"
            label="Buscar descrição"
            prepend-inner-icon="mdi-magnify"
            variant="outlined"
            density="comfortable"
            hide-details
          />
        </v-col>
        <v-col cols="12" sm="6" md="2">
          <v-select
            v-model="filters.type"
            label="Tipo"
            :items="[
              { title: 'Todos', value: '' },
              { title: 'Receitas', value: 'income' },
              { title: 'Despesas', value: 'expense' },
            ]"
            variant="outlined"
            density="comfortable"
            hide-details
          />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-text-field v-model="filters.date_from" label="De" type="date" variant="outlined" density="comfortable" hide-details />
        </v-col>
        <v-col cols="6" sm="3" md="2">
          <v-text-field v-model="filters.date_to" label="Até" type="date" variant="outlined" density="comfortable" hide-details />
        </v-col>
        <v-col cols="12" md="2">
          <v-btn variant="text" prepend-icon="mdi-filter-off-outline" block class="text-none" @click="clearFilters">
            Limpar filtros
          </v-btn>
        </v-col>
      </v-row>
    </v-card>

    <TransactionTable
      :transactions="pageData.data"
      :loading="loading"
      :page="pageData.meta.current_page"
      :total-pages="pageData.meta.last_page"
      :total="pageData.meta.total"
      @page="loadTransactions"
    />

    <v-dialog v-model="dialog" max-width="520">
      <v-card class="dialog-card">
        <v-card-title>Novo lançamento</v-card-title>
        <v-card-text>
          <v-alert v-if="formError" type="error" variant="tonal" density="compact" class="mb-4">
            {{ formError }}
          </v-alert>
          <v-form @submit.prevent="submitTransaction">
            <v-text-field
              v-model="form.description"
              label="Descrição"
              placeholder="Ex.: Conta de energia"
              variant="outlined"
              :rules="[(value: string) => !!value?.trim() || 'Informe uma descrição.']"
              required
            />
            <v-row>
              <v-col cols="6">
                <v-text-field
                  v-model="form.date"
                  label="Data"
                  type="date"
                  variant="outlined"
                  :rules="[(value: string) => !!value || 'Informe uma data.']"
                  required
                />
              </v-col>
              <v-col cols="6">
                <v-text-field
                  v-model="form.amount"
                  label="Valor (R$)"
                  type="number"
                  min="0.01"
                  step="0.01"
                  prefix="R$"
                  variant="outlined"
                  :rules="[(value: string) => Number(value) > 0 || 'Informe um valor positivo.']"
                  required
                />
              </v-col>
            </v-row>
            <v-select
              v-model="form.type"
              label="Tipo de lançamento"
              :items="[
                { title: 'Receita', value: 'income' },
                { title: 'Despesa', value: 'expense' },
              ]"
              variant="outlined"
            />
            <div class="dialog-actions">
              <v-btn variant="text" class="text-none" @click="dialog = false">Voltar</v-btn>
              <v-btn type="submit" color="primary" :loading="saving" class="text-none">Salvar lançamento</v-btn>
            </div>
          </v-form>
        </v-card-text>
      </v-card>
    </v-dialog>
  </v-container>
</template>

<style scoped>
.filters-card {
  padding: 16px;
}

.dialog-card {
  border-radius: 18px !important;
  padding-top: 8px;
}

.dialog-card .v-card-title {
  color: #263c34;
  font-size: 21px;
  font-weight: 800;
}

.dialog-actions {
  display: flex;
  justify-content: flex-end;
  gap: 8px;
}

.dialog-actions .v-btn {
  text-transform: none;
}
</style>
