export async function fetchAsignaturas() {
  const res = await fetch('https://app.iedeoccidente.com/ig/getMaterias.php')
  const data = await res.json()
  return data.map(m => m.materia)
}

// --- Configuración del período académico ---
export const peridosRange = [{nombre:'PRIMERO',fechas:{inicio:'2026-01-26',fin:'2026-04-01'}}, {nombre:'SEGUNDO',fechas:{inicio:'2026-04-02',fin:'2026-06-12'}}, {nombre:'TERCERO',fechas:{inicio:'2026-07-07',fin:'2026-09-12'}}, {nombre:'CUARTO',fechas:{inicio:'2026-09-13',fin:'2026-11-20'}}]

export function getPeriodoLabel(periodo) {
  const labels = { PRIMERO: 'Primer', SEGUNDO: 'Segundo', TERCERO: 'Tercer', CUARTO: 'Cuarto' }
  return `${labels[periodo] || periodo} Período Académico`
}

export function getPeriodoName(periodo) {
  const labels = { PRIMERO: 'Primer', SEGUNDO: 'Segundo', TERCERO: 'Tercer', CUARTO: 'Cuarto' }
  return labels[periodo] || periodo
}

export function isPeriodoActivo(periodo) {
  const hoy = new Date()
  const p = peridosRange.find(r => r.nombre === periodo)
  if (!p) return false
  return hoy >= new Date(p.fechas.inicio) && hoy <= new Date(p.fechas.fin)
}

export function getCurrentPeriodo() {
  const hoy = new Date()
  for (const p of peridosRange) {
    if (hoy >= new Date(p.fechas.inicio) && hoy <= new Date(p.fechas.fin)) {
      return p.nombre
    }
  }
  return peridosRange[peridosRange.length - 1].nombre
}

const currentPeriodoNumero = getPeriodoName(getCurrentPeriodo())

export const periodoConfig = {
  numero: currentPeriodoNumero,
  anioLectivo: 2026,
  nivel: 'Básica Secundaria y Media'
}

export const periodoLabel = `${currentPeriodoNumero} Período Académico`
export const anioLectivoLabel = `Año Lectivo ${periodoConfig.anioLectivo}`

export const institutionHeader = `INSTITUCION EDUCATIVA OFICIAL INSTITUTO GUATICA
Resolución de aprobación N° 002879 del 13 de Diciembre de 2017
NIT: 891.401.438-5 DANE: 166318000537`

export const documentTitle = `ACTA DE ENTREGA PLANES DE MEJORAMIENTO ACADEMICO A PADRES - ${periodoConfig.numero.toUpperCase()} PERIODO ACADEMICO AÑO LECTIVO ${periodoConfig.anioLectivo} ${periodoConfig.nivel.toUpperCase()}`

export async function fetchEstudiantes() {
  const res = await fetch('https://app.iedeoccidente.com/ig/getEstudiantes.php')
  const data = await res.json()
  // data: [{nombre, grado}, ...]
  const estudiantes = data.map(e => ({
    nombreCompleto: e.nombre,
    grupo: String(e.grado)
  }))
  const grupos = [...new Set(estudiantes.map(e => e.grupo))].sort((a, b) => Number(a) - Number(b))
  return { estudiantes, grupos }
}

export async function fetchDocentes() {
  const res = await fetch('https://app.iedeoccidente.com/ig/getprofes.php')
  const data = await res.json()
  // data: ["NOMBRE1", "NOMBRE2", ...]
  return data
}
