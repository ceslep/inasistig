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

function getDocentesList() {
    return horariosData.map(h => h.docente);
}

function getHorasLibresDocente(docente) {
    const h = horariosData.find(x => x.docente === docente);
    if (!h) return { horasClase: 0, horasLibres: 0, libres: [] };

    let horasClase = 0;
    let horasLibres = 0;
    const libres = [];

    for (const dia of DIAS) {
        const jornada = h[dia] || [];
        for (let hora = 0; hora < jornada.length; hora++) {
            const slot = jornada[hora];
            if (!slot || slot === '') {
                horasLibres++;
                libres.push({ dia, hora });
            } else if (slot !== 'DESC' && slot !== 'PEDAG' && slot !== 'DEESC') {
                horasClase++;
            }
        }
    }

    return { horasClase, horasLibres, libres };
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
let docenteBuscado = '';

for (const arg of args) {
    if (arg.startsWith('--docente=')) {
        docenteBuscado = arg.split('=')[1].replace(/_/g, ' ');
    }
}

if (!docenteBuscado) {
    console.log('');
    console.log('=== ANÁLISIS DE COBERTURA ===');
    console.log('');
    console.log('Usage: node analyze_cobertura.js --docente="NOMBRE COMPLETO"');
    console.log('');
    console.log('Docentes disponibles:');
    const docentes = getDocentesList();
    for (let i = 0; i < docentes.length; i++) {
        console.log(`  ${i + 1}. ${docentes[i]}`);
    }
    console.log('');
    process.exit(0);
}

console.log('='.repeat(60));
console.log('ANÁLISIS DE COBERTURA');
console.log('='.repeat(60));

const docente = horariosData.find(h => h.docente === docenteBuscado);

if (!docente) {
    console.log(`❌ Docente no encontrado: ${docenteBuscado}`);
    console.log('');
    console.log('Docentes disponibles:');
    for (const d of getDocentesList()) {
        console.log(`  - ${d}`);
    }
    process.exit(1);
}

const coberturas = fetchCoberturas();
console.log(`Docente: ${docenteBuscado}`);
console.log(`Registros históricos: ${coberturas.length}`);
console.log('');

const hoy = new Date();
const hace14dias = new Date(hoy);
hace14dias.setDate(hoy.getDate() - 14);
const hace7dias = new Date(hoy);
hace7dias.setDate(hoy.getDate() - 7);
const semanaActual = getSemanaDelAno(hoy.toISOString().split('T')[0]);

const { horasClase, horasLibres, libres } = getHorasLibresDocente(docenteBuscado);

const coberturasSemana = coberturas.filter(c => {
    if (c.docente_cubre !== docenteBuscado) return false;
    if (c.estado !== 'aprobado') return false;
    const cpSemana = getSemanaDelAno(c.fecha);
    return cpSemana === semanaActual;
});

const hace14diasStr = hace14dias.toISOString().split('T')[0];
const hace7diasStr = hace7dias.toISOString().split('T')[0];

const ultimaSemanaCoberturas = coberturas.filter(c => {
    if (c.docente_cubre !== docenteBuscado) return false;
    if (c.estado !== 'aprobado') return false;
    if (!c.fecha) return false;
    const cpFecha = new Date(c.fecha + 'T00:00:00');
    return cpFecha >= hace14dias && cpFecha < hace7dias;
});

console.log('-'.repeat(60));
console.log('HORARIO:');
console.log('-'.repeat(60));
console.log(`Horas clase: ${horasClase}`);
console.log(`Horas libres: ${horasLibres}`);
console.log('');

console.log('-'.repeat(60));
console.log('COBERTURAS HISTÓRICAS (hoja pruebas):');
console.log('-'.repeat(60));
console.log(`Esta semana (${semanaActual}): ${coberturasSemana.length} cobertura(s)`);
for (const c of coberturasSemana) {
    console.log(`  - ${c.fecha} (${c.dia_semana}) h${c.hora}: ${c.docente_ausente} → ${c.grupo_a_cubrir}`);
}
console.log(`Hace 1-2 semanas: ${ultimaSemanaCoberturas.length} cobertura(s)`);
for (const c of ultimaSemanaCoberturas) {
    console.log(`  - ${c.fecha} (${c.dia_semana}) h${c.hora}: ${c.docente_ausente}`);
}
console.log('');

console.log('-'.repeat(60));
console.log('LÍMITES:');
console.log('-'.repeat(60));
const esSinLimite = ROLES_SIN_LIMITE.some(r => docenteBuscado.includes(r));
if (esSinLimite) {
    console.log('⚡ Rol sin límite (ORIENTADOR/COORDINADOR/BIBLIOTECA/AUDITORIO)');
} else {
    const usadasSemana = coberturasSemana.length;
    const disponiblesDia = horasLibres > 0 ? 1 : 0;
    const disponiblesSemana = Math.max(0, 2 - usadasSemana);

    console.log(`1h/día: ${usadasSemana >= 1 ? '0' : '1'} disponible(s) ${usadasSemana >= 1 ? '⚠️ (límite alcanzado)' : '✓'}`);
    console.log(`2h/semana: ${disponiblesSemana} disponible(s) ${usadasSemana >= 2 ? '⚠️ (límite alcanzado)' : '✓'}`);
}
console.log('');

console.log('-'.repeat(60));
console.log('SLOTS LIBRES EN HORARIO:');
console.log('-'.repeat(60));
if (libres.length === 0) {
    console.log('No tiene horas libres en su horario.');
} else {
    for (const l of libres) {
        const diaIdx = DIAS.indexOf(l.dia);
        const coberturaEnSlot = coberturasSemana.find(c =>
            c.dia_semana === l.dia && c.hora === l.hora + 1
        );

        const estado = coberturaEnSlot
            ? `✓ (ya cubrió ${coberturaEnSlot.fecha})`
            : '✓ (disponible)';

        console.log(`  ${DIAS[diaIdx].toUpperCase()} h${l.hora + 1} ${estado}`);
    }
}
console.log('');

console.log('-'.repeat(60));
console.log('ESTADO:');
console.log('-'.repeat(60));
const puedeCubrir = horasLibres > 0 && (esSinLimite || coberturasSemana.length < 2);
if (puedeCubrir) {
    const disponibles = esSinLimite ? horasLibres : Math.max(0, 2 - coberturasSemana.length);
    console.log(`✅ PUEDE CUBRIR (${disponibles} hora(s) disponible(s))`);
} else {
    if (coberturasSemana.length >= 2) {
        console.log('⛔ NO PUEDE CUBRIR - Límite semanal alcanzado (2h)');
    } else {
        console.log('⛔ NO PUEDE CUBRIR - No tiene horas libres en su horario');
    }
}

console.log('');
console.log('='.repeat(60));
console.log('FIN DEL ANÁLISIS');
console.log('='.repeat(60));