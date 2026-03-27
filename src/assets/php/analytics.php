<?php
/**
 * analytics.php - Recopila y consulta estadísticas de uso (almacenamiento JSON)
 *
 * POST: Registra un evento de uso
 * GET:  Consulta estadísticas (siempre últimos 7 días, auto-limpieza)
 */

include_once("cors.php");
header('Content-Type: application/json; charset=utf-8');

$DATA_DIR = __DIR__ . '/analytics_data';
$EVENTS_FILE = $DATA_DIR . '/events.json';
$SESSIONS_FILE = $DATA_DIR . '/sessions.json';

// Crear directorio si no existe
if (!is_dir($DATA_DIR)) {
    mkdir($DATA_DIR, 0755, true);
}

// Inicializar archivos si no existen
if (!file_exists($EVENTS_FILE)) {
    file_put_contents($EVENTS_FILE, json_encode([]));
}
if (!file_exists($SESSIONS_FILE)) {
    file_put_contents($SESSIONS_FILE, json_encode([]));
}

function readJson(string $file): array {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    if (!$content) return [];
    $decoded = json_decode($content, true);
    return is_array($decoded) ? $decoded : [];
}

function writeJson(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Auto-limpieza: elimina eventos y sesiones con más de 7 días de antigüedad.
 */
function autoCleanup(string $eventsFile, string $sessionsFile): void {
    $cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));

    $events = readJson($eventsFile);
    $cleanEvents = array_values(array_filter($events, function ($e) use ($cutoff) {
        return ($e['created_at'] ?? '') >= $cutoff;
    }));
    if (count($cleanEvents) < count($events)) {
        writeJson($eventsFile, $cleanEvents);
    }

    $sessions = readJson($sessionsFile);
    $cleanSessions = array_values(array_filter($sessions, function ($s) use ($cutoff) {
        return ($s['started_at'] ?? '') >= $cutoff;
    }));
    if (count($cleanSessions) < count($sessions)) {
        writeJson($sessionsFile, $cleanSessions);
    }
}

// Auto-limpieza en cada request
autoCleanup($EVENTS_FILE, $SESSIONS_FILE);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos']);
        exit();
    }

    // Reiniciar estadísticas
    if (isset($input['action']) && $input['action'] === 'reset') {
        writeJson($EVENTS_FILE, []);
        writeJson($SESSIONS_FILE, []);
        echo json_encode(['status' => 'success', 'message' => 'Estadísticas reiniciadas']);
        exit();
    }

    if (!isset($input['event_type']) || !isset($input['session_id'])) {
        echo json_encode(['status' => 'error', 'message' => 'Datos incompletos: se requiere event_type y session_id']);
        exit();
    }

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $now = date('Y-m-d H:i:s');

    // Guardar evento
    $event = [
        'session_id'          => $input['session_id'],
        'id_docente'          => $input['id_docente'] ?? null,
        'event_type'          => $input['event_type'],
        'event_data'          => $input['event_data'] ?? null,
        'app_version'         => $input['app_version'] ?? null,
        'user_agent'          => $input['user_agent'] ?? null,
        'platform'            => $input['platform'] ?? null,
        'browser_name'        => $input['browser_name'] ?? null,
        'browser_version'     => $input['browser_version'] ?? null,
        'os_name'             => $input['os_name'] ?? null,
        'os_version'          => $input['os_version'] ?? null,
        'device_type'         => $input['device_type'] ?? null,
        'device_vendor'       => $input['device_vendor'] ?? null,
        'device_model'        => $input['device_model'] ?? null,
        'cpu_architecture'    => $input['cpu_architecture'] ?? null,
        'language'            => $input['language'] ?? null,
        'screen_width'        => $input['screen_width'] ?? null,
        'screen_height'       => $input['screen_height'] ?? null,
        'viewport_width'      => $input['viewport_width'] ?? null,
        'viewport_height'     => $input['viewport_height'] ?? null,
        'connection_type'     => $input['connection_type'] ?? null,
        'connection_downlink' => $input['connection_downlink'] ?? null,
        'is_online'           => $input['is_online'] ?? true,
        'timezone'            => $input['timezone'] ?? null,
        'referrer'            => $input['referrer'] ?? null,
        'ip_address'          => $ip,
        'created_at'          => $now,
    ];

    $events = readJson($EVENTS_FILE);
    $events[] = $event;
    writeJson($EVENTS_FILE, $events);

    // Actualizar sesión
    $sessions = readJson($SESSIONS_FILE);
    $sessionId = $input['session_id'];
    $isPageView = in_array($input['event_type'], ['page_view', 'view_change']) ? 1 : 0;
    $found = false;

    foreach ($sessions as &$session) {
        if ($session['session_id'] === $sessionId) {
            $session['last_activity'] = $now;
            $session['events_count'] = ($session['events_count'] ?? 0) + 1;
            $session['page_views'] = ($session['page_views'] ?? 0) + $isPageView;
            $start = strtotime($session['started_at']);
            $session['duration_seconds'] = time() - $start;
            $found = true;
            break;
        }
    }
    unset($session);

    if (!$found) {
        $sessions[] = [
            'session_id'       => $sessionId,
            'id_docente'       => $input['id_docente'] ?? null,
            'started_at'       => $now,
            'last_activity'    => $now,
            'duration_seconds' => 0,
            'page_views'       => $isPageView,
            'events_count'     => 1,
            'app_version'      => $input['app_version'] ?? null,
            'user_agent'       => $input['user_agent'] ?? null,
            'platform'         => $input['platform'] ?? null,
            'browser_name'     => $input['browser_name'] ?? null,
            'browser_version'  => $input['browser_version'] ?? null,
            'os_name'          => $input['os_name'] ?? null,
            'os_version'       => $input['os_version'] ?? null,
            'device_type'      => $input['device_type'] ?? null,
            'ip_address'       => $ip,
        ];
    }
    writeJson($SESSIONS_FILE, $sessions);

    echo json_encode(['status' => 'success', 'message' => 'Evento registrado']);

} elseif ($method === 'GET') {
    $events = readJson($EVENTS_FILE);
    $sessions = readJson($SESSIONS_FILE);

    // Todos los datos ya están filtrados a 7 días por auto-limpieza
    $totalEvents = count($events);
    $totalSessions = count($sessions);

    // Duración promedio
    $durations = array_filter(array_column($sessions, 'duration_seconds'), fn($d) => $d > 0);
    $avgDuration = count($durations) > 0 ? round(array_sum($durations) / count($durations)) : 0;

    // Docentes únicos
    $docentes = array_unique(array_filter(array_column($events, 'id_docente')));
    $uniqueDocentes = count($docentes);

    // Vistas más visitadas (módulos)
    $views = [];
    foreach ($events as $e) {
        if ($e['event_type'] === 'view_change' && isset($e['event_data']['view'])) {
            $v = $e['event_data']['view'];
            $views[$v] = ($views[$v] ?? 0) + 1;
        }
    }
    arsort($views);
    $topViews = [];
    foreach (array_slice($views, 0, 10, true) as $name => $count) {
        $topViews[] = ['view_name' => $name, 'count' => $count];
    }

    // Resumen semanal por docente
    $docenteMap = [];
    foreach ($sessions as $s) {
        $did = $s['id_docente'] ?? null;
        if (!$did) continue;
        if (!isset($docenteMap[$did])) {
            $docenteMap[$did] = [
                'id_docente' => $did,
                'sessions' => 0,
                'total_duration' => 0,
                'modules_used' => [],
            ];
        }
        $docenteMap[$did]['sessions']++;
        $docenteMap[$did]['total_duration'] += ($s['duration_seconds'] ?? 0);
    }

    // Agregar módulos usados por cada docente (de view_change events)
    foreach ($events as $e) {
        $did = $e['id_docente'] ?? null;
        if (!$did || $e['event_type'] !== 'view_change') continue;
        if (!isset($e['event_data']['view'])) continue;
        $view = $e['event_data']['view'];
        if (isset($docenteMap[$did]) && !in_array($view, $docenteMap[$did]['modules_used'])) {
            $docenteMap[$did]['modules_used'][] = $view;
        }
    }

    // Ordenar por sesiones desc
    $docentesWeekly = array_values($docenteMap);
    usort($docentesWeekly, fn($a, $b) => $b['sessions'] - $a['sessions']);

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_events'         => $totalEvents,
            'total_sessions'       => $totalSessions,
            'avg_session_duration' => $avgDuration,
            'unique_docentes'      => $uniqueDocentes,
            'top_views'            => $topViews,
            'docentes_weekly'      => $docentesWeekly,
        ]
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
