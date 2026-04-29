import { writable } from 'svelte/store';

import { ensureGisLoaded } from './utils';

const DRIVE_UPLOAD_SCOPE = 'https://www.googleapis.com/auth/drive.file';
const DRIVE_READONLY_SCOPE = 'https://www.googleapis.com/auth/drive.readonly';
const DRIVE_SCOPES = `${DRIVE_UPLOAD_SCOPE} ${DRIVE_READONLY_SCOPE}`;

const ACCESS_TOKEN_KEY = 'gdrive_access_token';
const TOKEN_EXPIRY_KEY = 'gdrive_token_expiry';
const SCOPE_VERSION_KEY = 'gdrive_scope_version';
const CURRENT_SCOPE_VERSION = '3';

export const isUploading = writable(false);

export interface FolderItem {
  id: string;
  name: string;
}

export interface ListFoldersResult {
  folders: FolderItem[];
  error?: string;
}

interface TokenResult {
  token: string | null;
  error?: 'gis_not_loaded' | 'user_cancelled' | 'auth_error' | 'timeout' | 'unknown';
  message?: string;
}

function isGisLoaded(): boolean {
  try {
    return typeof google !== 'undefined'
      && google?.accounts?.oauth2?.initTokenClient != null;
  } catch {
    return false;
  }
}

export const gdriveService = {
  async getAccessToken(clientId: string): Promise<TokenResult> {
    const storedScopeVersion = localStorage.getItem(SCOPE_VERSION_KEY);
    if (storedScopeVersion !== CURRENT_SCOPE_VERSION) {
      localStorage.removeItem(ACCESS_TOKEN_KEY);
      localStorage.removeItem(TOKEN_EXPIRY_KEY);
      localStorage.setItem(SCOPE_VERSION_KEY, CURRENT_SCOPE_VERSION);
    }

    const storedToken = localStorage.getItem(ACCESS_TOKEN_KEY);
    const expiry = localStorage.getItem(TOKEN_EXPIRY_KEY);
    if (storedToken && expiry && Date.now() < parseInt(expiry)) {
      return { token: storedToken };
    }

    try {
      await ensureGisLoaded();
    } catch {
      // fall through to isGisLoaded check
    }

    if (!isGisLoaded()) {
      return {
        token: null,
        error: 'gis_not_loaded',
        message: 'El servicio de Google no se ha cargado. Recarga la página e intenta de nuevo.',
      };
    }

    return new Promise<TokenResult>((resolve) => {
      let resolved = false;

      const timeoutId = setTimeout(() => {
        if (!resolved) {
          resolved = true;
          resolve({
            token: null,
            error: 'timeout',
            message: 'Tiempo de espera agotado. Intenta de nuevo.',
          });
        }
      }, 30_000);

      try {
        const client = google.accounts.oauth2.initTokenClient({
          client_id: clientId,
          scope: DRIVE_SCOPES,
          callback: (response) => {
            if (resolved) return;
            resolved = true;
            clearTimeout(timeoutId);

            if (response.error) {
              resolve({
                token: null,
                error: 'auth_error',
                message: response.error_description || `Error de autorización: ${response.error}`,
              });
              return;
            }

            const expiryTime = Date.now() + response.expires_in * 1000;
            localStorage.setItem(ACCESS_TOKEN_KEY, response.access_token);
            localStorage.setItem(TOKEN_EXPIRY_KEY, expiryTime.toString());
            resolve({ token: response.access_token });
          },
          error_callback: (error) => {
            if (resolved) return;
            resolved = true;
            clearTimeout(timeoutId);

            if (error.type === 'popup_closed') {
              resolve({
                token: null,
                error: 'user_cancelled',
                message: 'Se cerró la ventana de autorización.',
              });
            } else {
              resolve({
                token: null,
                error: 'auth_error',
                message: error.message || 'Error al conectar con Google.',
              });
            }
          },
        });

        client.requestAccessToken();
      } catch (err) {
        if (!resolved) {
          resolved = true;
          clearTimeout(timeoutId);
          resolve({
            token: null,
            error: 'unknown',
            message: 'Error inesperado al iniciar la autenticación.',
          });
        }
      }
    });
  },

  async listFolders(clientId: string, parentId: string = 'root', driveId?: string): Promise<ListFoldersResult> {
    const tokenResult = await this.getAccessToken(clientId);
    if (!tokenResult.token) {
      return { folders: [], error: tokenResult.message };
    }

    try {
      const query = `'${parentId}' in parents and mimeType = 'application/vnd.google-apps.folder' and trashed = false`;
      let url = `https://www.googleapis.com/drive/v3/files?q=${encodeURIComponent(query)}&fields=files(id,name)&orderBy=name&includeItemsFromAllDrives=true&supportsAllDrives=true`;
      if (driveId) {
        url += `&driveId=${encodeURIComponent(driveId)}&corpora=drive`;
      }
      const response = await fetch(
        url,
        { headers: { Authorization: `Bearer ${tokenResult.token}` } },
      );

      if (!response.ok) {
        const errorBody = await response.json().catch(() => ({}));
        const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';

        if (response.status === 401) {
          localStorage.removeItem(ACCESS_TOKEN_KEY);
          localStorage.removeItem(TOKEN_EXPIRY_KEY);
          return { folders: [], error: 'Tu sesión de Drive expiró. Intenta de nuevo.' };
        }
        if (response.status === 403) {
          return { folders: [], error: `Sin permisos para acceder a Drive. ${apiMessage}` };
        }
        return { folders: [], error: `Error al listar carpetas (${response.status}). ${apiMessage}` };
      }

      const data: { files?: FolderItem[] } = await response.json();
      return { folders: data.files || [] };
    } catch (error) {
      console.error('Error listing folders:', error);
      return { folders: [], error: 'Error de conexión al listar carpetas.' };
    }
  },

  async listSharedDrives(clientId: string): Promise<ListFoldersResult> {
    const tokenResult = await this.getAccessToken(clientId);
    if (!tokenResult.token) {
      return { folders: [], error: tokenResult.message };
    }

    try {
      const response = await fetch(
        'https://www.googleapis.com/drive/v3/drives?pageSize=50&fields=drives(id,name)',
        { headers: { Authorization: `Bearer ${tokenResult.token}` } },
      );

      if (!response.ok) {
        const errorBody = await response.json().catch(() => ({}));
        const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';
        return { folders: [], error: `Error al listar unidades compartidas (${response.status}). ${apiMessage}` };
      }

      const data: { drives?: FolderItem[] } = await response.json();
      return { folders: data.drives || [] };
    } catch (error) {
      console.error('Error listing shared drives:', error);
      return { folders: [], error: 'Error de conexión al listar unidades compartidas.' };
    }
  },

   async listStarredFolders(clientId: string): Promise<ListFoldersResult> {
     const tokenResult = await this.getAccessToken(clientId);
     if (!tokenResult.token) {
       return { folders: [], error: tokenResult.message };
     }

     try {
       const query = `starred = true and mimeType = 'application/vnd.google-apps.folder' and trashed = false`;
       const response = await fetch(
         `https://www.googleapis.com/drive/v3/files?q=${encodeURIComponent(query)}&fields=files(id,name)&orderBy=name&includeItemsFromAllDrives=true&supportsAllDrives=true`,
         { headers: { Authorization: `Bearer ${tokenResult.token}` } },
       );

       if (!response.ok) {
         const errorBody = await response.json().catch(() => ({}));
         const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';
         return { folders: [], error: `Error al listar favoritos (${response.status}). ${apiMessage}` };
       }

       const data: { files?: FolderItem[] } = await response.json();
       return { folders: data.files || [] };
     } catch (error) {
       console.error('Error listing starred folders:', error);
       return { folders: [], error: 'Error de conexión al listar favoritos.' };
     }
   },

   async listFilesByNameInFolder(
     clientId: string,
     fileName: string,
     folderId?: string,
     driveId?: string
   ): Promise<{ files: FolderItem[]; error?: string }> {
     const tokenResult = await this.getAccessToken(clientId);
     if (!tokenResult.token) {
       return { files: [], error: tokenResult.message };
     }

try {
        let query = `name = "${fileName.replace(/"/g, '\\"')}" and trashed = false`;
        if (folderId) {
          query += ` and '${folderId}' in parents`;
        }

        let url = `https://www.googleapis.com/drive/v3/files?q=${encodeURIComponent(query)}&fields=files(id,name)`;
        
        if (driveId) {
          url += `&driveId=${encodeURIComponent(driveId)}&corpora=drive&includeItemsFromAllDrives=true&supportsAllDrives=true`;
        } else if (folderId) {
          url += `&supportsAllDrives=true`;
        }

        const response = await fetch(url, { headers: { Authorization: `Bearer ${tokenResult.token}` } });

       if (!response.ok) {
         const errorBody = await response.json().catch(() => ({}));
         const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';
         if (response.status === 401) {
           localStorage.removeItem(ACCESS_TOKEN_KEY);
           localStorage.removeItem(TOKEN_EXPIRY_KEY);
           return { files: [], error: 'Tu sesión de Drive expiró. Intenta de nuevo.' };
         }
         return { files: [], error: `Error al buscar archivos (${response.status}). ${apiMessage}` };
       }

       const data: { files?: FolderItem[] } = await response.json();
       return { files: data.files || [] };
     } catch (error) {
       console.error('Error listing files by name:', error);
       return { files: [], error: 'Error de conexión al buscar archivos.' };
     }
   },

   async createFolder(
     clientId: string,
     folderName: string,
     parentId: string = 'root',
     driveId?: string
   ): Promise<{ folder?: FolderItem; error?: string }> {
     const tokenResult = await this.getAccessToken(clientId);
     if (!tokenResult.token) {
       return { error: tokenResult.message };
     }

     try {
       const metadata: {
         name: string;
         mimeType: string;
         parents?: string[];
       } = {
         name: folderName,
         mimeType: 'application/vnd.google-apps.folder',
       };

       if (parentId) {
         metadata['parents'] = [parentId];
       }

       const form = new FormData();
       form.append('metadata', new Blob([JSON.stringify(metadata)], { type: 'application/json' }));

       let url = 'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true';
       if (driveId) {
         url += `&driveId=${encodeURIComponent(driveId)}&corpora=drive`;
       }

       const response = await fetch(
         url,
         {
           method: 'POST',
           headers: { Authorization: `Bearer ${tokenResult.token}` },
           body: form,
         },
       );

       if (!response.ok) {
         const errorBody = await response.json().catch(() => ({}));
         const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';
         if (response.status === 401) {
           localStorage.removeItem(ACCESS_TOKEN_KEY);
           localStorage.removeItem(TOKEN_EXPIRY_KEY);
           return { error: 'Tu sesión de Drive expiró. Intenta de nuevo.' };
         }
         return { error: `Error al crear carpeta (${response.status}). ${apiMessage}` };
       }

       const data: FolderItem = await response.json();
       return { folder: data };
     } catch (error) {
       console.error('Error creating folder:', error);
       return { error: 'Error de conexión al crear la carpeta.' };
     }
   },

async uploadFile(
      blob: Blob,
      fileName: string,
      mimeType: string,
      clientId: string,
      folderId?: string,
    ): Promise<{ success: boolean; fileId?: string; error?: string }> {
      isUploading.set(true);
      try {
        const tokenResult = await this.getAccessToken(clientId);
        if (!tokenResult.token) {
          return { success: false, error: tokenResult.message || 'No se pudo obtener el token de acceso' };
        }

        const existingFiles = await this.listFilesByNameInFolder(clientId, fileName, folderId);
        if (existingFiles.error) {
          return { success: false, error: existingFiles.error };
        }

        if (existingFiles.files.length > 0) {
          const fileId = existingFiles.files[0].id;
          await this.deleteFile(clientId, fileId);
        }

        const metadata: { name: string; mimeType: string; parents?: string[] } = {
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
          'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart&supportsAllDrives=true',
          {
            method: 'POST',
            headers: { Authorization: `Bearer ${tokenResult.token}` },
            body: form,
          },
        );

        if (!response.ok) {
          const error: { error?: { message?: string } } = await response.json().catch(() => ({}));
          return { success: false, error: error?.error?.message || 'Error al subir a Google Drive' };
        }

        const result: { id: string } = await response.json();
        return { success: true, fileId: result.id };
      } catch (error: unknown) {
        const message = error instanceof Error ? error.message : 'No se pudo guardar el archivo en tu unidad.';
        console.error('GDrive Error:', error);
        return { success: false, error: message };
      } finally {
        isUploading.set(false);
      }
    },

   async deleteFile(clientId: string, fileId: string): Promise<{ success: boolean; error?: string }> {
     const tokenResult = await this.getAccessToken(clientId);
     if (!tokenResult.token) {
       return { success: false, error: tokenResult.message };
     }

     try {
       const response = await fetch(
         `https://www.googleapis.com/drive/v3/files/${fileId}?supportsAllDrives=true`,
         {
           method: 'DELETE',
           headers: { Authorization: `Bearer ${tokenResult.token}` },
         },
       );

       if (!response.ok) {
         const errorBody = await response.json().catch(() => ({}));
         const apiMessage = (errorBody as { error?: { message?: string } })?.error?.message || '';
         if (response.status === 401) {
           localStorage.removeItem(ACCESS_TOKEN_KEY);
           localStorage.removeItem(TOKEN_EXPIRY_KEY);
           return { success: false, error: 'Tu sesión de Drive expiró. Intenta de nuevo.' };
         }
         return { success: false, error: `Error al eliminar archivo (${response.status}). ${apiMessage}` };
       }

       return { success: true };
     } catch (error) {
       console.error('Error deleting file:', error);
       return { success: false, error: 'Error de conexión al eliminar el archivo.' };
     }
   },

  clearToken(): void {
    localStorage.removeItem(ACCESS_TOKEN_KEY);
    localStorage.removeItem(TOKEN_EXPIRY_KEY);
  },
};
