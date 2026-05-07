const STORAGE_KEY = 'anotador_draft';
const SAVE_DELAY = 2000;

export interface DraftData {
  docente: string;
  materia: string;
  grado: string;
  horas: string;
  fecha: string;
  anotacion: string;
  observacion: string;
  timestamp: number;
}

export function useDraftSave() {
  let saveTimeout: ReturnType<typeof setTimeout> | null = null;

  const saveDraft = (data: Partial<DraftData>) => {
    if (saveTimeout) {
      clearTimeout(saveTimeout);
    }
    
    saveTimeout = setTimeout(() => {
      const draft: DraftData = {
        docente: data.docente || '',
        materia: data.materia || '',
        grado: data.grado || '',
        horas: data.horas || '',
        fecha: data.fecha || new Date().toISOString().split('T')[0],
        anotacion: data.anotacion || '',
        observacion: data.observacion || '',
        timestamp: Date.now(),
      };
      
      try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(draft));
      } catch (e) {
        console.warn('No se pudo guardar el borrador:', e);
      }
    }, SAVE_DELAY);
  };

  const loadDraft = (): DraftData | null => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      if (saved) {
        const draft = JSON.parse(saved) as DraftData;
        const hoursSinceSave = (Date.now() - draft.timestamp) / (1000 * 60 * 60);
        if (hoursSinceSave < 24) {
          return draft;
        }
        clearDraft();
      }
    } catch (e) {
      console.warn('No se pudo cargar el borrador:', e);
    }
    return null;
  };

  const clearDraft = () => {
    try {
      localStorage.removeItem(STORAGE_KEY);
    } catch (e) {
      console.warn('No se pudo eliminar el borrador:', e);
    }
  };

  const hasDraft = (): boolean => {
    try {
      const saved = localStorage.getItem(STORAGE_KEY);
      return !!saved;
    } catch {
      return false;
    }
  };

  return {
    saveDraft,
    loadDraft,
    clearDraft,
    hasDraft,
  };
}