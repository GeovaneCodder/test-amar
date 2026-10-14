export type TransactionType = 'income' | 'expense'

export interface Transaction {
  id: number
  date: string
  description: string
  amount: string
  type: TransactionType
  created_at: string | null
}
