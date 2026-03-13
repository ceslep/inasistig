/**
 * analyticsService.ts - Servicio de recopilación de estadísticas de uso
 */

import { ANALYTICS_URL } from "../constants";
import { APP_VERSION } from "../version";

// Interfaces
interface NetworkInfo {
  effectiveType?: string;
  downlink?: number;
}

interface AnalyticsEvent {
  session_id: string;
  id_docente: string | null;
  event_type: string;
  event_data?: Record<string, unknown>;
  app_version: string;
  user_agent: string;
  platform: string;
  language: string;
  screen_width: number;
  screen_height: number;
  viewport_width: number;
  viewport_height: number;
  connection_type: string | null;
  connection_downlink: number | null;
  is_online: boolean;
  timezone: string;
  referrer: string;
}

interface EventsByType {
  event_type: string;
  count: number;
}

interface ViewCount {
  view_name: string;
  count: number;
}

interface PlatformCount {
  platform: string;
  count: number;
}

interface HourActivity {
  hora: number;
  count: number;
}

interface DayActivity {
  fecha: string;
  count: number;
}

interface RecentEvent {
  event_type: string;
  event_data: string | null;
  created_at: string;
}

export interface SessionDetail {
  session_id: string;
  id_docente: string | null;
  started_at: string;
  last_activity: string;
  duration_seconds: number;
  page_views: number;
  events_count: number;
  app_version: string | null;
  platform: string | null;
  ip_address: string | null;
}

export interface AnalyticsData {
  total_events: number;
  total_sessions: number;
  avg_session_duration: number;
  unique_docentes: number;
  events_by_type: EventsByType[];
  top_views: ViewCount[];
  platforms: PlatformCount[];
  activity_by_hour: HourActivity[];
  activity_by_day: DayActivity[];
  recent_events: RecentEvent[];
  all_sessions: SessionDetail[];
}

export interface ClientInfo {
  session_id: string;
  app_version: string;
  user_agent: string;
  platform: string;
  language: string;
  screen: string;
  viewport: string;
  connection_type: string;
  connection_speed: string;
  timezone: string;
  is_online: boolean;
  memory: string;
  cores: number;
  touch: boolean;
  pwa_installed: boolean;
  referrer: string;
  session_start: string;
  session_duration: string;
}

let sessionId: string = "";
let sessionStart: number = Date.now();
let eventQueue: AnalyticsEvent[] = [];
let flushTimer: ReturnType<typeof setTimeout> | null = null;

function generateSessionId(): string {
  return `${Date.now()}-${Math.random().toString(36).substring(2, 11)}`;
}

function getSessionId(): string {
  if (!sessionId) {
    const stored = sessionStorage.getItem("analytics_session_id");
    if (stored) {
      sessionId = stored;
      const storedStart = sessionStorage.getItem("analytics_session_start");
      if (storedStart) sessionStart = parseInt(storedStart, 10);
    } else {
      sessionId = generateSessionId();
      sessionStart = Date.now();
      sessionStorage.setItem("analytics_session_id", sessionId);
      sessionStorage.setItem("analytics_session_start", sessionStart.toString());
    }
  }
  return sessionId;
}

const DOCENTE_MATERIAS_KEYS = [
  "docenteMaterias",
  "docenteMateriasDiario",
];

const LAST_DOCENTE_KEYS = ["lastDocente", "lastDocenteDiario"];

function normalizeDocente(docente: unknown): string | null {
  if (typeof docente !== "string") return null;
  const normalized = docente.trim();
  return normalized.length === 0 ? null : normalized;
}

function addDocenteCount(counts: Map<string, number>, docente: string, value: number): void {
  const previous = counts.get(docente) ?? 0;
  counts.set(docente, previous + value);
}

function collectDocenteCounts(): Map<string, number> {
  const counts = new Map<string, number>();

  if (typeof window === "undefined" || typeof localStorage === "undefined") {
    return counts;
  }

  for (const key of DOCENTE_MATERIAS_KEYS) {
    try {
      const raw = localStorage.getItem(key);
      if (!raw) continue;
      const parsed = JSON.parse(raw);
      if (!parsed || typeof parsed !== "object") continue;
      for (const [docenteKey, value] of Object.entries(parsed)) {
        const docente = normalizeDocente(docenteKey);
        if (!docente) continue;
        let weight = 0;
        if (Array.isArray(value)) {
          weight = value.length;
        } else if (value && typeof value === "object") {
          weight = Object.keys(value).length;
        } else if (typeof value === "string") {
          weight = value.trim().length > 0 ? 1 : 0;
        }
        addDocenteCount(counts, docente, Math.max(weight, 1));
      }
    } catch {
      continue;
    }
  }

  for (const key of LAST_DOCENTE_KEYS) {
    try {
      const doc = normalizeDocente(localStorage.getItem(key));
      if (!doc) continue;
      addDocenteCount(counts, doc, 1);
    } catch {
      continue;
    }
  }

  return counts;
}

function getDocente(): string | null {
  try {
    const counts = collectDocenteCounts();
    let winner: string | null = null;
    let bestCount = 0;
    counts.forEach((value, docente) => {
      if (value > bestCount) {
        bestCount = value;
        winner = docente;
      }
    });
    return winner;
  } catch {
    return null;
  }
}

function getNetworkInfo(): NetworkInfo {
  const nav = navigator as Navigator & { connection?: { effectiveType?: string; downlink?: number } };
  return nav.connection || {};
}

function buildEvent(eventType: string, eventData?: Record<string, unknown>): AnalyticsEvent {
  const net = getNetworkInfo();
  return {
    session_id: getSessionId(),
    id_docente: getDocente(),
    event_type: eventType,
    event_data: eventData,
    app_version: APP_VERSION,
    user_agent: navigator.userAgent,
    platform: navigator.platform || "unknown",
    language: navigator.language,
    screen_width: screen.width,
    screen_height: screen.height,
    viewport_width: window.innerWidth,
    viewport_height: window.innerHeight,
    connection_type: net.effectiveType || null,
    connection_downlink: net.downlink ?? null,
    is_online: navigator.onLine,
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    referrer: document.referrer,
  };
}

async function flushQueue(): Promise<void> {
  if (eventQueue.length === 0) return;
  const events = [...eventQueue];
  eventQueue = [];

  for (const event of events) {
    try {
      await fetch(ANALYTICS_URL, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(event),
      });
    } catch {
      // Silently fail - analytics should never break the app
    }
  }
}

function scheduleFlush(): void {
  if (flushTimer) return;
  flushTimer = setTimeout(() => {
    flushTimer = null;
    flushQueue();
  }, 3000);
}

export function trackEvent(eventType: string, eventData?: Record<string, unknown>): void {
  eventQueue.push(buildEvent(eventType, eventData));
  scheduleFlush();
}

export function trackViewChange(view: string): void {
  trackEvent("view_change", { view });
}

export function trackAction(action: string, details?: Record<string, unknown>): void {
  trackEvent("action", { action, ...details });
}

export function initAnalytics(): void {
  getSessionId();
  trackEvent("session_start");

  // Track cuando el usuario se va
  window.addEventListener("beforeunload", () => {
    const duration = Math.round((Date.now() - sessionStart) / 1000);
    const event = buildEvent("session_end", { duration_seconds: duration });
    // Use sendBeacon for reliability
    const blob = new Blob([JSON.stringify(event)], { type: "application/json" });
    navigator.sendBeacon(ANALYTICS_URL, blob);
  });

  // Track visibilidad
  document.addEventListener("visibilitychange", () => {
    if (document.hidden) {
      trackEvent("app_hidden");
    } else {
      trackEvent("app_visible");
    }
  });

  // Track errores
  window.addEventListener("error", (e) => {
    trackEvent("error", { message: e.message, filename: e.filename, line: e.lineno });
  });
}

export function getClientInfo(): ClientInfo {
  const net = getNetworkInfo();
  const nav = navigator as Navigator & { deviceMemory?: number };
  const duration = Math.round((Date.now() - sessionStart) / 1000);
  const mins = Math.floor(duration / 60);
  const secs = duration % 60;

  let pwaInstalled = false;
  if (window.matchMedia("(display-mode: standalone)").matches) {
    pwaInstalled = true;
  }

  return {
    session_id: getSessionId(),
    app_version: APP_VERSION,
    user_agent: navigator.userAgent,
    platform: navigator.platform || "Desconocido",
    language: navigator.language,
    screen: `${screen.width}x${screen.height}`,
    viewport: `${window.innerWidth}x${window.innerHeight}`,
    connection_type: net.effectiveType || "Desconocido",
    connection_speed: net.downlink ? `${net.downlink} Mbps` : "Desconocido",
    timezone: Intl.DateTimeFormat().resolvedOptions().timeZone,
    is_online: navigator.onLine,
    memory: nav.deviceMemory ? `${nav.deviceMemory} GB` : "No disponible",
    cores: navigator.hardwareConcurrency || 0,
    touch: "ontouchstart" in window || navigator.maxTouchPoints > 0,
    pwa_installed: pwaInstalled,
    referrer: document.referrer || "Directo",
    session_start: new Date(sessionStart).toLocaleString("es-CO"),
    session_duration: `${mins}m ${secs}s`,
  };
}

export async function fetchAnalytics(period: string = "all"): Promise<AnalyticsData | null> {
  try {
    const docente = getDocente();
    const params = new URLSearchParams({ period });
    if (docente) params.append("id_docente", docente);

    const response = await fetch(`${ANALYTICS_URL}?${params}`);
    const result = await response.json() as { status: string; data: AnalyticsData };
    if (result.status === "success") {
      return result.data;
    }
    return null;
  } catch {
    return null;
  }
}
