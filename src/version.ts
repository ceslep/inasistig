/**
 * version.ts - Versionado de la aplicación y auto-actualización de clientes
 *
 * Al detectar una nueva versión, fuerza recarga para que el cliente
 * obtenga los assets más recientes (invalida service worker cache).
 */

export const APP_VERSION = "1.0.2";
export const APP_BUILD_DATE = "2026-03-13";

const VERSION_STORAGE_KEY = "app_version";

/**
 * Compara dos versiones semver. Retorna true si remote > local.
 */
function isNewerVersion(local: string, remote: string): boolean {
  const localParts = local.split(".").map(Number);
  const remoteParts = remote.split(".").map(Number);

  for (let i = 0; i < 3; i++) {
    const l = localParts[i] ?? 0;
    const r = remoteParts[i] ?? 0;
    if (r > l) return true;
    if (r < l) return false;
  }
  return false;
}

/**
 * Verifica si hay una nueva versión disponible consultando version.json
 * desplegado junto con la app.
 */
async function checkForUpdate(): Promise<void> {
  try {
    const response = await fetch("/inasistig/version.json?t=" + Date.now(), {
      cache: "no-store",
    });
    if (!response.ok) return;

    const data = (await response.json()) as { version: string };
    const remoteVersion = data.version;

    if (isNewerVersion(APP_VERSION, remoteVersion)) {
      console.log(
        `[Version] Nueva versión detectada: ${remoteVersion} (actual: ${APP_VERSION})`
      );

      // Guardar la nueva versión antes de recargar
      localStorage.setItem(VERSION_STORAGE_KEY, remoteVersion);

      // Limpiar caches y recargar
      await forceHardReload();
    }
  } catch {
    // Silently fail - no interrumpir la app
  }
}

/**
 * Fuerza la limpieza de caches y service worker, luego recarga.
 */
async function forceHardReload(): Promise<void> {
  // Limpiar service worker
  if ("serviceWorker" in navigator) {
    const registrations = await navigator.serviceWorker.getRegistrations();
    for (const registration of registrations) {
      await registration.unregister();
    }
  }

  // Limpiar caches
  if ("caches" in window) {
    const cacheNames = await caches.keys();
    for (const name of cacheNames) {
      await caches.delete(name);
    }
  }

  // Forzar recarga completa
  window.location.reload();
}

/**
 * Inicializa el sistema de versionado.
 * - Cada vez que la app carga, consulta version.json remoto
 * - Si hay versión nueva: limpia todo y recarga
 * - Si no hay conexión o falla, continúa normalmente
 */
export async function initVersionCheck(): Promise<void> {
  const storedVersion = localStorage.getItem(VERSION_STORAGE_KEY);

  if (!storedVersion) {
    localStorage.setItem(VERSION_STORAGE_KEY, APP_VERSION);
  } else if (isNewerVersion(storedVersion, APP_VERSION)) {
    localStorage.setItem(VERSION_STORAGE_KEY, APP_VERSION);
    console.log(`[Version] App actualizada a ${APP_VERSION}`);
  }

  // Verificar siempre al cargar
  await checkForUpdate();

  // Verificar al volver de segundo plano
  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
      checkForUpdate();
    }
  });
}
