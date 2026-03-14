/**
 * version.ts - Versionado de la aplicación con aviso al usuario
 *
 * Al detectar una nueva versión, muestra un banner no intrusivo.
 * El usuario decide cuándo actualizar para no perder trabajo en progreso.
 */

import Swal from "sweetalert2";

export const APP_VERSION = "1.0.3";
export const APP_BUILD_DATE = "2026-03-14";

const VERSION_STORAGE_KEY = "app_version";
let updateAvailable = false;

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

async function checkForUpdate(): Promise<void> {
  if (updateAvailable) return;

  try {
    const response = await fetch("/inasistig/version.json?t=" + Date.now(), {
      cache: "no-store",
    });
    if (!response.ok) return;

    const data = (await response.json()) as { version: string };
    const remoteVersion = data.version;

    if (isNewerVersion(APP_VERSION, remoteVersion)) {
      updateAvailable = true;
      localStorage.setItem(VERSION_STORAGE_KEY, remoteVersion);
      showUpdatePrompt(remoteVersion);
    }
  } catch {
    // Silently fail
  }
}

function showUpdatePrompt(newVersion: string): void {
  Swal.fire({
    title: "Nueva versión disponible",
    html: `Hay una actualización disponible <b>(v${newVersion})</b>.<br>Puedes actualizar cuando termines lo que estás haciendo.`,
    icon: "info",
    showCancelButton: true,
    confirmButtonText: "Actualizar ahora",
    cancelButtonText: "Más tarde",
    confirmButtonColor: "#6366f1",
    toast: false,
    allowOutsideClick: true,
  }).then((result) => {
    if (result.isConfirmed) {
      applyUpdate();
    }
  });
}

async function applyUpdate(): Promise<void> {
  if ("serviceWorker" in navigator) {
    const registrations = await navigator.serviceWorker.getRegistrations();
    for (const registration of registrations) {
      await registration.unregister();
    }
  }

  if ("caches" in window) {
    const cacheNames = await caches.keys();
    for (const name of cacheNames) {
      await caches.delete(name);
    }
  }

  window.location.reload();
}

export async function initVersionCheck(): Promise<void> {
  const storedVersion = localStorage.getItem(VERSION_STORAGE_KEY);

  if (!storedVersion) {
    localStorage.setItem(VERSION_STORAGE_KEY, APP_VERSION);
  } else if (isNewerVersion(storedVersion, APP_VERSION)) {
    localStorage.setItem(VERSION_STORAGE_KEY, APP_VERSION);
  }

  await checkForUpdate();

  document.addEventListener("visibilitychange", () => {
    if (!document.hidden) {
      checkForUpdate();
    }
  });
}
