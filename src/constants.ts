// Google Auth
export const GOOGLE_CLIENT_ID = '52017046107-5tked8kgj3c12qekpe77uu3r148fo7nb.apps.googleusercontent.com'

export const SPREADSHEET_ID = "1wN7lp7lOGyxKYIUJ9TU89N9knnJjX2Z_TfsOUg48QpQ";
export const API_URL_GS = "https://app.iedeoccidente.com/gs";

export const BASE_URL = "https://app.iedeoccidente.com/ig";
export const DOCENTES_URL = `${BASE_URL}/getprofes.php`;
export const MATERIAS_URL = `${BASE_URL}/getMaterias.php`;
export const ESTUDIANTES_URL = `${BASE_URL}/getEstudiantes.php`;

// Google Sheets SALVAR INASISTENCIAS
export const SAVE_INASISTENCIAS_URL = `${API_URL_GS}/save_inasistencias.php`;

// Google Sheets SALVAR ANOTADOR
export const SAVE_ANOTADOR_URL = `${API_URL_GS}/save_anotador.php`;

// Anotador
export const ANOTADOR_URL = `${BASE_URL}/getOpcionesAnotador.php`;
export const ANOTADOR2_URL = `${BASE_URL}/getOpcionesAnotador2.php`;
export const ANOTADOR3_URL = `${BASE_URL}/getOpcionesAnotador3.php`;

//diario
export const DIARIO_OPTIONS_URL = `${BASE_URL}/adiario.php`;
export const SAVE_DIARIO_URL = `${API_URL_GS}/save_diario.php`;

// Planeador
export const SPREADSHEET_ID_PLANEADOR = "1nXqDNW_KLlDoXKUENQ-Yg50dHfNRI_vL5r4boYOfbOo";
export const SAVE_PLANEADOR_URL = `${API_URL_GS}/save_planeador.php`;
export const GET_PLANEADOR_URL = `${API_URL_GS}/get_planeador.php`;
export const WORKSHEET_TITLE_PLANEADOR = "Planeaciones";

// PIAR
export const SPREADSHEET_ID_PIAR = "1nXqDNW_KLlDoXKUENQ-Yg50dHfNRI_vL5r4boYOfbOo";
export const SAVE_PIAR_URL = `${API_URL_GS}/save_piar.php`;
export const GET_PIAR_URL = `${API_URL_GS}/get_piar.php`;
export const WORKSHEET_TITLE_PIAR = "PIAR";

// Acta de Reunión de Padres de Familia
export const SPREADSHEET_ID_PADRES = "PENDIENTE_CONFIGURAR";
export const SAVE_ACTA_PADRES_URL = `${API_URL_GS}/save_acta_padres.php`;
export const WORKSHEET_TITLE_PADRES = "ActaPadres";
export const INFO_PADRES =
  "Registra el acta de reunión de padres conforme al Decreto 1286 de 2005 (artículo 23). Genera PDF, Excel y guarda en Drive.";

// Acta de Reunión de Área
export const SPREADSHEET_ID_ACTA = "PENDIENTE_CONFIGURAR";
export const SAVE_ACTA_URL = `${API_URL_GS}/save_acta_area.php`;
export const WORKSHEET_TITLE_ACTA = "ActaArea";
export const INFO_ACTA =
  "Registra el acta de reunión de área conforme a la Ley 115 de 1994 y el Decreto 1860 de 1994. El borrador se guarda automáticamente.";

// Acta de Izada de Bandera
export const SPREADSHEET_ID_IZADA = "PENDIENTE_CONFIGURAR";
export const SAVE_ACTA_IZADA_URL = `${API_URL_GS}/save_acta_izada.php`;
export const WORKSHEET_TITLE_IZADA = "ActaIzada";
export const INFO_IZADA =
  "Registra el acta de izadas de bandera conforme al Decreto 1860 de 1994 (artículos 36 al 40). Genera PDF, Excel y guarda en Drive.";

// Temas del Docente (JSON)
export const UPLOAD_TEMAS_URL = `${BASE_URL}/arjson.php`;
export const GET_TEMAS_BASE_URL = `${BASE_URL}/getjson.php`;

// Google Sheets
export const WORKSHEET_TITLE = "Inasistencias";
export const WORKSHEET_TITLE_ANOTADOR = "Datos";
export const SPREADSHEET_ID_ANOTADOR =
  "1Q6EcSvccB7BoJiw9PD2s5J4PB8AJmr-6v-yKhiE4E8k";
export const SPREADSHEET_ID_DIARIO =
  "10pBVNYnS-ctmBYP9Y8KL3h32GZKsyi0bwoyil-kEGaE";
export const WORKSHEET_TITLE_DIARIO = "diario";

// Textos informativos para alertas dismissibles
export const INFO_INASISTENCIA =
  "Selecciona un grado y una asignatura para comenzar a registrar inasistencias. Ahora puedes marcar motivos individuales y observaciones específicas por estudiante.Tambien pueden ver el filtro de datos en Locker Studio";
export const INFO_ANOTADOR =
  "Completa los datos de la clase y selecciona las observaciones que correspondan. Puedes editar el texto de cada observación si es necesario.Recuerda hacer check en el cuadro de selección para guardar esa anotación.";

export const INFO_DIARIO =
  "Completa los datos de la clase y selecciona las observaciones que correspondan. Puedes editar el texto de cada observación si es necesario.Recuerda hacer check en el cuadro de selección para guardar esa anotación.";

export const URL_LOCKER_STUDIO =
  "https://lookerstudio.google.com/reporting/be2efb22-dc5f-4f21-9dfc-0253e763e19f";

// Analytics
export const ANALYTICS_URL = `${BASE_URL}/analytics.php`;

// AI Proxy
export const AI_PROXY_URL = `${BASE_URL}/ai_proxy.php`;

// html2pdf (CDN)
export const HTML2PDF_CDN_URL = 'https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js'

export const URL_DBAS =
  `${BASE_URL}/getDBAs.php`;
export const URL_EBCS =
  `${BASE_URL}/getEBCs.php`;
export const URL_DBA_EBC =
  `${BASE_URL}/dba_ebc/dba_ebc.php`;

// Coberturas
export const SPREADSHEET_ID_COBERTURA = "1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw";
export const WORKSHEET_TITLE_COBERTURA = "historial";
export const WORKSHEET_TITLE_COBERTURA_PRUEBAS = "pruebas";
export const SAVE_COBERTURA_URL = `${API_URL_GS}/save_cobertura.php`;
export const GET_COBERTURA_URL = `${API_URL_GS}/get_coberturas.php`;

// Plan de Aula
export const SPREADSHEET_ID_PLAN_AULA = "1pkFF954kWh1aCAlyMlIjk7eQL1Povn3vO_5aJFxkM4c";
export const WORKSHEET_TITLE_PLAN_AULA = "plan";
export const SAVE_PLAN_AULA_URL = `${API_URL_GS}/savePlanAula.php`;
export const GET_PLAN_AULA_URL = `${API_URL_GS}/getPlanAula.php`;

// Periodos

interface Periodo {
  nombre: string;
  fecha_inicio: Date;
  fecha_fin: Date;
}

export const periodos: Periodo[] = [
  {
    nombre: "UNO",
    fecha_inicio: new Date("2026-01-26"),
    fecha_fin: new Date("2026-03-27"),
  },
  {
    nombre: "DOS",
    fecha_inicio: new Date("2026-03-30"),
    fecha_fin: new Date("2026-06-12"),
  },
  {
    nombre: "TRES",
    fecha_inicio: new Date("2026-07-07"),
    fecha_fin: new Date("2026-09-11"),
  },
  {
    nombre: "CUATRO",
    fecha_inicio: new Date("2026-09-14"),
    fecha_fin: new Date("2026-11-20"),
  },
];
