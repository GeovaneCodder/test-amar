import { api } from './http'

export interface DashboardSummary {
  income: string
  expenses: string
  balance: string
}

export async function getDashboardSummary() {
  const { data } = await api.get<{ data: DashboardSummary }>('/dashboard')

  return data.data
}
