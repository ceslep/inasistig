/**
 * Shared utility functions for report generators and filters.
 */

// --- Text normalization ---

/**
 * Strips accents, special characters, lowercases, and trims a string.
 * Used for fuzzy matching of docente names, student names, materias, etc.
 */
export const normalize = (str: string | null | undefined): string => {
  if (!str) return '';
  return str
    .toString()
    .normalize('NFD')
    .replace(/[\u0300-\u036f]/g, '')
    .replace(/[^a-zA-Z0-9\s]/g, '')
    .replace(/\s+/g, ' ')
    .trim()
    .toLowerCase();
};

// --- Docente number extraction ---

/**
 * Extracts the trailing number suffix from a docente name.
 * E.g. "Juan-5" returns "5", "Maria" returns null.
 */
export const getDocenteNumber = (docente: string): string | null => {
  const match = docente.match(/-(\d+)$/);
  return match ? match[1] : null;
};

// --- Date formatting ---

const SPANISH_MONTH_ABBR = [
  'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
  'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic',
];

/**
 * Formats a Date for display using Spanish month abbreviations.
 * Returns "DD/Mon/YYYY" format (2-digit year).
 */
export const formatDateDisplay = (date: Date): string => {
  return `${date.getUTCDate()}/${SPANISH_MONTH_ABBR[date.getUTCMonth()]}/${date.getUTCFullYear().toString().substr(2)}`;
};

// --- Dynamic library loading (cached) ---

interface PdfLibs {
  jsPDF: typeof import('jspdf').jsPDF;
  autoTable: typeof import('jspdf-autotable').default;
}

let cachedPdfLibs: PdfLibs | null = null;

/**
 * Dynamically imports jsPDF + autoTable. Cached after first load.
 */
export const loadPdfLibraries = async (): Promise<PdfLibs> => {
  if (cachedPdfLibs) return cachedPdfLibs;

  const [jspdfModule, autoTableModule] = await Promise.all([
    import('jspdf'),
    import('jspdf-autotable'),
  ]);

  cachedPdfLibs = {
    jsPDF: jspdfModule.default,
    autoTable: autoTableModule.default,
  };

  return cachedPdfLibs;
};

interface ExcelLibs {
  ExcelJS: typeof import('exceljs');
  saveAs: typeof import('file-saver').saveAs;
}

let cachedExcelLibs: ExcelLibs | null = null;

/**
 * Dynamically imports ExcelJS + file-saver. Cached after first load.
 */
export const loadExcelLibraries = async (): Promise<ExcelLibs> => {
  if (cachedExcelLibs) return cachedExcelLibs;

  const [exceljsModule, fileSaverModule] = await Promise.all([
    import('exceljs'),
    import('file-saver'),
  ]);

  cachedExcelLibs = {
    ExcelJS: exceljsModule.default,
    saveAs: fileSaverModule.default,
  };

  return cachedExcelLibs;
};

// --- Google Identity Services script loader ---

const GIS_SCRIPT_URL = 'https://accounts.google.com/gsi/client';

let gisLoadPromise: Promise<void> | null = null;

export const ensureGisLoaded = (): Promise<void> => {
  if (typeof google !== 'undefined' && google?.accounts?.oauth2?.initTokenClient != null) {
    return Promise.resolve();
  }

  if (gisLoadPromise) return gisLoadPromise;

  gisLoadPromise = new Promise<void>((resolve, reject) => {
    const existing = document.querySelector(`script[src="${GIS_SCRIPT_URL}"]`);
    if (existing) {
      existing.addEventListener('load', () => resolve());
      if (typeof google !== 'undefined' && google?.accounts) {
        resolve();
      }
      return;
    }

    const script = document.createElement('script');
    script.src = GIS_SCRIPT_URL;
    script.async = true;
    script.defer = true;
    script.onload = () => resolve();
    script.onerror = () => {
      gisLoadPromise = null;
      reject(new Error('No se pudo cargar el script de Google.'));
    };
    document.head.appendChild(script);
  });

  return gisLoadPromise;
};

// --- CSV escaping ---

/**
 * Properly escapes a CSV field value.
 * If the field contains a comma, newline, or double quote, it wraps
 * the value in double quotes and escapes internal quotes by doubling them.
 */
export const escapeCsvField = (value: string): string => {
  if (value.includes(',') || value.includes('\n') || value.includes('"')) {
    return `"${value.replace(/"/g, '""')}"`;
  }
  return value;
};
