<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

$visits_file   = $data_dir . '/visits.json';
$payments_file = $data_dir . '/payments.json';
$clicks_file   = $data_dir . '/clicks.json';
$funnel_file   = $data_dir . '/funnel.json';

function read_json($file) {
    if (!file_exists($file)) return [];
    return json_decode(file_get_contents($file), true) ?: [];
}
function write_json($file, $data) {
    // Keep max 2000 entries per file to avoid bloat
    if (count($data) > 2000) $data = array_slice($data, -2000);
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}
function get_ip() {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    // Handle comma-separated IPs from proxies
    return trim(explode(',', $ip)[0]);
}
function detect_source($referrer, $ua) {
    $ref = strtolower($referrer ?? '');
    $ua  = strtolower($ua ?? '');
    if (str_contains($ref, 'tiktok') || str_contains($ua, 'tiktok'))   return 'TikTok';
    if (str_contains($ref, 'facebook') || str_contains($ref, 'fb.'))   return 'Facebook';
    if (str_contains($ref, 'instagram'))                                 return 'Instagram';
    if (str_contains($ref, 'twitter') || str_contains($ref, 't.co'))   return 'Twitter/X';
    if (str_contains($ref, 'whatsapp') || str_contains($ua, 'whatsapp')) return 'WhatsApp';
    if (str_contains($ref, 'youtube'))                                   return 'YouTube';
    if (str_contains($ref, 'google'))                                    return 'Google';
    if (empty($ref) || $ref === '')                                      return 'Direct';
    return 'Other';
}
function detect_device($ua) {
    $ua = strtolower($ua ?? '');
    if (str_contains($ua, 'mobile') || str_contains($ua, 'android') || str_contains($ua, 'iphone')) return 'Mobile';
    if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) return 'Tablet';
    return 'Desktop';
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? '';
$now    = date('Y-m-d H:i:s');
$ip     = get_ip();
$ua     = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ref    = $input['referrer'] ?? '';
$source = detect_source($ref, $ua);
$device = detect_device($ua);

if ($action === 'visit') {
    $visits = read_json($visits_file);
    $visits[] = [
        'time'   => $now,
        'ip'     => $ip,
        'source' => $source,
        'device' => $device,
        'ref'    => $ref,
        'ua'     => $ua,
    ];
    write_json($visits_file, $visits);

    // Funnel: mark visited
    $funnel = read_json($funnel_file);
    $funnel[$ip] = array_merge($funnel[$ip] ?? [], ['visited' => $now, 'source' => $source, 'device' => $device]);
    write_json($funnel_file, $funnel);

    echo json_encode(['ok' => true]);

} elseif ($action === 'loan_click') {
    $clicks = read_json($clicks_file);
    $clicks[] = [
        'time'   => $now,
        'ip'     => $ip,
        'amount' => $input['amount'] ?? 0,
        'fee'    => $input['fee'] ?? 0,
        'source' => $source,
        'device' => $device,
    ];
    write_json($clicks_file, $clicks);

    // Funnel: mark clicked
    $funnel = read_json($funnel_file);
    $funnel[$ip] = array_merge($funnel[$ip] ?? [], ['clicked' => $now, 'loan_amount' => $input['amount'] ?? 0]);
    write_json($funnel_file, $funnel);

    echo json_encode(['ok' => true]);

} elseif ($action === 'form_submit') {
    // Funnel: mark form submitted
    $funnel = read_json($funnel_file);
    $funnel[$ip] = array_merge($funnel[$ip] ?? [], ['form_submitted' => $now]);
    write_json($funnel_file, $funnel);
    echo json_encode(['ok' => true]);

} elseif ($action === 'payment_attempt') {
    $payments = read_json($payments_file);
    $payments[] = [
        'time'   => $now,
        'ip'     => $ip,
        'amount' => $input['amount'] ?? 0,
        'fee'    => $input['fee'] ?? 0,
        'phone'  => substr($input['phone'] ?? '', 0, 6) . '****',
        'status' => 'pending',
        'source' => $source,
        'device' => $device,
    ];
    write_json($payments_file, $payments);

    // Funnel: mark payment attempted
    $funnel = read_json($funnel_file);
    $funnel[$ip] = array_merge($funnel[$ip] ?? [], ['payment_attempted' => $now]);
    write_json($funnel_file, $funnel);

    echo json_encode(['ok' => true]);

} elseif ($action === 'payment_result') {
    $payments = read_json($payments_file);
    for ($i = count($payments) - 1; $i >= 0; $i--) {
        if ($payments[$i]['ip'] === $ip) {
            $payments[$i]['status']  = $input['status'] ?? 'unknown';
            $payments[$i]['updated'] = $now;
            break;
        }
    }
    write_json($payments_file, $payments);

    if (($input['status'] ?? '') === 'success') {
        $funnel = read_json($funnel_file);
        $funnel[$ip] = array_merge($funnel[$ip] ?? [], ['payment_success' => $now]);
        write_json($funnel_file, $funnel);
    }

    echo json_encode(['ok' => true]);

} else {
    echo json_encode(['ok' => false, 'msg' => 'unknown action']);
}
