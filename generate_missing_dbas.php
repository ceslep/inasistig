<?php
// Evitar la salida del echo en el archivo deepseek
ob_start();
require_once 'src/assets/php/deepseek_php_20260223_1888f6.php';
ob_end_clean();

$gradosMap = [
    1 => "primero",
    2 => "segundo",
    3 => "tercero",
    4 => "cuarto",
    5 => "quinto",
    6 => "sexto",
    7 => "septimo",
    8 => "octavo",
    9 => "noveno",
    10 => "decimo",
    11 => "once"
];

function formatPHPArray($prefijo, $nombreCorto, $nombreCargo, $categoria, $dbasRaw, $extraKey = "dimension")
{
    global $gradosMap;
    $output = "        \"$nombreCorto\" => [\n";
    $output .= "            \"nombre\" => \"$nombreCargo\",\n";
    $output .= "            \"categoria\" => \"$categoria\",\n";
    $output .= "            \"dbas\" => [\n";

    foreach ($dbasRaw as $gNum => $data) {
        $gradoStr = $gradosMap[$gNum];
        $textos = $data['dba'] ?? [];

        $extras = $data['ejes'] ?? $data['competencias'] ?? $data['componentes'] ?? $data['lenguajes'] ?? [];
        if (empty($extras) && isset($data['dimensiones']))
            $extras = $data['dimensiones'];

        foreach ($textos as $index => $texto) {
            $numFormato = str_pad($index + 1, 2, "0", STR_PAD_LEFT);
            $id = "{$prefijo}-{$gNum}-{$numFormato}";

            // Asignar el extra (eje, competencia, etc) según el índice, o el primero si no hay suficientes
            $extraVal = $extras[$index] ?? ($extras[0] ?? "");

            $escapedTexto = str_replace('"', '\"', $texto);
            $output .= "                [\"id\" => \"$id\", \"grado\" => \"$gradoStr\", \"descripcion\" => \"$escapedTexto\", \"evidencia\" => \"\", \"$extraKey\" => \"$extraVal\"],\n";
        }
    }

    $output = rtrim($output, ",\n") . "\n";
    $output .= "            ]\n";
    $output .= "        ],\n";
    return $output;
}

// 1. Cátedra de la Paz
$finalOutput = formatPHPArray("PAZ", "catedra_paz", "Cátedra de la Paz", "Transversal", generarDBAPaz(), "eje");

// 2. Formación Ciudadana
$finalOutput .= formatPHPArray("CIU", "formacion_ciudadana", "Competencias Ciudadanas", "Transversal", generarDBACiudadana(), "competencia");

// 3. Cátedra Afrocolombiana
$finalOutput .= formatPHPArray("AFR", "catedra_afrocolombiana", "Cátedra de Estudios Afrocolombianos", "Transversal", generarDBAAfro(), "eje");

// 4. Educación Religiosa (Reemplazo completo)
$finalOutput .= formatPHPArray("REL", "educacion_religiosa", "Educación Religiosa", "Opcional (según PEI)", generarDBAReligion(), "eje");

// 5. Preescolar
$preescolarOutput = "        \"preescolar\" => [\n";
$preescolarOutput .= "            \"nombre\" => \"Preescolar\",\n";
$preescolarOutput .= "            \"categoria\" => \"Transición\",\n";
$preescolarOutput .= "            \"dbas\" => [\n";
$preescolar = $dba_colombia_completo['preescolar']['dimensiones'];
$idx = 1;
foreach ($preescolar as $dimKey => $dimData) {
    $dimNombre = $dimData['nombre'];
    foreach ($dimData['dba'] as $texto) {
        $numFormato = str_pad($idx, 2, "0", STR_PAD_LEFT);
        $id = "PRE-COG-{$numFormato}";
        $escapedTexto = str_replace('"', '\"', $texto);
        $preescolarOutput .= "                [\"id\" => \"$id\", \"grado\" => \"transicion\", \"descripcion\" => \"$escapedTexto\", \"evidencia\" => \"\", \"dimension\" => \"$dimNombre\"],\n";
        $idx++;
    }
}
$preescolarOutput = rtrim($preescolarOutput, ",\n") . "\n";
$preescolarOutput .= "            ]\n";
$preescolarOutput .= "        ],\n";

$finalOutput = $preescolarOutput . $finalOutput;

file_put_contents('c:/Users/cesar/inasistig/extracted_dbas.txt', $finalOutput);
?>