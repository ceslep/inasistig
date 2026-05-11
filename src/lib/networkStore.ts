import { writable, get } from 'svelte/store'
import { processQueue, getPendingCount, getPendingSummary, clearAllPending, type PendingOperationSummary } from './offlineQueue'

export const isOnline = writable(
  typeof navigator !== 'undefined' ? navigator.onLine : true,
)
export const pendingCount = writable(0)
export const pendingOperations = writable<PendingOperationSummary[]>([])
export const isSyncing = writable(false)

export async function refreshPendingCount() {
  const count = await getPendingCount()
  pendingCount.set(count)
}

export async function refreshPendingOperations() {
  const ops = await getPendingSummary()
  pendingOperations.set(ops)
}

export async function syncPendingOperations() {
  if (get(isSyncing)) return

  const count = await getPendingCount()
  if (count === 0) return

  isSyncing.set(true)
  try {
    const result = await processQueue()
    await refreshPendingCount()
    await refreshPendingOperations()

    if (result.synced > 0) {
      window.dispatchEvent(
        new CustomEvent('pwa-sync-complete', {
          detail: { synced: result.synced, failed: result.failed },
        }),
      )
    }
  } finally {
    isSyncing.set(false)
  }
}

export async function discardPendingOperation(id: number) {
  const { dequeue } = await import('./offlineQueue')
  await dequeue(id)
  await refreshPendingCount()
  await refreshPendingOperations()
}

export async function discardAllPending() {
  await clearAllPending()
  await refreshPendingCount()
  await refreshPendingOperations()
}

if (typeof window !== 'undefined') {
  window.addEventListener('online', () => {
    isOnline.set(true)
    syncPendingOperations()
  })

  window.addEventListener('offline', () => {
    isOnline.set(false)
  })

  refreshPendingCount()
  refreshPendingOperations()
}
