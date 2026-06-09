import { execSync } from 'child_process';
import fs from 'fs';

const docentes = [
    'ANA SOFIA CARDENAS PETUMA',
    'ANDRES MAYORGA BOTERO',
    'CARLOS ARMANDO RIOS ALVAREZ',
    'BLANCA NELLY MARIN GIRALDO',
    'CARLOS ALBERTO AGUIRRE GONZALEZ',
    'CESAR LEANDRO PATINO VELEZ',
    'DANIEL QUICENO RIVERA',
    'DELCID DE JESUS BUENO ARANDIA',
    'DERLY JOANNA PUERTAS',
    'ILDORY JARAMILLO',
    'JON JAMES VASCO RODRIGUEZ',
    'JAVIER DE JESUS AGUDELO CARVAJAL',
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

function generateRecords(count) {
    const records = [];
    for (let i = 0; i < count; i++) {
        let docenteAusente = randomItem(docentes);
        let docenteCubre;
        do {
            docenteCubre = randomItem(docentes);
        } while (docenteCubre === docenteAusente);

        records.push([
            randomDate(),
            randomItem(dias),
            String(Math.floor(Math.random() * 7) + 1),
            docenteAusente,
            randomItem(grupos),
            docenteCubre,
            randomItem(grupos),
            randomItem(estados),
            randomItem(motivos)
        ]);
    }
    return records;
}

function sendBatch(values, batchNum) {
    const payload = JSON.stringify({
        spreadsheetId: '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw',
        worksheetTitle: 'pruebas',
        values: values
    });

    const tempFile = `batch_${batchNum}.json`;
    fs.writeFileSync(tempFile, payload);

    try {
        const result = execSync(`curl -X POST "https://app.iedeoccidente.com/gs/save_cobertura.php" -H "Content-Type: application/json" --data-binary "@${tempFile}"`, { encoding: 'utf8' });
        console.log(`Lote ${batchNum}:`, result.trim());
    } catch (e) {
        console.log(`Lote ${batchNum} error:`, e.message);
    }

    fs.unlinkSync(tempFile);
}

console.log('Generando 200 registros...');
const allRecords = generateRecords(200);
const batchSize = 50;

for (let i = 0; i < 4; i++) {
    const start = i * batchSize;
    const batch = allRecords.slice(start, start + batchSize);
    console.log(`Enviando lote ${i + 1}...`);
    sendBatch(batch, i + 1);
}

console.log('\n=== 200 registros enviados a hoja pruebas ===');