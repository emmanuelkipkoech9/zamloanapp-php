<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$data_dir = __DIR__ . '/data';
if (!is_dir($data_dir)) mkdir($data_dir, 0755, true);

$visits_file   = $data_dir . '/visits.json';
$payments_file = $data_dir . '/payments.json';
$clicks_file   = $data_dir . '/clicks.json';

function read_json($file) {
    if (!file_exists($file)) return [];
    $content = file_get_contents($file);
    return json_decode($content, true) ?: [];
}

function write_json($file, $data) {
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
}

function get_ip() {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'unknown';
}

function get_country($ip) {
    // Basic geo lookup via free API
    $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=country,city");
    if ($response) {
        $geo = json_decode($response, true);
        return ($geo['country'] ?? 'Unknown') . ', ' . ($geo['city'] ?? '');
    }
    return 'Unknown';
}

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';
$now = date('Y-m-d H:i:s');
$ip = get_ip();

if ($action === 'visit') {
    $visits = read_json($visits_file);
    $visits[] = [
        'time'    => $now,
        'ip'      => $ip,
        'ua'      => $_SERVER['HTTP_USER_AGENT'] ?? '',
        'ref'     => $input['referrer'] ?? '',
        'page'    => $input['page'] ?? '/',
    ];
    write_json($visits_file, $visits);
    echo json_encode(['ok' => true]);

} elseif ($action === 'loan_click') {
    $clicks = read_json($clicks_file);
    $clicks[] = [
        'time'   => $now,
        'ip'     => $ip,
        'amount' => $input['amount'] ?? 0,
        'fee'    => $input['fee'] ?? 0,
    ];
    write_json($clicks_file, $clicks);
    echo json_encode(['ok' => true]);

} elseif ($action === 'payment_attempt') {
    $payments = read_json($payments_file);
    $payments[] = [
        'time'   => $now,
        'ip'     => $ip,
        'amount' => $input['amount'] ?? 0,
        'fee'    => $input['fee'] ?? 0,
        'phone'  => substr($input['phone'] ?? '', 0, 6) . '****', // partial mask
        'status' => $input['status'] ?? 'pending',
    ];
    write_json($payments_file, $payments);
    echo json_encode(['ok' => true]);

} elseif ($action === 'payment_result') {
    $payments = read_json($payments_file);
    // Update last payment entry for this IP
    for ($i = count($payments) - 1; $i >= 0; $i--) {
        if ($payments[$i]['ip'] === $ip) {
            $payments[$i]['status'] = $input['status'] ?? 'unknown';
            $payments[$i]['updated'] = $now;
            break;
        }
    }
    write_json($payments_file, $payments);
    echo json_encode(['ok' => true]);

} else {
    echo json_encode(['ok' => false, 'msg' => 'unknown action']);
}
