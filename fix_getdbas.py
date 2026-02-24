import os

filepath = r'c:\Users\cesar\inasistig\src\assets\php\getDBAs.php'

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix 1: Fix areaMap values that don't match data array keys
replacements = {
    "'etica' => 'etica'": "'etica' => 'etica_valores'",
    "'ética' => 'etica'": "'ética' => 'etica_valores'",
    "'ETICA' => 'etica'": "'etica_valores' => 'etica_valores'",
    "'ÉTICA' => 'etica'": "'etica y valores' => 'etica_valores'",
    "'tecnologia' => 'tecnologia'": "'tecnologia' => 'tecnologia_informatica'",
    "'tecnología' => 'tecnologia'": "'tecnología' => 'tecnologia_informatica'",
    "'TECNOLOGIA' => 'tecnologia'": "'tecnologia_informatica' => 'tecnologia_informatica'",
    "'TECNOLOGÍA' => 'tecnologia'": "'tecnologia e informatica' => 'tecnologia_informatica'",
    "'tech' => 'tecnologia'": "'tech' => 'tecnologia_informatica'",
    "'religion' => 'religion'": "'religion' => 'educacion_religiosa'",
    "'religión' => 'religion'": "'religión' => 'educacion_religiosa'",
    "'RELIGION' => 'religion'": "'educacion_religiosa' => 'educacion_religiosa'",
    "'RELIGIÓN' => 'religion'": "'educacion religiosa' => 'educacion_religiosa'",
    "'ambiental' => 'ambiental',\r\n    'ambiental' => 'ambiental'": "'ambiental' => 'educacion_ambiental',\r\n    'educacion_ambiental' => 'educacion_ambiental'",
    "'AMBIENTAL' => 'ambiental'": "'educacion ambiental' => 'educacion_ambiental'",
    "'sexual' => 'sexual',\r\n    'sexual' => 'sexual'": "'sexual' => 'educacion_sexual',\r\n    'educacion_sexual' => 'educacion_sexual'",
    "'SEXUAL' => 'sexual'": "'educacion sexual' => 'educacion_sexual'",
    "'economia' => 'economia'": "'economia' => 'educacion_economica'",
    "'economía' => 'economia'": "'economía' => 'educacion_economica'",
    "'ECONOMIA' => 'economia'": "'educacion_economica' => 'educacion_economica'",
    "'ECONOMÍA' => 'economia'": "'educacion economica' => 'educacion_economica'",
}

for old, new in replacements.items():
    if old in content:
        content = content.replace(old, new)
        print(f"Replaced: {old[:40]}...")
    else:
        print(f"NOT FOUND: {old[:40]}...")

# Fix 2: Remove lines 500-502 that overwrite $area and $grado
# Replace the block that overwrites with only $id extraction
old_block = "// Manejo de parámetros GET para filtrar\r\n$area = isset($_GET['area']) ? strtolower($_GET['area']) : null;\r\n$grado = isset($_GET['grado']) ? strtolower($_GET['grado']) : null;\r\n$id = isset($_GET['id']) ? strtoupper($_GET['id']) : null;"

new_block = "// $area y $grado ya fueron leidos y normalizados arriba (POST + GET + mapeo)\r\n// Solo necesitamos leer $id aqui\r\n$id = isset($_GET['id']) ? strtoupper($_GET['id']) : (isset($input['id']) ? strtoupper($input['id']) : null);"

if old_block in content:
    content = content.replace(old_block, new_block)
    print("Fix 2: Replaced overwrite block successfully")
else:
    # Try with \n only
    old_block2 = old_block.replace('\r\n', '\n')
    if old_block2 in content:
        content = content.replace(old_block2, new_block.replace('\r\n', '\n'))
        print("Fix 2: Replaced overwrite block (LF) successfully")
    else:
        print("Fix 2: OVERWRITE BLOCK NOT FOUND!")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("\nDone! File updated.")
