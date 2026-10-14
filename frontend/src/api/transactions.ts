import type { Transaction, TransactionType } from '../types/transaction'
import { api } from './http'

export interface TransactionFilters {
  page?: number
  per_page?: number
  type?: TransactionType | ''
  date_from?: string
  date_to?: string
  search?: string
}

export interface PaginatedTransactions {
  data: Transaction[]
  meta: {
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
}

export interface NewTransaction {
  date: string
  description: string
  amount: string
  type: TransactionType
}

export async function listTransactions(filters: TransactionFilters = {}) {
  const { data } = await api.get<PaginatedTransactions>('/transactions', { params: filters })

  return data
}

export async function createTransaction(transaction: NewTransaction) {
  const { data } = await api.post<{ data: Transaction }>('/transactions', transaction)

  return data.data
}
