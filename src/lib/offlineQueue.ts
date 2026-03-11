import { openDB, type IDBPDatabase } from 'idb'

export interface QueuedOperation {
  id?: number
  endpoint: string
  payload: unknown
  timestamp: number
  operationType: string
}

const DB_NAME = 'inasistig-offline-queue'
const DB_VERSION = 1
const STORE_NAME = 'pending-saves'

let dbPromise: Promise<IDBPDatabase> | null = null

function getDb() {
  if (!dbPromise) {
    dbPromise = openDB(DB_NAME, DB_VERSION, {
      upgrade(db) {
        if (!db.objectStoreNames.contains(STORE_NAME)) {
          db.createObjectStore(STORE_NAME, {
            keyPath: 'id',
            autoIncrement: true,
          })
        }
      },
    })
  }
  return dbPromise
}

export async function enqueue(
  op: Omit<QueuedOperation, 'id'>,
): Promise<void> {
  const db = await getDb()
  await db.add(STORE_NAME, op)
}

export async function dequeue(id: number): Promise<void> {
  const db = await getDb()
  await db.delete(STORE_NAME, id)
}

export async function getAllPending(): Promise<QueuedOperation[]> {
  const db = await getDb()
  return db.getAll(STORE_NAME)
}

export async function getPendingCount(): Promise<number> {
  const db = await getDb()
  return db.count(STORE_NAME)
}

export async function processQueue(): Promise<{
  synced: number
  failed: number
}> {
  const pending = await getAllPending()
  let synced = 0
  let failed = 0

  for (const op of pending) {
    try {
      const response = await fetch(op.endpoint, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(op.payload),
      })

      if (response.ok) {
        await dequeue(op.id!)
        synced++
      } else {
        failed++
      }
    } catch {
      failed++
      break
    }
  }

  return { synced, failed }
}
