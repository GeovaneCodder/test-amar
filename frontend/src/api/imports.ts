import type { ImportRecord } from '../types/import'
import { api } from './http'

export async function uploadImport(file: File) {
  const formData = new FormData()
  formData.append('file', file)
  const { data } = await api.post<{ data: ImportRecord }>('/imports', formData)

  return data.data
}

export async function getImport(importId: number) {
  const { data } = await api.get<{ data: ImportRecord }>(`/imports/${importId}`)

  return data.data
}

export async function transitionImport(importId: number, action: 'pause' | 'resume' | 'cancel') {
  await api.post(`/imports/${importId}/${action}`)
  return getImport(importId)
}
