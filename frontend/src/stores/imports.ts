import { ref } from 'vue'
import { defineStore } from 'pinia'
import { transitionImport } from '../api/imports'
import { disconnectRealtime, subscribeToImportEvents } from '../services/realtime'
import type { ImportRecord } from '../types/import'

interface ImportNotification {
  color: 'success' | 'warning' | 'error'
  message: string
}

const importStatuses = new Set<string>([
  'pending',
  'processing',
  'paused',
  'completed',
  'completed_with_errors',
  'failed',
  'cancelled',
])

function completionNotification(record: ImportRecord): ImportNotification | null {
  if (record.status === 'completed') {
    return {
      color: 'success',
      message: `Importação #${record.id} concluída: ${record.imported_rows} lançamentos importados.`,
    }
  }

  if (record.status === 'completed_with_errors') {
    return {
      color: 'warning',
      message: `Importação #${record.id} concluída com ${record.failed_rows} erro(s).`,
    }
  }

  if (record.status === 'failed') {
    return {
      color: 'error',
      message: `A importação #${record.id} falhou.`,
    }
  }

  return null
}

export const useImportStore = defineStore('imports', () => {
  const currentImport = ref<ImportRecord | null>(null)
  const transitioning = ref(false)
  const notification = ref<ImportNotification | null>(null)
  const completionRevision = ref(0)
  let unsubscribeFromEvents: (() => void) | undefined
  let subscribedUserId: number | undefined

  function track(importRecord: ImportRecord) {
    currentImport.value = importRecord
    notification.value = null
  }

  function listenForUser(userId: number) {
    if (subscribedUserId === userId) return
    unsubscribeFromEvents?.()
    subscribedUserId = userId
    unsubscribeFromEvents = subscribeToImportEvents(userId, (payload) => {
      if (!isImportRecord(payload)) {
        console.error('Received an invalid import notification payload.', payload)
        return
      }

      currentImport.value = payload
      const completion = completionNotification(payload)
      notification.value = completion
      if (completion) completionRevision.value += 1
    })
  }

  async function changeStatus(action: 'pause' | 'resume' | 'cancel') {
    if (!currentImport.value) return
    transitioning.value = true

    try {
      const updatedImport = await transitionImport(currentImport.value.id, action)
      currentImport.value = updatedImport
    } finally {
      transitioning.value = false
    }
  }

  function dismissNotification() {
    notification.value = null
  }

  function clearTracking() {
    unsubscribeFromEvents?.()
    unsubscribeFromEvents = undefined
    subscribedUserId = undefined
    disconnectRealtime()
    currentImport.value = null
    notification.value = null
  }

  function isImportRecord(payload: unknown): payload is ImportRecord {
    if (typeof payload !== 'object' || payload === null) return false

    const record = payload as Partial<ImportRecord>
    return (
      typeof record.id === 'number'
      && typeof record.status === 'string'
      && importStatuses.has(record.status)
      && typeof record.total_rows === 'number'
      && typeof record.processed_rows === 'number'
      && typeof record.imported_rows === 'number'
      && typeof record.failed_rows === 'number'
      && Array.isArray(record.errors)
      && (typeof record.created_at === 'string' || record.created_at === null)
    )
  }

  return {
    currentImport,
    transitioning,
    notification,
    completionRevision,
    track,
    listenForUser,
    changeStatus,
    dismissNotification,
    clearTracking,
  }
})
