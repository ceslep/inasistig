/**
 * analyticsService.ts - Servicio de recopilación de estadísticas de uso
 */

import { ANALYTICS_URL } from "../constants";
import { APP_VERSION } from "../version";
import { getDocenteName } from "./authStore";

import { UAParser } from "ua-parser-js";

// Parsed UA info (cached per session since UA doesn't change)
interface ParsedUA {
  browser_name: string;
  browser_version: string;
  os_name: string;
  os_version: string;
  device_type: string;
  device_vendor: string;
  device_model: string;
  cpu_architecture: string;
}

interface UAHighEntropy {
  platform?: string;
  platformVersion?: string;
  architecture?: string;
  model?: string;
  fullVersionList?: Array<{ brand: string; version: string }>;
  mobile?: boolean;
}

interface NavigatorUA {
  userAgentData?: {
    platform?: string;
    mobile?: boolean;
    getHighEntropyValues?: (hints: string[]) => Promise<UAHighEntropy>;
  };
}

let cachedParsedUA: ParsedUA | null = null;
let parsedUAPromise: Promise<ParsedUA> | null = null;

/**
 * Resuelve la versión real de Windows a partir del platformVersion
 * de Client Hints. Major >= 13 = Windows 11, 1-12 = Windows 10.
 */
function resolveWindowsVersion(platformVersion: string): string {
  const major = parseInt(platformVersion.split(".")[0], 10);
  if (isNaN(major) || major <= 0) return "10";
  return major >= 13 ? "11" : "10";
}

/**
 * Encuentra el navegador principal del fullVersionList de Client Hints,
 * ignorando las marcas de señuelo (Chromium, Not*Brand, etc.)
 */
function extractBrowser(list: Array<{ brand: string; version: string }>): { name: string; version: string } | null {
  const skip = /^(chromium|not.{0,5}brand)$/i;
  const match = list.find((b) => !skip.test(b.brand));
  if (match) return { name: match.brand, version: match.version };
  const chromium = list.find((b) => /^chromium$/i.test(b.brand));
  if (chromium) return { name: chromium.brand, version: chromium.version };
  return null;
}

function getBaseUA(): ParsedUA {
  const parser = new UAParser(navigator.userAgent);
  const browser = parser.getBrowser();
  const os = parser.getOS();
  const device = parser.getDevice();
  const cpu = parser.getCPU();

  return {
    browser_name: browser.name || "Desconocido",
    browser_version: browser.version || "Desconocido",
    os_name: os.name || "Desconocido",
    os_version: os.version || "Desconocido",
    device_type: device.type || "desktop",
    device_vendor: device.vendor || "Desconocido",
    device_model: device.model || "Desconocido",
    cpu_architecture: cpu.architecture || "Desconocido",
  };
}

async function fetchHighEntropyUA(): Promise<ParsedUA> {
  const base = getBaseUA();

  try {
    const nav = navigator as Navigator & NavigatorUA;
    const uaData = nav.userAgentData;
    if (!uaData?.getHighEntropyValues) return base;

    const hints = await uaData.getHighEntropyValues([
      "platform",
      "platformVersion",
      "architecture",
      "model",
      "fullVersionList",
      "mobile",
    ]);

    // OS name + version corregido
    if (hints.platform) {
      base.os_name = hints.platform;
      if (hints.platformVersion) {
        if (hints.platform === "Windows") {
          base.os_version = resolveWindowsVersion(hints.platformVersion);
        } else {
          base.os_version = hints.platformVersion;
        }
      }
    }

    // Arquitectura CPU
    if (hints.architecture) {
      base.cpu_architecture = hints.architecture;
    }

    // Modelo de dispositivo (útil en Android)
    if (hints.model) {
      base.device_model = hints.model;
    }

    // Navegador y versión completa
    if (hints.fullVersionList && hints.fullVersionList.length > 0) {
      const browser = extractBrowser(hints.fullVersionList);
      if (browser) {
        base.browser_name = browser.name;
        base.browser_version = browser.version;
      }
    }

    // Tipo de dispositivo
    if (hints.mobile !== undefined) {
      if (hints.mobile && base.device_type === "desktop") {
        base.device_type = "mobile";
      }
    }
  } catch {
    // Client Hints falló, se usa el resultado de ua-parser-js
  }

  return base;
}

/**
 * Inicia la detección de UA en segundo plano (llamar en initAnalytics).
 * No bloquea, los primeros eventos pueden ir con datos parciales.
 */
function warmUpParsedUA(): void {
  if (parsedUAPromise) return;
  parsedUAPromise = fetchHighEntropyUA().then((result) => {
    cachedParsedUA = result;
    return result;
  });
}

function getParsedUA(): ParsedUA {
  if (cachedParsedUA) return cachedParsedUA;
  // Si aún no hay cache, devolver datos base síncronos
  cachedParsedUA = getBaseUA();
  return cachedParsedUA;
}

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
  browser_name: string;
  browser_version: string;
  os_name: string;
  os_version: string;
  device_type: string;
  device_vendor: string;
  device_model: string;
  cpu_architecture: string;
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
  browser_name: string | null;
  browser_version: string | null;
  os_name: string | null;
  os_version: string | null;
  device_type: string | null;
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
  browser_name: string;
  browser_version: string;
  os_name: string;
  os_version: string;
  device_type: string;
  device_vendor: string;
  device_model: string;
  cpu_architecture: string;
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

function getDocente(): string | null {
  const name = getDocenteName();
  return name || null;
}

function getNetworkInfo(): NetworkInfo {
  const nav = navigator as Navigator & { connection?: { effectiveType?: string; downlink?: number } };
  return nav.connection || {};
}

function buildEvent(eventType: string, eventData?: Record<string, unknown>): AnalyticsEvent {
  const net = getNetworkInfo();
  const parsed = getParsedUA();
  return {
    session_id: getSessionId(),
    id_docente: getDocente(),
    event_type: eventType,
    event_data: eventData,
    app_version: APP_VERSION,
    user_agent: navigator.userAgent,
    platform: navigator.platform || "unknown",
    ...parsed,
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
  warmUpParsedUA();
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

export async function getClientInfo(): Promise<ClientInfo> {
  const net = getNetworkInfo();
  const nav = navigator as Navigator & { deviceMemory?: number };
  const duration = Math.round((Date.now() - sessionStart) / 1000);
  const mins = Math.floor(duration / 60);
  const secs = duration % 60;
  const parsed = await fetchHighEntropyUA();

  let pwaInstalled = false;
  if (window.matchMedia("(display-mode: standalone)").matches) {
    pwaInstalled = true;
  }

  return {
    session_id: getSessionId(),
    app_version: APP_VERSION,
    user_agent: navigator.userAgent,
    platform: navigator.platform || "Desconocido",
    ...parsed,
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

export async function resetAnalytics(): Promise<boolean> {
  try {
    const response = await fetch(ANALYTICS_URL, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({ action: "reset" }),
    });
    const result = await response.json() as { status: string };
    return result.status === "success";
  } catch {
    return false;
  }
}

export async function fetchAnalytics(period: string = "all"): Promise<AnalyticsData | null> {
  try {
    const params = new URLSearchParams({ period });

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
