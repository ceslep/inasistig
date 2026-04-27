import { writable, get } from 'svelte/store';
import Swal from 'sweetalert2';

declare var google: any;

const DRIVE_SCOPE = 'https://www.googleapis.com/auth/drive.file';
const ACCESS_TOKEN_KEY = 'gdrive_access_token';
const TOKEN_EXPIRY_KEY = 'gdrive_token_expiry';

export const isUploading = writable(false);

interface DriveToken {
  access_token: string;
  expires_in: number;
}

/**
 * Servicio para interactuar con Google Drive REST API v3
 */
export const gdriveService = {
  /**
   * Obtiene un token de acceso válido, pidiendo permiso al usuario si es necesario
   */
  async getAccessToken(clientId: string): Promise<string | null> {
    const storedToken = localStorage.getItem(ACCESS_TOKEN_KEY);
    const expiry = localStorage.getItem(TOKEN_EXPIRY_KEY);

    if (storedToken && expiry && Date.now() < parseInt(expiry)) {
      return storedToken;
    }

    return new Promise((resolve) => {
      const client = google.accounts.oauth2.initTokenClient({
        client_id: clientId,
        scope: DRIVE_SCOPE,
        callback: (response: any) => {
          if (response.error) {
            console.error('Error de autorización:', response.error);
            resolve(null);
            return;
          }
          const expiryTime = Date.now() + response.expires_in * 1000;
          localStorage.setItem(ACCESS_TOKEN_KEY, response.access_token);
          localStorage.setItem(TOKEN_EXPIRY_KEY, expiryTime.toString());
          resolve(response.access_token);
        },
      });
      client.requestAccessToken();
    });
  },

  /**
   * Lista las carpetas en una ubicación específica
   */
  async listFolders(clientId: string, parentId: string = 'root') {
    try {
      const token = await this.getAccessToken(clientId);
      if (!token) return [];

      const query = `'${parentId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false`;
      const response = await fetch(
        `https://www.googleapis.com/drive/v3/files?q=${encodeURIComponent(query)}&fields=files(id,name)&orderBy=name`,
        {
          headers: { Authorization: `Bearer ${token}` },
        }
      );

      if (!response.ok) throw new Error('Error al listar carpetas');
      const data = await response.json();
      return data.files || [];
    } catch (error) {
      console.error('Error listing folders:', error);
      return [];
    }
  },

  /**
   * Sube un archivo a Google Drive en una carpeta específica
   */
  async uploadFile(blob: Blob, fileName: string, mimeType: string, clientId: string, folderId?: string) {
    isUploading.set(true);
    try {
      const token = await this.getAccessToken(clientId);
      if (!token) throw new Error('No se pudo obtener el token de acceso');

      // 1. Crear metadatos del archivo
      const metadata: any = {
        name: fileName,
        mimeType: mimeType,
      };

      if (folderId) {
        metadata.parents = [folderId];
      }

      const form = new FormData();
      form.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));
      form.append('file', blob);

      const response = await fetch(
        'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
        {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${token}`,
          },
          body: form,
        }
      );

      if (!response.ok) {
        const error = await response.json();
        throw new Error(error.error?.message || 'Error al subir a Google Drive');
      }

      const result = await response.json();
      
      await Swal.fire({
        icon: 'success',
        title: '¡Guardado en Drive!',
        text: `El reporte "${fileName}" se ha guardado correctamente.`,
        confirmButtonColor: '#4f46e5',
        footer: `<a href="https://drive.google.com/file/d/${result.id}/view" target="_blank" style="color: #6366f1; font-weight: 600;">Ver archivo en Drive</a>`
      });

      return result;
    } catch (error: any) {
      console.error('GDrive Error:', error);
      Swal.fire({
        icon: 'error',
        title: 'Error de Drive',
        text: error.message || 'No se pudo guardar el archivo en tu unidad.',
        confirmButtonColor: '#ef4444',
      });
      return null;
    } finally {
      isUploading.set(false);
    }
  }
};
