<?php
$content = file_get_contents(__DIR__ . '/src/assets/php/estandaresbasicosdp.php');

// We want to extract Competencias Ciudadanas
$startStr = "'competencias_ciudadanas' => [";
$start = strpos($content, $startStr);
if ($start === false)
    die("Not found\n");

$endStr = "    // ============================================\n    // MATEMÁTICAS"; // Wait, Competencias is at the end of the file in estandaresbasicosdp.php
// Let's just substring from $start to the end of the array.
$section = substr($content, $start);

// Extract lines that look like Grado matches
preg_match_all("/\/\/\s*GRADO\s*(\d+)°(.*?)(?=\/\/\s*GRADO|\z)/s", $section, $matches);

$gradoMap = [
    1 => 'primero',
    2 => 'segundo',
    3 => 'tercero',
    4 => 'cuarto',
    5 => 'quinto',
    6 => 'sexto',
];

$output = "        \"competencias_ciudadanas\" => [\n";
$output .= "            \"nombre\" => \"Competencias Ciudadanas\",\n";
$output .= "            \"tipo\" => \"Transversal\",\n";
$output .= "            \"estandares_por_grado\" => [\n";

foreach ($matches[1] as $index => $gradoNum) {
    if (!isset($gradoMap[$gradoNum]))
        continue;
    $gradoNombre = $gradoMap[$gradoNum];
    $block = $matches[2][$index];

    $output .= "                \"$gradoNombre\" => [\n";

    // Within each grade block, block looks like:
    // 1 => [ 'grupo' => 'Convivencia y Paz', 'estandares' => [ 'std1', 'std2' ] ],
    // 1 => [ ... ]

    preg_match_all("/'grupo'\s*=>\s*'([^']+)'\s*,\s*'estandares'\s*=>\s*\[(.*?)\]\s*\]/s", $block, $groupMatches);

    $itemIndex = 1;
    foreach ($groupMatches[1] as $gIndex => $grupo) {
        $estandaresStr = $groupMatches[2][$gIndex];
        preg_match_all("/'([^']+)'/", $estandaresStr, $stdMatches);

        foreach ($stdMatches[1] as $estandar) {
            $id = sprintf("CC-%d-%02d", $gradoNum, $itemIndex++);
            $estandar = addslashes($estandar);
            $grupo = addslashes($grupo);

            $output .= "                    [\"id\" => \"$id\", \"grado\" => \"$gradoNombre\", \"estandar\" => \"$estandar\", \"dimension\" => \"$grupo\", \"indicadores\" => []],\n";
        }
    }
    $output .= "                ],\n";
}

$output .= "            ]\n";
$output .= "        ]\n";

file_put_contents(__DIR__ . '/cc_out.txt', $output);
