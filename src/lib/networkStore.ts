import { writable, get } from 'svelte/store'
import { processQueue, getPendingCount } from './offlineQueue'

export const isOnline = writable(
  typeof navigator !== 'undefined' ? navigator.onLine : true,
)
export const pendingCount = writable(0)
export const isSyncing = writable(false)

export async function refreshPendingCount() {
  const count = await getPendingCount()
  pendingCount.set(count)
}

export async function syncPendingOperations() {
  if (get(isSyncing)) return

  const count = await getPendingCount()
  if (count === 0) return

  isSyncing.set(true)
  try {
    const result = await processQueue()
    await refreshPendingCount()

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

if (typeof window !== 'undefined') {
  window.addEventListener('online', () => {
    isOnline.set(true)
    syncPendingOperations()
  })

  window.addEventListener('offline', () => {
    isOnline.set(false)
  })

  refreshPendingCount()
}
