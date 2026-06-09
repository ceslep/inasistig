import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';

const SPREADSHEET_ID = '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw';
const WORKSHEET_TITLE = 'pruebas';
const API_URL = 'https://app.iedeoccidente.com/gs/save_cobertura.php';

const docentes = [
    'ANA SOFIA CARDENAS PETUMA',
    'ANDRES MAYORGA BOTERO',
    'CARLOS ALBERTO AGUIRRE GONZALEZ',
    'CARLOS ARMANDO RIOS ALVAREZ',
    'CESAR LEANDRO PATINO VELEZ',
    'BLANCA NELLY MARIN GIRALDO',
    'DANIEL QUICENO RIVERA',
    'DELCID DE JESUS BUENO ARANDIA',
    'DERLY JOANNA PUERTAS',
    'ILDORY JARAMILLO',
    'JAVIER DE JESUS AGUDELO CARVAJAL',
    'JON JAMES VASCO RODRIGUEZ',
    'JOHN EDWIN ARBOLEDA ACEVEDO',
    'JHON JAIRO VELEZ TEJADA',
    'JOSE DARIO OREJUELA SANCHEZ'
];

const grupos = ['601','602','701','702','801','802','901','902','1001','1101','1102'];
const dias = ['lunes','martes','miercoles','jueves','viernes'];
const estados = ['aprobado','aprobado','aprobado','aprobado','pendiente'];
const motivos = ['Enfermedad','Cita medica','Curso','Emergencia familiar','Comite','Capacitacion'];

function randomDate(daysAgo = 60) {
    const d = new Date();
    d.setDate(d.getDate() - Math.floor(Math.random() * daysAgo));
    return d.toISOString().split('T')[0];
}

function randomItem(arr) {
    return arr[Math.floor(Math.random() * arr.length)];
}

function randomDocente(distinctFrom = null) {
    let d = randomItem(docentes);
    if (distinctFrom) {
        while (d === distinctFrom) d = randomItem(docentes);
    }
    return d;
}

function generateRecords(count) {
    const records = [];
    for (let i = 0; i < count; i++) {
        const docenteAusente = randomDocente();
        records.push([
            randomDate(),
            randomItem(dias),
            String(Math.floor(Math.random() * 7) + 1),
            docenteAusente,
            randomItem(grupos),
            randomDocente(docenteAusente),
            randomItem(grupos),
            randomItem(estados),
            randomItem(motivos)
        ]);
    }
    return records;
}

function sendBatch(values) {
    const payload = JSON.stringify({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
        values
    });

    const tempFile = path.join(__dirname, 'batch_temp.json');
    fs.writeFileSync(tempFile, payload);

    try {
        const result = execSync(`curl -X POST "${API_URL}" -H "Content-Type: application/json" --data-binary "@${tempFile}"`, { encoding: 'utf8' });
        fs.unlinkSync(tempFile);
        return JSON.parse(result.trim());
    } catch (e) {
        if (fs.existsSync(tempFile)) fs.unlinkSync(tempFile);
        return { success: false, error: e.message };
    }
}

const args = process.argv.slice(2);
let count = 200;
for (const arg of args) {
    if (arg.startsWith('--count=')) {
        count = parseInt(arg.split('=')[1]) || 200;
    }
}

console.log('='.repeat(50));
console.log('GENERAR DATOS DE PRUEBA - HOJA PRUEBAS');
console.log('='.repeat(50));
console.log(`Spreadsheet: ${SPREADSHEET_ID}`);
console.log(`Worksheet: ${WORKSHEET_TITLE}`);
console.log(`Registros a generar: ${count}`);
console.log('');

const allRecords = generateRecords(count);
const batchSize = 50;
let totalInserted = 0;

for (let i = 0; i < Math.ceil(count / batchSize); i++) {
    const start = i * batchSize;
    const batch = allRecords.slice(start, start + batchSize);
    console.log(`Enviando lote ${i + 1}/${Math.ceil(count / batchSize)} (${batch.length} registros)...`);

    const result = sendBatch(batch);
    if (result.success) {
        const inserted = result.rowsInserted || batch.length;
        totalInserted += inserted;
        console.log(`  ✓ Lote ${i + 1}: ${inserted} registros insertados`);
    } else {
        console.log(`  ✗ Lote ${i + 1}: Error - ${result.error || 'Desconocido'}`);
    }
}

console.log('');
console.log('='.repeat(50));
console.log(`RESULTADO: ${totalInserted} registros generados`);
console.log('='.repeat(50));
console.log('');
console.log('Para verificar los datos, ejecuta:');
console.log('  node scripts/pruebas/verify_assignment.js --dia=lunes --fecha=2026-06-08');
console.log('');