import { execSync } from 'child_process';
import fs from 'fs';
import path from 'path';
import { fileURLToPath } from 'url';

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

const SPREADSHEET_ID = '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw';
const WORKSHEET_TITLE = 'pruebas';
const API_URL = 'https://app.iedeoccidente.com/gs/get_coberturas.php';

const args = process.argv.slice(2);
let confirm = false;
let fechaEspecifica = null;

for (const arg of args) {
    if (arg === '--confirm=true' || arg === '--confirm') confirm = true;
    if (arg.startsWith('--fecha=')) fechaEspecifica = arg.split('=')[1];
}

console.log('='.repeat(60));
console.log('LIMPIEZA DE DATOS DE PRUEBA');
console.log('='.repeat(60));
console.log(`Spreadsheet: ${SPREADSHEET_ID}`);
console.log(`Worksheet: ${WORKSHEET_TITLE}`);
console.log('');

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

function deleteCobertura(fecha, hora, docente_cubre) {
    const payload = JSON.stringify({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
        fecha,
        hora,
        docente_cubre
    });

    const tempFile = path.join(__dirname, 'delete_temp.json');
    fs.writeFileSync(tempFile, payload);

    try {
        const deleteUrl = 'https://app.iedeoccidente.com/gs/delete_cobertura.php';
        const result = execSync(`curl -X POST "${deleteUrl}" -H "Content-Type: application/json" --data-binary "@${tempFile}"`, { encoding: 'utf8' });
        fs.unlinkSync(tempFile);
        return JSON.parse(result.trim());
    } catch (e) {
        if (fs.existsSync(tempFile)) fs.unlinkSync(tempFile);
        return { success: false, error: e.message };
    }
}

function deletePorFecha(fecha) {
    const payload = JSON.stringify({
        spreadsheetId: SPREADSHEET_ID,
        worksheetTitle: WORKSHEET_TITLE,
        fecha
    });

    const tempFile = path.join(__dirname, 'delete_temp.json');
    fs.writeFileSync(tempFile, payload);

    try {
        const deleteUrl = 'https://app.iedeoccidente.com/gs/delete_coberturas_fecha.php';
        const result = execSync(`curl -X POST "${deleteUrl}" -H "Content-Type: application/json" --data-binary "@${tempFile}"`, { encoding: 'utf8' });
        fs.unlinkSync(tempFile);
        return JSON.parse(result.trim());
    } catch (e) {
        if (fs.existsSync(tempFile)) fs.unlinkSync(tempFile);
        return { success: false, error: e.message };
    }
}

const coberturas = fetchCoberturas();
console.log(`Registros encontrados: ${coberturas.length}`);

if (coberturas.length === 0) {
    console.log('');
    console.log('No hay registros para eliminar.');
    console.log('');
    process.exit(0);
}

if (!confirm) {
    console.log('');
    console.log('⚠️  ACCIÓN REQUIERE CONFIRMACIÓN');
    console.log('');
    console.log('Para eliminar TODOS los registros, ejecuta:');
    console.log('  node scripts/pruebas/cleanup_pruebas.js --confirm=true');
    console.log('');
    console.log('Para eliminar solo una fecha específica:');
    console.log('  node scripts/pruebas/cleanup_pruebas.js --fecha=2026-06-08 --confirm=true');
    console.log('');
    console.log('Primeros 5 registros:');
    for (let i = 0; i < Math.min(5, coberturas.length); i++) {
        const c = coberturas[i];
        console.log(`  ${i + 1}. ${c.fecha} (${c.dia_semana}) h${c.hora} - ${c.docente_cubre}`);
    }
    console.log('');
    process.exit(0);
}

console.log('');
console.log('🗑️  ELIMINANDO REGISTROS...');
console.log('');

if (fechaEspecifica) {
    console.log(`Eliminando todos los registros de fecha: ${fechaEspecifica}`);
    const result = deletePorFecha(fechaEspecifica);
    if (result.success) {
        console.log('  ✓ Eliminación exitosa');
    } else {
        console.log(`  ✗ Error: ${result.error || 'Desconocido'}`);
    }
} else {
    let eliminados = 0;
    let errores = 0;

    for (const c of coberturas) {
        const result = deleteCobertura(c.fecha, c.hora, c.docente_cubre);
        if (result.success) {
            eliminados++;
            console.log(`  ✓ ${c.fecha} h${c.hora} - ${c.docente_cubre}`);
        } else {
            errores++;
            console.log(`  ✗ ${c.fecha} h${c.hora} - ${c.docente_cubre}: ${result.error || 'Error'}`);
        }
    }

    console.log('');
    console.log('-'.repeat(60));
    console.log(`RESULTADO: ${eliminados} eliminados, ${errores} errores`);
}

console.log('');
console.log('='.repeat(60));
console.log('FIN DE LIMPIEZA');
console.log('='.repeat(60));