<?php
/**
 * LOGG Branch Fingerprint Agent
 * يسحب بيانات الحضور من جهاز البصمة المحلي ويرسلها للسيرفر الرئيسي عبر الإنترنت
 *
 * الاستخدام:
 *   php agent.php
 *   php agent.php --dry-run   (عرض السجلات بدون إرسال)
 */

if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die("ERROR: Run 'composer install' first.\n");
}
require_once __DIR__ . '/vendor/autoload.php';

if (!file_exists(__DIR__ . '/config.php')) {
    die("ERROR: config.php not found. Copy config.example.php to config.php and fill in your values.\n");
}
$config  = require __DIR__ . '/config.php';
$dryRun  = in_array('--dry-run', $argv ?? []);

$log = function(string $msg) {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . PHP_EOL;
};

$log("Starting fingerprint sync" . ($dryRun ? ' [DRY RUN]' : '') . "...");

// ── اتصال بجهاز البصمة ──────────────────────────────────
if (!class_exists('\Rats\Zkteco\Lib\ZKTeco')) {
    die("ERROR: ZKTeco library not found. Run 'composer install'.\n");
}

$zk = new \Rats\Zkteco\Lib\ZKTeco($config['device_ip'], $config['device_port']);

$log("Connecting to device {$config['device_ip']}:{$config['device_port']}...");

if (!$zk->connect()) {
    die("ERROR: Cannot connect to device. Check IP/port and that the device is on the same network.\n");
}

$log("Connected. Pulling attendance records...");

$zk->disableDevice();
$attendance = [];
try {
    $attendance = $zk->getAttendance();
} finally {
    $zk->enableDevice();
    $zk->disconnect();
}

if (empty($attendance)) {
    $log("No attendance records found on device.");
    exit(0);
}

$log("Found " . count($attendance) . " record(s) on device.");

if ($dryRun) {
    foreach (array_slice($attendance, 0, 5) as $r) {
        $log("  Sample: id={$r['id']} uid={$r['uid']} time={$r['timestamp']} type={$r['type']}");
    }
    $log("[DRY RUN] Not sending to server.");
    exit(0);
}

// ── تنسيق السجلات وإرسالها ──────────────────────────────
$logs = array_map(function ($record) {
    return [
        'id'        => $record['id'],
        'uid'       => $record['uid'] ?? $record['id'],
        'timestamp' => $record['timestamp'],
        'type'      => (int)($record['type']  ?? 0),
        'state'     => (int)($record['state'] ?? 0),
    ];
}, $attendance);

$log("Sending " . count($logs) . " records to server...");

$ch = curl_init($config['server_url']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['logs' => $logs]),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Agent-Token: ' . $config['api_token'],
    ],
    CURLOPT_TIMEOUT        => 60,
    CURLOPT_CONNECTTIMEOUT => 15,
    CURLOPT_SSL_VERIFYPEER => true,
]);

$response  = curl_exec($ch);
$curlError = curl_error($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($curlError) {
    die("ERROR: Failed to reach server: $curlError\n");
}

$result = json_decode($response, true);

if ($httpCode === 200 && isset($result['success']) && $result['success']) {
    $log("SUCCESS: {$result['count']} new record(s) saved on server.");
    exit(0);
}

$error = $result['error'] ?? $response;
die("ERROR: Server responded HTTP $httpCode: $error\n");
