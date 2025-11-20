<?php
header("Content-Type: application/json"); // Ensure JSON response
error_reporting(E_ALL);
ini_set('display_errors', 1);

$host = "localhost";
$user = "root";
$pass = "";
$dbname = "DiabetesManagementDB";

$conn = new mysqli($host, $user, $pass, $dbname);

// Check database connection
if ($conn->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $conn->connect_error]));
}

// Read JSON input
$data = json_decode(file_get_contents("php://input"), true);

// Validate input
if (!isset($data['patient_id'], $data['predicted_bgl'], $data['predicted_bmi'], 
          $data['diabetes_level'], $data['diet_plan'], $data['exercise_plan'], $data['prediction_date'])) {
    echo json_encode(["error" => "Missing required fields"]);
    exit();
}

// Convert diet and exercise plans to JSON strings (if arrays)
$diet_plan = is_array($data['diet_plan']) ? json_encode($data['diet_plan']) : $data['diet_plan'];
$exercise_plan = is_array($data['exercise_plan']) ? json_encode($data['exercise_plan']) : $data['exercise_plan'];

// Prepare SQL statement
$stmt = $conn->prepare("INSERT INTO predictions 
    (patient_id, PredictedBGL, PredictedBMI, DiabetesLevel, diet_plan, exercise_plan, PredictionDate) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("iddisss", 
    $data['patient_id'], 
    $data['predicted_bgl'], 
    $data['predicted_bmi'], 
    $data['diabetes_level'], 
    $diet_plan, 
    $exercise_plan, 
    $data['prediction_date']
);

// Execute and return response
if ($stmt->execute()) {
    echo json_encode(["success" => true, "message" => "Prediction saved successfully"]);
} else {
    echo json_encode(["error" => "Error saving prediction: " . $stmt->error]);
}

// Close database connections
$stmt->close();
$conn->close();
?>
