<?php
include 'db.php';
include 'verify_jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'] ?? null;

if (!$token) {
    echo json_encode(['success' => false, 'error' => 'Missing token']);
    exit();
}

$verification = verifyTokenWithFlask($token);
if (!$verification['valid']) {
    echo json_encode(['success' => false, 'error' => $verification['error']]);
    exit();
}

$user_data = $verification['user_data'];
$patient_id = $user_data['user_id'];

$stmt = $conn->prepare("SELECT * FROM patient_reports WHERE patient_id = ? ORDER BY report_date DESC LIMIT 1");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$report = $stmt->get_result()->fetch_assoc();

if ($report) {
    echo json_encode([
        'success' => true,
        'age' => $report['age'],
        'BGL' => $report['BGL'],
        'BMI' => $report['BMI'],
        'report_date' => $report['report_date']
    ]);
} else {
    echo json_encode(['success' => false, 'error' => 'No reports found']);
}
?>
