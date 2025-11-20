<?php
header('Content-Type: application/json');
include('db.php');

if (!$conn) {
    echo json_encode(["success" => false, "message" => "❌ Database connection failed."]);
    exit;
}

if (isset($_GET['id'])) {
    // ✅ Fetch single patient by ID
    $patient_id = intval($_GET['id']);

    $stmt = $conn->prepare("SELECT username, age FROM patients WHERE patient_id = ?");
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $patient = $result->fetch_assoc();
        echo json_encode([
            "success" => true,
            "name" => $patient['username'],
            "age" => $patient['age']
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "❌ Patient not found."]);
    }

    $stmt->close();

} else {
    // ✅ Fetch all patients (from your provided code)
    $query = "SELECT patient_id, username, email, age, gender FROM patients";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $patients = [];
        while ($row = $result->fetch_assoc()) {
            $patients[] = $row;
        }
        echo json_encode([
            "success" => true,
            "patients" => $patients
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "❌ No patients found or error: " . $conn->error
        ]);
    }
}

$conn->close();
?>
