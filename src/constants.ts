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

// Google Sheets
export const WORKSHEET_TITLE = "Inasistencias";
export const WORKSHEET_TITLE_ANOTADOR = "Datos";
export const SPREADSHEET_ID_ANOTADOR =
  "1Q6EcSvccB7BoJiw9PD2s5J4PB8AJmr-6v-yKhiE4E8k";

// Textos informativos para alertas dismissibles
export const INFO_INASISTENCIA =
  "Selecciona un grado y una asignatura para comenzar a registrar inasistencias. Ahora puedes marcar motivos individuales y observaciones específicas por estudiante.";
export const INFO_ANOTADOR =
  "Completa los datos de la clase y selecciona las observaciones que correspondan. Puedes editar el texto de cada observación si es necesario.Recuerda hacer check en el cuadro de selección para guardar esa anotación.";
