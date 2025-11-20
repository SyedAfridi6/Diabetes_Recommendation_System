<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $patient_id = $_POST['patient_id'];
    $age = $_POST['age'];
    $blood_glucose = $_POST['blood_glucose'];
    $bmi = $_POST['bmi'];
    $report_date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO patient_reports (patient_id, age, BGL, BMI, report_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sidds", $patient_id, $age, $blood_glucose, $bmi, $report_date);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Report submitted successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to submit report.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
}
?>
