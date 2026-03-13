<?php
/**
 * analytics.php - Recopila y consulta estadísticas de uso (almacenamiento JSON)
 * 
 * POST: Registra un evento de uso
 * GET:  Consulta estadísticas
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
    $content = file_get_contents($file);
    return $content ? json_decode($content, true) : [];
}

function writeJson(string $file, array $data): void {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['event_type']) || !isset($input['session_id'])) {
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

    // Limitar a últimos 10000 eventos para no crecer indefinidamente
    if (count($events) > 10000) {
        $events = array_slice($events, -10000);
    }
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
            'ip_address'       => $ip,
        ];
    }

    // Limitar a últimas 5000 sesiones
    if (count($sessions) > 5000) {
        $sessions = array_slice($sessions, -5000);
    }
    writeJson($SESSIONS_FILE, $sessions);

    echo json_encode(['status' => 'success', 'message' => 'Evento registrado']);

} elseif ($method === 'GET') {
    $id_docente = $_GET['id_docente'] ?? null;
    $period = $_GET['period'] ?? 'all';

    $events = readJson($EVENTS_FILE);
    $sessions = readJson($SESSIONS_FILE);

    // Filtro de fecha
    $cutoff = null;
    if ($period === 'today') {
        $cutoff = date('Y-m-d 00:00:00');
    } elseif ($period === 'week') {
        $cutoff = date('Y-m-d H:i:s', strtotime('-7 days'));
    } elseif ($period === 'month') {
        $cutoff = date('Y-m-d H:i:s', strtotime('-30 days'));
    }

    // Filtrar eventos
    $filtered = array_filter($events, function ($e) use ($cutoff, $id_docente) {
        if ($cutoff && ($e['created_at'] ?? '') < $cutoff) return false;
        if ($id_docente && ($e['id_docente'] ?? '') !== $id_docente) return false;
        return true;
    });
    $filtered = array_values($filtered);

    // Filtrar sesiones
    $filteredSessions = array_filter($sessions, function ($s) use ($cutoff, $id_docente) {
        if ($cutoff && ($s['started_at'] ?? '') < $cutoff) return false;
        if ($id_docente && ($s['id_docente'] ?? '') !== $id_docente) return false;
        return true;
    });
    $filteredSessions = array_values($filteredSessions);

    // Total eventos y sesiones
    $totalEvents = count($filtered);
    $totalSessions = count($filteredSessions);

    // Duración promedio
    $durations = array_filter(array_column($filteredSessions, 'duration_seconds'), fn($d) => $d > 0);
    $avgDuration = count($durations) > 0 ? round(array_sum($durations) / count($durations)) : 0;

    // Docentes únicos (sobre todos los eventos filtrados por fecha, sin filtro docente)
    $allFiltered = array_filter($events, function ($e) use ($cutoff) {
        if ($cutoff && ($e['created_at'] ?? '') < $cutoff) return false;
        return true;
    });
    $docentes = array_unique(array_filter(array_column($allFiltered, 'id_docente')));
    $uniqueDocentes = count($docentes);

    // Eventos por tipo
    $byType = [];
    foreach ($filtered as $e) {
        $t = $e['event_type'];
        $byType[$t] = ($byType[$t] ?? 0) + 1;
    }
    arsort($byType);
    $eventsByType = [];
    foreach (array_slice($byType, 0, 20, true) as $type => $count) {
        $eventsByType[] = ['event_type' => $type, 'count' => $count];
    }

    // Vistas más visitadas
    $views = [];
    foreach ($filtered as $e) {
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

    // Plataformas
    $plats = [];
    foreach ($filtered as $e) {
        $p = $e['platform'] ?? 'Desconocida';
        $plats[$p] = ($plats[$p] ?? 0) + 1;
    }
    arsort($plats);
    $platforms = [];
    foreach (array_slice($plats, 0, 10, true) as $name => $count) {
        $platforms[] = ['platform' => $name, 'count' => $count];
    }

    // Actividad por hora
    $byHour = [];
    foreach ($filtered as $e) {
        $h = (int)date('G', strtotime($e['created_at']));
        $byHour[$h] = ($byHour[$h] ?? 0) + 1;
    }
    ksort($byHour);
    $activityByHour = [];
    foreach ($byHour as $hora => $count) {
        $activityByHour[] = ['hora' => $hora, 'count' => $count];
    }

    // Actividad por día (últimos 30 días)
    $cutoff30 = date('Y-m-d H:i:s', strtotime('-30 days'));
    $byDay = [];
    foreach ($filtered as $e) {
        if ($e['created_at'] >= $cutoff30) {
            $d = date('Y-m-d', strtotime($e['created_at']));
            $byDay[$d] = ($byDay[$d] ?? 0) + 1;
        }
    }
    ksort($byDay);
    $activityByDay = [];
    foreach ($byDay as $fecha => $count) {
        $activityByDay[] = ['fecha' => $fecha, 'count' => $count];
    }

    // Eventos recientes
    $recent = array_slice(array_reverse($filtered), 0, 20);
    $recentEvents = array_map(fn($e) => [
        'event_type' => $e['event_type'],
        'event_data' => $e['event_data'] ? json_encode($e['event_data']) : null,
        'created_at' => $e['created_at'],
    ], $recent);

    // Detallado de todas las sesiones (últimas 100, ordenadas por más reciente)
    $sortedSessions = $filteredSessions;
    usort($sortedSessions, fn($a, $b) => strcmp($b['started_at'] ?? '', $a['started_at'] ?? ''));
    $allSessions = array_map(fn($s) => [
        'session_id'       => $s['session_id'],
        'id_docente'       => $s['id_docente'] ?? null,
        'started_at'       => $s['started_at'],
        'last_activity'    => $s['last_activity'] ?? $s['started_at'],
        'duration_seconds' => $s['duration_seconds'] ?? 0,
        'page_views'       => $s['page_views'] ?? 0,
        'events_count'     => $s['events_count'] ?? 0,
        'app_version'      => $s['app_version'] ?? null,
        'platform'         => $s['platform'] ?? null,
        'ip_address'       => $s['ip_address'] ?? null,
    ], array_slice($sortedSessions, 0, 100));

    echo json_encode([
        'status' => 'success',
        'data' => [
            'total_events'         => $totalEvents,
            'total_sessions'       => $totalSessions,
            'avg_session_duration' => $avgDuration,
            'unique_docentes'      => $uniqueDocentes,
            'events_by_type'       => $eventsByType,
            'top_views'            => $topViews,
            'platforms'            => $platforms,
            'activity_by_hour'     => $activityByHour,
            'activity_by_day'      => $activityByDay,
            'recent_events'        => $recentEvents,
            'all_sessions'         => $allSessions,
        ]
    ]);

} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
}
