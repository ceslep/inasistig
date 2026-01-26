import { API_URL } from '../src/constants';

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