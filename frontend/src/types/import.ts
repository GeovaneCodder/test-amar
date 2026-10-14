export type ImportStatus =
  | 'pending'
  | 'processing'
  | 'paused'
  | 'completed'
  | 'completed_with_errors'
  | 'failed'
  | 'cancelled'

export interface ImportRowError {
  row: number | null
  message: string
}

export interface ImportRecord {
  id: number
  status: ImportStatus
  total_rows: number
  processed_rows: number
  imported_rows: number
  failed_rows: number
  errors: ImportRowError[]
  created_at: string | null
}
