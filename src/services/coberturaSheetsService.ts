import {
  SPREADSHEET_ID_COBERTURA,
  WORKSHEET_TITLE_COBERTURA,
  API_URL_GS,
} from "../constants";
import type { CoberturaHistorica } from "../lib/coberturaUtils";

export class CoberturaSheetsService {
  private spreadsheetId = SPREADSHEET_ID_COBERTURA;
  private worksheetTitle = WORKSHEET_TITLE_COBERTURA;

  async saveCobertura(row: {
    fecha: string;
    dia_semana: string;
    hora: number;
    docente_ausente: string;
    grupo_ausente: string;
    docente_cubre: string;
    grupo_a_cubrir: string;
    estado: string;
  }): Promise<void> {
    const response = await fetch(`${API_URL_GS}/save_cobertura.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        spreadsheetId: this.spreadsheetId,
        worksheetTitle: this.worksheetTitle,
        values: [
          row.fecha,
          row.dia_semana,
          String(row.hora),
          row.docente_ausente,
          row.grupo_ausente,
          row.docente_cubre,
          row.grupo_a_cubrir,
          row.estado,
        ],
      }),
    });

    if (!response.ok) {
      const err = await response.json().catch(() => ({ error: "Error desconocido" }));
      throw new Error(err.error || "Error al guardar cobertura");
    }
  }

  async getCoberturas(): Promise<CoberturaHistorica[]> {
    const url = new URL(`${API_URL_GS}/get_coberturas.php`);
    url.searchParams.append("spreadsheetId", this.spreadsheetId);
    url.searchParams.append("worksheetTitle", this.worksheetTitle);

    const response = await fetch(url.toString());

    if (!response.ok) {
      const err = await response.json().catch(() => ({ error: "Error desconocido" }));
      throw new Error(err.error || "Error al obtener coberturas");
    }

    const data = await response.json();
    if (!data.values) return [];

    return data.values.map((row: any[]) => ({
      fecha: row[0] || "",
      dia_semana: row[1] || "",
      hora: parseInt(row[2]) || 0,
      docente_ausente: row[3] || "",
      grupo_ausente: row[4] || "",
      docente_cubre: row[5] || "",
      grupo_a_cubrir: row[6] || "",
      estado: row[7] || "pendiente",
    }));
  }

  async deleteCobertura(fecha: string, hora: number, docente_cubre: string): Promise<void> {
    const response = await fetch(`${API_URL_GS}/delete_cobertura.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        spreadsheetId: this.spreadsheetId,
        worksheetTitle: this.worksheetTitle,
        fecha,
        hora,
        docente_cubre,
      }),
    });

    if (!response.ok) {
      const err = await response.json().catch(() => ({ error: "Error desconocido" }));
      throw new Error(err.error || "Error al eliminar cobertura");
    }
  }

  async deleteCoberturasPorFecha(fecha: string): Promise<void> {
    const response = await fetch(`${API_URL_GS}/delete_coberturas_fecha.php`, {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        spreadsheetId: this.spreadsheetId,
        worksheetTitle: this.worksheetTitle,
        fecha,
      }),
    });

    if (!response.ok) {
      const err = await response.json().catch(() => ({ error: "Error desconocido" }));
      throw new Error(err.error || "Error al eliminar coberturas del día");
    }
  }
}

export const coberturaSheetsService = new CoberturaSheetsService();