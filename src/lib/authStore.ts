import { writable, derived } from 'svelte/store'

export interface GoogleUser {
  email: string
  name: string
  picture: string
  sub: string
  exp: number
  credential: string
}

interface JwtPayload {
  email: string
  name: string
  picture: string
  sub: string
  exp: number
}

const STORAGE_KEY = 'google_auth_user'
const DOCENTE_NAME_KEY = 'google_auth_docente'
const EXPIRY_CHECK_INTERVAL = 5 * 60 * 1000 // 5 minutes

function decodeJwt(token: string): JwtPayload {
  const base64Url = token.split('.')[1]
  const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/')
  const jsonPayload = decodeURIComponent(
    atob(base64)
      .split('')
      .map((c) => '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2))
      .join(''),
  )
  return JSON.parse(jsonPayload) as JwtPayload
}

function isTokenExpired(exp: number): boolean {
  return Date.now() >= exp * 1000
}

function loadStoredUser(): GoogleUser | null {
  if (typeof window === 'undefined') return null
  try {
    const stored = localStorage.getItem(STORAGE_KEY)
    if (!stored) return null
    const user = JSON.parse(stored) as GoogleUser
    if (isTokenExpired(user.exp)) {
      localStorage.removeItem(STORAGE_KEY)
      return null
    }
    return user
  } catch {
    localStorage.removeItem(STORAGE_KEY)
    return null
  }
}

export const authUser = writable<GoogleUser | null>(loadStoredUser())

export const isAuthenticated = derived(authUser, ($user) => $user !== null)

// Docente name store — persisted in localStorage
function loadDocenteName(): string {
  if (typeof window === 'undefined') return ''
  return localStorage.getItem(DOCENTE_NAME_KEY) || ''
}

export const docenteName = writable<string>(loadDocenteName())

export function setDocenteName(name: string): void {
  localStorage.setItem(DOCENTE_NAME_KEY, name)
  docenteName.set(name)
}

export function getDocenteName(): string {
  return localStorage.getItem(DOCENTE_NAME_KEY) || ''
}

export function findMatchingDocente(docentes: string[], name: string): string | null {
  if (!name) return null
  const normalized = name.toLowerCase().trim()
  const exact = docentes.find(d => d.toLowerCase().trim() === normalized)
  if (exact) return exact
  const partial = docentes.find(d =>
    d.toLowerCase().replace(/-\d+$/, '').trim() === normalized,
  )
  if (partial) return partial
  return null
}

export function signIn(credential: string): void {
  const payload = decodeJwt(credential)
  const user: GoogleUser = {
    email: payload.email,
    name: payload.name,
    picture: payload.picture,
    sub: payload.sub,
    exp: payload.exp,
    credential,
  }
  localStorage.setItem(STORAGE_KEY, JSON.stringify(user))
  authUser.set(user)
}

export function signOut(): void {
  localStorage.removeItem(STORAGE_KEY)
  localStorage.removeItem(DOCENTE_NAME_KEY)
  authUser.set(null)
  docenteName.set('')
  if (typeof google !== 'undefined' && google?.accounts?.id) {
    google.accounts.id.disableAutoSelect()
  }
}

// Periodic token expiration check
let expiryInterval: ReturnType<typeof setInterval> | null = null

function checkExpiry(): void {
  const stored = localStorage.getItem(STORAGE_KEY)
  if (!stored) return
  try {
    const user = JSON.parse(stored) as GoogleUser
    if (isTokenExpired(user.exp)) {
      signOut()
    }
  } catch {
    signOut()
  }
}

if (typeof window !== 'undefined') {
  expiryInterval = setInterval(checkExpiry, EXPIRY_CHECK_INTERVAL)

  document.addEventListener('visibilitychange', () => {
    if (document.visibilityState === 'visible') {
      checkExpiry()
    }
  })
}
