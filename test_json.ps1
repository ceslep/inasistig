$docentes = @('ANA SOFIA CARDENAS PETUMA','ANDRES MAYORGA BOTERO')
$grupos = @('601','602')
$dias = @('lunes','martes')
$estados = @('aprobado')
$motivos = @('Enfermedad')

$allValues = @()
$allValues += ,@('2026-05-28', 'lunes', '1', $docentes[0], '601', $docentes[1], '601', 'aprobado', 'Enfermedad')
$allValues += ,@('2026-05-29', 'martes', '2', $docentes[1], '602', $docentes[0], '602', 'aprobado', 'Enfermedad')

$jsonObj = @{
    spreadsheetId = '1N-94FYW5kvGmOcJ4CCqQRWC71guFLxlXltlM7GvDQDw'
    worksheetTitle = 'pruebas'
    values = $allValues
}

$jsonStr = $jsonObj | ConvertTo-Json -Depth 10 -Compress
Write-Host $jsonStr

$tempFile = "test_batch.json"
[System.IO.File]::WriteAllText($tempFile, $jsonStr, [System.Text.Encoding]::UTF8)
Write-Host "File created"