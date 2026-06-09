$docentes = @(
    "ANA SOFIA CARDENAS PETUMA",
    "ANDRES MAYORGA BOTERO",
    "CARLOS ARMANDO RIOS ALVAREZ",
    "BLANCA NELLY MARIN GIRALDO",
    "CARLOS ALBERTO AGUIRRE GONZALEZ",
    "CESAR LEANDRO PATINO VELEZ",
    "DANIEL QUICENO RIVERA",
    "DELCID DE JESUS BUENO ARANDIA",
    "DERLY JOANNA PUERTAS",
    "ILDORY JARAMILLO",
    "JON JAMES VASCO RODRIGUEZ",
    "JAVIER DE JESUS AGUDELO CARVAJAL",
    "JOHN EDWIN ARBOLEDA ACEVEDO",
    "JHON JAIRO VELEZ TEJADA",
    "JOSE DARIO OREJUELA SANCHEZ"
)
$grupos = @("601","602","701","702","801","802","901","902","1001","1101","1102")
$dias = @("lunes","martes","miercoles","jueves","viernes")
$estados = @("aprobado","aprobado","aprobado","aprobado","pendiente")
$motivos = @("Enfermedad","Cita medica","Curso","Emergencia familiar","Comite","Capacitacion")
$rnd = New-Object System.Random

function Get-RandomDate {
    $daysAgo = $rnd.Next(0, 60)
    return (Get-Date).AddDays(-$daysAgo).ToString("yyyy-MM-dd")
}

$allValues = @()
for ($i = 0; $i -lt 200; $i++) {
    $docenteAusente = $docentes[$rnd.Next(0, $docentes.Length)]
    $docenteCubre = $docentes | Where-Object { $_ -ne $docenteAusente } | Get-Random
    $grupo = $grupos[$rnd.Next(0, $grupos.Length)]
    $dia = $dias[$rnd.Next(0, $dias.Length)]
    $hora = $rnd.Next(1, 8)
    $fecha = Get-RandomDate
    $estado = $estados[$rnd.Next(0, $estados.Length)]
    $motivo = $motivos[$rnd.Next(0, $motivos.Length)]

    $allValues += ,@($fecha, $dia, [string]$hora, $docenteAusente, $grupo, $docenteCubre, $grupo, $estado, $motivo)
}

$batchSize = 50
$totalRecords = 200

for ($batch = 0; $batch -lt ($totalRecords / $batchSize); $batch++) {
    $startIdx = $batch * $batchSize
    $batchValues = $allValues[$startIdx..($startIdx + $batchSize - 1)]

    $jsonObj = @{
        spreadsheetId = "1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw"
        worksheetTitle = "pruebas"
        values = $batchValues
    }

    $jsonStr = $jsonObj | ConvertTo-Json -Depth 10
    $tempFile = "batch_$batch.json"
    [System.IO.File]::WriteAllText($tempFile, $jsonStr, [System.Text.Encoding]::UTF8)

    Write-Host "Enviando lote $($batch + 1)..."
    $cmd = "curl.exe -X POST ""https://app.iedeoccidente.com/gs/save_cobertura.php"" -H ""Content-Type: application/json"" --data-binary ""@$tempFile"""
    cmd /c $cmd

    Remove-Item $tempFile -Force -ErrorAction SilentlyContinue
    Write-Host "Lote $($batch + 1) completado"
}

Write-Host ""
Write-Host "=== 200 registros generados exitosamente ==="