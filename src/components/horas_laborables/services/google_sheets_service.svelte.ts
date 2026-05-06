/**
 * Google Sheets Service (PHP Backend Version)
 *
 * Este servicio utiliza el backend PHP en lugar de un proxy directo.
 */

import { API_CONFIG } from "../constants";

export class GoogleSheetsService {
  spreadsheetId = $state("");
  worksheetTitle = $state("");
  // URL base para los scripts PHP
  backendUrl = $state("");

  constructor() {
    if (typeof window !== "undefined") {
      this.spreadsheetId =
        localStorage.getItem("spreadsheetId") ||
        "1UW_dbtJEFJeOjCg323HJPaacqPIztw_9bGI5Rw6HRxQ";
      this.worksheetTitle =
        localStorage.getItem("horasSheet") ||
        new Date().getFullYear().toString();
      // Por defecto apuntamos a la URL configurada en constants.ts
      this.backendUrl =
        localStorage.getItem("backendUrl") || API_CONFIG.BASE_URL;
    }
  }

  async init(customSpreadsheetId?: string, customWorksheetTitle?: string) {
    if (typeof window === "undefined") return;

    if (customSpreadsheetId) {
      this.spreadsheetId = customSpreadsheetId;
      localStorage.setItem("spreadsheetId", customSpreadsheetId);
    }

    if (customWorksheetTitle) {
      this.worksheetTitle = customWorksheetTitle;
      localStorage.setItem("horasSheet", customWorksheetTitle);
    }
  }

  /**
   * Envía los datos al backend PHP para guardarlos en Google Sheets.
   */
  async appendRow(row: any[], rowIndex: number | null = null) {
    if (!this.backendUrl) {
      throw new Error("Backend URL no configurada.");
    }

    const payload: { spreadsheetId: string; worksheetTitle: string; values: any[]; rowIndex?: number } = {
      spreadsheetId: this.spreadsheetId,
      worksheetTitle: this.worksheetTitle,
      values: row,
    };

    if (rowIndex !== null) {
      payload.rowIndex = rowIndex;
    }

    const response = await fetch(`${this.backendUrl}/save_horas.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(errorData.error || "Error al guardar en el servidor PHP");
    }

    return await response.json();
  }

  /**
   * Obtiene todos los registros del backend PHP.
   */
  async getRows(): Promise<{ success: boolean; records: { rowIndex: number; values: any[] }[] }> {
    if (!this.backendUrl) {
      throw new Error("Backend URL no configurada.");
    }

    const response = await fetch(`${this.backendUrl}/get_horas.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        spreadsheetId: this.spreadsheetId,
        worksheetTitle: this.worksheetTitle,
      }),
    });

    if (!response.ok) {
      const errorData = await response.json();
      throw new Error(
        errorData.error || "Error al obtener datos del servidor PHP",
      );
    }

    return await response.json();
  }

  async setAnotadorWorksheet() {
    if (typeof window === "undefined") return;
    this.worksheetTitle = localStorage.getItem("anotadorSheet") || "Anotador";
  }

  saveBackendUrl(url: string) {
    if (typeof window === "undefined") return;
    this.backendUrl = url;
    localStorage.setItem("backendUrl", url);
  }
}

export const sheetsService = new GoogleSheetsService();
