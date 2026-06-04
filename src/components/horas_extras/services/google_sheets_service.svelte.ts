const SPREADSHEET_ID_HORAS_EXTRAS = "18LakiPTmnKMwH9jiuJXAyV7MSINqjT7khNIvl7DJhWk";
const WORKSHEET_TITLE_HORAS_EXTRAS = "extras";
const API_URL_GS = "https://app.iedeoccidente.com/gs";

export class HorasExtrasSheetsService {
  spreadsheetId = $state(SPREADSHEET_ID_HORAS_EXTRAS);
  worksheetTitle = $state(WORKSHEET_TITLE_HORAS_EXTRAS);
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
}

export const horasExtrasSheetsService = new HorasExtrasSheetsService();