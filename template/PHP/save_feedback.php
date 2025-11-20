<?php
header("Content-Type: application/json");
include('db.php');

// Read JSON input
$input = json_decode(file_get_contents("php://input"), true);

if (!isset($input["patient_id"]) || !isset($input["feedback"])) {
    echo json_encode(["success" => false, "error" => "Patient ID and feedback are required"]);
    exit;
}

$patient_id = $input["patient_id"];
$feedback = $input["feedback"];

$query = "INSERT INTO feedback (patient_id, feedback_text, submitted_at) VALUES (?, ?, NOW())";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $patient_id, $feedback);

if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Feedback submitted successfully!"]);
} else {
    echo json_encode(["success" => false, "error" => $stmt->error]);
}

$stmt->close();
$conn->close();