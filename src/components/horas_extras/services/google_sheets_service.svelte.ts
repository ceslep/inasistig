const SPREADSHEET_ID_HORAS_EXTRAS = "18LakiPTmnKMwH9jiuJXAyV7MSINqjT7khNIvl7DJhWk";
const WORKSHEET_TITLE_HORAS_EXTRAS = "extras";
const WORKSHEET_TITLE_FIRMAS = "firmas";
const API_URL_GS = "https://app.iedeoccidente.com/gs";
const API_URL_IG = "https://app.iedeoccidente.com/ig";

export class HorasExtrasSheetsService {
  spreadsheetId = $state(SPREADSHEET_ID_HORAS_EXTRAS);
  worksheetTitle = $state(WORKSHEET_TITLE_HORAS_EXTRAS);
  worksheetFirmas = $state(WORKSHEET_TITLE_FIRMAS);
  backendUrl = $state('');

  constructor() {
    if (typeof window !== 'undefined') {
      this.backendUrl = localStorage.getItem('backendUrl') || API_URL_GS;
    }
  }

  async saveRegistro(values: string[], rowIndex: number | null = null): Promise<{ success: boolean; rowIndex?: number; updated?: boolean; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const payload: { spreadsheetId: string; worksheetTitle: string; values: string[]; rowIndex?: number } = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetTitle,
      values,
    };

    if (rowIndex !== null) {
      payload.rowIndex = rowIndex;
    }

    const response = await fetch(`${this.backendUrl}/save_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al guardar en el servidor PHP');
    }

    return await response.json();
  }

  async uploadFirma(docente: string, firmaBase64: string): Promise<{ success: boolean; url?: string; error?: string }> {
    try {
      const response = await fetch(`${API_URL_IG}/save_firma.php`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ docente, firmaBase64 }),
      });

      if (!response.ok) {
        const errorData = await response.json();
        throw new Error(errorData.error || 'Error al subir la firma');
      }

      return await response.json();
    } catch (error) {
      console.error('Error uploading firma:', error)
      throw error
    }
  }

  async saveFirma(docente: string, firmaUrl: string): Promise<{ success: boolean; rowIndex?: number; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const payload = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetFirmas,
      values: [docente, firmaUrl],
    };

    const response = await fetch(`${this.backendUrl}/save_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al guardar la firma');
    }

    return await response.json();
  }

  async getFirma(docente: string): Promise<{ success: boolean; firmaUrl?: string; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const body = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetFirmas,
      filterDocente: docente,
    };

    const response = await fetch(`${this.backendUrl}/get_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al obtener la firma');
    }

    const result = await response.json();
    if (result.success && result.records && result.records.length > 0) {
      return { success: true, firmaUrl: result.records[0].values[1] };
    }
    return { success: true, firmaUrl: undefined };
  }

async getRegistros(filtros?: { grado?: string; materia?: string }): Promise<{ success: boolean; records: { rowIndex: number; values: string[] }[]; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const body: { spreadsheetId: string; worksheetTitle: string; filterGrado?: string; filterMateria?: string } = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetTitle,
    };
    if (filtros?.grado) body.filterGrado = filtros.grado;
    if (filtros?.materia) body.filterMateria = filtros.materia;

    const response = await fetch(`${this.backendUrl}/get_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al obtener datos del servidor PHP');
    }

    return await response.json();
  }

  async getAllRegistros(): Promise<{ success: boolean; records: { rowIndex: number; values: string[] }[]; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const body = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetTitle,
    };

    const response = await fetch(`${this.backendUrl}/get_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(body),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al obtener datos del servidor PHP');
    }

    return await response.json();
  }

  async deleteRegistro(rowIndex: number): Promise<{ success: boolean; error?: string }> {
    if (!this.backendUrl) {
      throw new Error('Backend URL no configurada.');
    }

    const response = await fetch(`${this.backendUrl}/delete_horas_extras.php`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        spreadsheetId: this.spreadsheetId,
        worksheetTitle: this.worksheetTitle,
        rowIndex
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || 'Error al eliminar registro');
    }

    return await response.json();
  }
}

export const horasExtrasSheetsService = new HorasExtrasSheetsService();