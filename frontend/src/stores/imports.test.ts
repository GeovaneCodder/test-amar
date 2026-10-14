import { useImportStore } from './imports'
import { subscribeToImportEvents } from '../services/realtime'
import type { ImportRecord } from '../types/import'

jest.mock('vue', () => ({
  ref: (value: unknown) => ({ value }),
}))

jest.mock('pinia', () => ({
  defineStore: (_id: string, setup: () => Record<string, unknown>) => {
    let store: Record<string, unknown> | undefined

    return () => {
      if (!store) {
        const state = setup()
        store = new Proxy(state, {
          get(target, property) {
            const value = Reflect.get(target, property)
            return value && typeof value === 'object' && 'value' in value ? value.value : value
          },
          set(target, property, value) {
            const current = Reflect.get(target, property)
            if (current && typeof current === 'object' && 'value' in current) {
              current.value = value
              return true
            }

            return Reflect.set(target, property, value)
          },
        })
      }

      return store
    }
  },
}))

jest.mock('../api/imports', () => ({
  transitionImport: jest.fn(),
}))

jest.mock('../services/realtime', () => ({
  subscribeToImportEvents: jest.fn(() => jest.fn()),
  disconnectRealtime: jest.fn(),
}))

const pendingImport: ImportRecord = {
  id: 42,
  status: 'processing',
  total_rows: 10,
  processed_rows: 5,
  imported_rows: 5,
  failed_rows: 0,
  errors: [],
  created_at: '2026-10-13T20:00:00.000Z',
}

describe('CSV import tracking', () => {
  const store = useImportStore()

  beforeEach(() => {
    store.clearTracking()
    jest.mocked(subscribeToImportEvents).mockClear()
  })

  it('announces successful completion received from the current user WebSocket channel', () => {
    const initialRevision = store.completionRevision
    store.listenForUser(7)
    store.track(pendingImport)

    expect(subscribeToImportEvents).toHaveBeenCalledWith(7, expect.any(Function))
    const onFinished = jest.mocked(subscribeToImportEvents).mock.calls[0][1]
    onFinished({
      ...pendingImport,
      status: 'completed',
      processed_rows: 10,
      imported_rows: 10,
    })

    expect(store.currentImport?.status).toBe('completed')
    expect(store.notification).toEqual({
      color: 'success',
      message: 'Importação #42 concluída: 10 lançamentos importados.',
    })
    expect(store.completionRevision).toBe(initialRevision + 1)
  })

  it('announces a WebSocket completion that contains row errors', () => {
    const initialRevision = store.completionRevision
    store.listenForUser(7)
    const onFinished = jest.mocked(subscribeToImportEvents).mock.calls[0][1]
    onFinished({
      ...pendingImport,
      status: 'completed_with_errors',
      processed_rows: 10,
      imported_rows: 8,
      failed_rows: 2,
    })

    expect(store.notification).toEqual({
      color: 'warning',
      message: 'Importação #42 concluída com 2 erro(s).',
    })
    expect(store.completionRevision).toBe(initialRevision + 1)
  })
})
