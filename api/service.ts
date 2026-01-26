import { API_URL, BASE_URL } from '../src/constants';

export interface InasistenciaPayload {
  [key: string]: any;
}

export const getInasistencias = async (payload: InasistenciaPayload = {}) => {
  try {
    const response = await fetch(`${API_URL}/get_inasistencias.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching inasistencias:', error);
    throw error;
  }
};

export const saveInasistencias = async (payload: InasistenciaPayload) => {
  try {
    const response = await fetch(`${API_URL}/save_inasistencias.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(payload),
    });

    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }

    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error saving inasistencias:', error);
    throw error;
  }
};

export const getDocentes = async () => {
  try {
    const response = await fetch(`${BASE_URL}/getprofes.php`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching docentes:', error);
    throw error;
  }
};

export const getMaterias = async () => {
  try {
    const response = await fetch(`${BASE_URL}/getMaterias.php`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching materias:', error);
    throw error;
  }
};

export const getEstudiantes = async () => {
  try {
    const response = await fetch(`${BASE_URL}/getEstudiantes.php`);
    if (!response.ok) {
      throw new Error(`Error: ${response.status}`);
    }
    const data = await response.json();
    return data;
  } catch (error) {
    console.error('Error fetching estudiantes:', error);
    throw error;
  }
};