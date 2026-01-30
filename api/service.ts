import {
  API_URL_GS,
  BASE_URL,
  DOCENTES_URL,
  MATERIAS_URL,
  ESTUDIANTES_URL,
  ANOTADOR_URL,
  ANOTADOR2_URL,
  SAVE_INASISTENCIAS_URL,
  SAVE_ANOTADOR_URL,
} from "../src/constants";

export interface InasistenciaPayload {
  [key: string]: any;
}

export interface AnotadorPayload {
  spreadsheetId: string;
  worksheetTitle: string;
  datos: any[][];
}

export const getInasistencias = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL_GS}/get_inasistencias.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching inasistencias:", error);
    throw error;
  }
};


export const getAnotador = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL_GS}/get_anotador.php`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching inasistencias:", error);
    throw error;
  }
};

export const saveInasistencias = async (payload: InasistenciaPayload) => {
  try {
    const response = await fetch(`${SAVE_INASISTENCIAS_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error saving inasistencias:", error);
    throw error;
  }
};

export const getDocentes = async () => {
  try {
    const response = await fetch(`${DOCENTES_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching docentes:", error);
    throw error;
  }
};

export const getMaterias = async () => {
  try {
    const response = await fetch(`${MATERIAS_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching materias:", error);
    throw error;
  }
};

export const getEstudiantes = async () => {
  try {
    const response = await fetch(`${ESTUDIANTES_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching estudiantes:", error);
    throw error;
  }
};

export const getOpcionesAnotador = async () => {
  try {
    const response = await fetch(`${ANOTADOR_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching opciones anotador:", error);
    throw error;
  }
};

export const getOpcionesAnotador2 = async () => {
  try {
    const response = await fetch(`${ANOTADOR2_URL}`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error fetching opciones anotador:", error);
    throw error;
  }
};

export const saveAnotador = async (payload: AnotadorPayload) => {
  try {
    const response = await fetch(`${SAVE_ANOTADOR_URL}`, {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error("Error saving inasistencias:", error);
    throw error;
  }
};