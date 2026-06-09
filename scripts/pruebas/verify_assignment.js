import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const SPREADSHEET_ID = '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw';
const WORKSHEET_TITLE = 'pruebas';
const API_URL = 'https://app.iedeoccidente.com/gs/get_coberturas.php';

const ROLES_SIN_LIMITE = ['ORIENTACION', 'ORIENTADOR', 'COORDINADOR', 'BIBLIOTECA', 'AUDITORIO'];
const DIAS = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes'];

const horariosData = JSON.parse(fs.readFileSync(path.join(__dirname, '../../src/lib/horarios.json'), 'utf8'));

function getSemanaDelAno(fecha) {
    const date = new Date(fecha + 'T00:00:00');
    const start = new Date(date.getFullYear(), 0, 1);
    const diff = date.getTime() - start.getTime();
    const oneWeek = 604800000;
    return Math.floor(diff / oneWeek);
}

function getGrupoFromSlot(slot) {
    if (!slot) return null;
    const match = slot.match(/(\d{3,4})$/);
    return match ? match[1] : null;
}

function getSlotsDelDia(dia, horarios) {
    const slots = [];
    for (const h of horarios) {
        const jornada = h[dia] || [];
        for (let hora = 0; hora < jornada.length; hora++) {
            const slot = jornada[hora];
            let tipo = 'libre';
            if (!slot || slot === '') tipo = 'libre';
            else if (slot === 'DESC') tipo = 'desc';
            else if (slot === 'PEDAG' || slot === 'DEESC') tipo = 'pedag';
            else tipo = 'clase';

            slots.push({
                hora,
                docente: h.docente,
                slot,
                tipo,
                docenteAusente: null,
                grupoAusente: null
            });
        }
    }
    return slots;
}

function aplicarAusencias(slots, ausencias) {
    return slots.map(s => {
        const ausencia = ausencias.find(a =>
            (a.tipo === 'docente' && a.nombre === s.docente) ||
            (a.tipo === 'grupo' && s.slot && getGrupoFromSlot(s.slot) === a.nombre)
        );
        if (ausencia && s.tipo === 'clase') {
            return {
                ...s,
                tipo: ausencia.tipo === 'grupo' ? 'libre' : 'libre_ausencia',
                docenteAusente: ausencia.tipo === 'docente' ? ausencia.nombre : s.docente,
                grupoAusente: ausencia.tipo === 'grupo' ? ausencia.nombre : getGrupoFromSlot(s.slot),
                motivoAusencia: ausencia.motivo || ''
            };
        }
        return s;
    });
}

function getSlotsLibresPorAusencia(slots) {
    return slots.filter(s => s.tipo === 'libre_ausencia');
}

function construirCargaDiariaHistorica(coberturasPrevias, fechaActual) {
    const carga = new Map();
    for (const cp of coberturasPrevias) {
        if (cp.estado !== 'aprobado') continue;
        if (cp.fecha !== fechaActual) continue;
        const doc = cp.docente_cubre;
        if (!doc) continue;
        if (ROLES_SIN_LIMITE.some(r => doc.includes(r))) continue;
        carga.set(doc, (carga.get(doc) || 0) + 1);
    }
    return carga;
}

function getPosiblesCobradores(slot, dia, horarios, cargaDiariaSesion, horasCubiertasSemana, indiceAusencias, asignacionesSesion) {
    return horarios
        .filter(h => {
            if (asignacionesSesion.some(c => c.docenteAusente === h.docente)) return false;

            const jornada = h[dia] || [];
            const slotDelDocente = jornada[slot.hora];

            if (slotDelDocente !== '' && slotDelDocente !== undefined) {
                const grupoDelSlot = getGrupoFromSlot(slotDelDocente);
                if (grupoDelSlot && slot.grupoAusente && slot.grupoAusente !== grupoDelSlot) return false;
                if (slotDelDocente !== '' && !slot.grupoAusente) return false;
            }

            if (slot.hora === 6) {
                const allDaysLibre = DIAS.every(d => {
                    const j = h[d] || [];
                    return j[6] === '';
                });
                if (allDaysLibre) return false;
            }

            const esSinLimite = ROLES_SIN_LIMITE.some(r => h.docente.includes(r));

            const yaAsignadoEnSesion = asignacionesSesion.some(c => c.docenteCubre === h.docente);
            if (yaAsignadoEnSesion && !esSinLimite) return false;

            const cargaSesion = cargaDiariaSesion.get(h.docente) || 0;
            if (cargaSesion >= 1 && !esSinLimite) return false;

            const horasSemana = horasCubiertasSemana.get(h.docente) || 0;
            if (horasSemana >= 2 && !esSinLimite) return false;

            return true;
        })
        .map(h => ({
            docente: h.docente,
            indice: indiceAusencias.get(h.docente) || 0
        }))
        .sort((a, b) => b.indice - a.indice);
}

function fetchCoberturas() {
    const url = `${API_URL}?spreadsheetId=${SPREADSHEET_ID}&worksheetTitle=${WORKSHEET_TITLE}`;
    const result = execSync(`curl -s "${url}"`, { encoding: 'utf8' });
    const data = JSON.parse(result);
    if (!data.values) return [];

    return data.values
        .filter(row => row[0] && row[0] !== 'fecha')
        .map(row => ({
            fecha: row[0] || '',
            dia_semana: row[1] || '',
            hora: parseInt(row[2]) || 0,
            docente_ausente: row[3] || '',
            grupo_ausente: row[4] || '',
            docente_cubre: row[5] || '',
            grupo_a_cubrir: row[6] || '',
            estado: row[7] || 'pendiente',
            motivo: row[8] || ''
        }));
}

const args = process.argv.slice(2);
let dia = 'lunes';
let fecha = new Date().toISOString().split('T')[0];

for (const arg of args) {
    if (arg.startsWith('--dia=')) dia = arg.split('=')[1];
    if (arg.startsWith('--fecha=')) fecha = arg.split('=')[1];
}

console.log('='.repeat(60));
console.log('VERIFICACIÓN DE ASIGNACIÓN DE COBERTURAS');
console.log('='.repeat(60));
console.log(`Día: ${dia}`);
console.log(`Fecha: ${fecha}`);
console.log(`Worksheet: ${WORKSHEET_TITLE}`);
console.log('');

const coberturasPrevias = fetchCoberturas();
console.log(`Registros históricos encontrados: ${coberturasPrevias.length}`);
console.log('');

const hoy = new Date(fecha + 'T00:00:00');
const hace14dias = new Date(hoy);
hace14dias.setDate(hoy.getDate() - 14);
const hace7dias = new Date(hoy);
hace7dias.setDate(hoy.getDate() - 7);
const semanaActual = getSemanaDelAno(fecha);

const horasCubiertasSemana = new Map();
for (const cp of coberturasPrevias) {
    if (cp.estado !== 'aprobado') continue;
    const cpSemana = getSemanaDelAno(cp.fecha);
    if (cpSemana !== semanaActual) continue;
    horasCubiertasSemana.set(cp.docente_cubre, (horasCubiertasSemana.get(cp.docente_cubre) || 0) + 1);
}

const indiceAusencias = new Map();
for (const cp of coberturasPrevias) {
    const cpFecha = new Date(cp.fecha + 'T00:00:00');
    if (cpFecha < hace14dias || cpFecha >= hace7dias) continue;
    if (cp.docente_ausente) {
        indiceAusencias.set(cp.docente_ausente, (indiceAusencias.get(cp.docente_ausente) || 0) + 1);
    }
}

const slotsDelDia = getSlotsDelDia(dia, horariosData);

const ausencias = [
    ...coberturasPrevias
        .filter(c => c.fecha === fecha && c.dia_semana === dia)
        .map(c => ({ tipo: 'docente', nombre: c.docente_ausente, motivo: c.motivo }))
];

const slotsConAusencia = aplicarAusencias(slotsDelDia, ausencias);
const libresPorAusencia = getSlotsLibresPorAusencia(slotsConAusencia);

console.log('-' .repeat(60));
console.log('SLOTS QUE NECESITAN COBERTURA:');
console.log('-'.repeat(60));

const cargaDiariaSesion = new Map();

if (libresPorAusencia.length === 0) {
    console.log('No hay slots que requieran cobertura para este día.');
} else {
    for (const slot of libresPorAusencia) {
        console.log('');
        console.log(`📌 Slot: hora ${slot.hora + 1} - Docente ausente: ${slot.docente}`);
        console.log(`   Grupo: ${slot.grupoAusente || 'N/A'}`);
        console.log(`   Motivo: ${slot.motivoAusencia || 'N/A'}`);

        const posibles = getPosiblesCobradores(
            slot,
            dia,
            horariosData,
            cargaDiariaSesion,
            horasCubiertasSemana,
            indiceAusencias,
            []
        );

        if (posibles.length === 0) {
            console.log('   ⚠️  NO HAY DOCENTES DISPONIBLES');
        } else {
            console.log('   Posibles cobradores (ordenados por disponibilidad):');
            for (let i = 0; i < Math.min(posibles.length, 5); i++) {
                const p = posibles[i];
                const cargaSemana = horasCubiertasSemana.get(p.docente) || 0;
                const cargaDia = cargaDiariaSesion.get(p.docente) || 0;
                const esSinLimite = ROLES_SIN_LIMITE.some(r => p.docente.includes(r));

                console.log(`   ${i + 1}. ${p.docente}`);
                console.log(`      - Ausencias en 2 sem: ${p.indice}`);
                console.log(`      - Coberturas semana: ${cargaSemana}/2 ${esSinLimite ? '(sin límite)' : ''}`);
                console.log(`      - Coberturas hoy: ${cargaDia}/1`);
            }

            const asignado = posibles[0];
            const esSinLimiteAsignado = ROLES_SIN_LIMITE.some(r => asignado.docente.includes(r));

            console.log('');
            console.log(`   ✅ ASIGNADO: ${asignado.docente}`);
            if (!esSinLimiteAsignado) {
                const newCarga = (cargaDiariaSesion.get(asignado.docente) || 0) + 1;
                const newSemana = (horasCubiertasSemana.get(asignado.docente) || 0) + 1;
                console.log(`      - Carga después: ${newCarga}/1 (diario), ${newSemana}/2 (semanal)`);
            }
            cargaDiariaSesion.set(asignado.docente, (cargaDiariaSesion.get(asignado.docente) || 0) + 1);
        }
    }
}

console.log('');
console.log('-'.repeat(60));
console.log('RESUMEN DE LÍMITES:');
console.log('-'.repeat(60));

const docentesConCobertura = [...cargaDiariaSesion.entries()];
if (docentesConCobertura.length === 0) {
    console.log('No hay docentes asignados.');
} else {
    for (const [docente, carga] of docentesConCobertura) {
        const semana = horasCubiertasSemana.get(docente) || 0;
        const esSinLimite = ROLES_SIN_LIMITE.some(r => docente.includes(r));

        const estadoDia = carga >= 1 && !esSinLimite ? '⚠️' : '✓';
        const estadoSemana = semana >= 2 && !esSinLimite ? '⚠️' : '✓';

        console.log(`${docente}: ${carga}/1 (día) ${estadoDia} | ${semana}/2 (semana) ${estadoSemana}`);
    }
}

console.log('');
console.log('='.repeat(60));
console.log('FIN DE VERIFICACIÓN');
console.log('='.repeat(60));