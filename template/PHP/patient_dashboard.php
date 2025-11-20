<?php
include 'db.php';
include 'verify_jwt.php';

$headers = getallheaders();
$token = $headers['Authorization'] ?? ($_GET['token'] ?? null);

if (!$token) {
    http_response_code(401);
    echo json_encode(['error' => 'Missing token']);
    exit();
}

$verification = verifyTokenWithFlask($token);

if (!$verification['valid']) {
    http_response_code(401);
    echo json_encode(['error' => $verification['error']]);
    exit();
}

$user_data = $verification['user_data'];

if ($user_data['user_type'] !== 'patient') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$patient_id = $user_data['user_id'];

$stmt = $conn->prepare("SELECT username, email, age, gender FROM patients WHERE patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$patient = $stmt->get_result()->fetch_assoc();

if (!$patient) {
    http_response_code(404);
    echo json_encode(['error' => 'Patient not found']);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM patient_reports WHERE patient_id = ? ORDER BY report_date DESC LIMIT 3");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$reports = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diabetes Patient Dashboard</title>
    <style>
        /* Add your custom styles here */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
        }

        h2 {
            color: #333;
        }

        .navbar {
            background: #007bff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px;
        }

        .navbar-logo {
            font-size: 20px;
            font-weight: bold;
        }

        .navbar-links {
            list-style-type: none;
            margin: 0;
            padding: 0;
            display: flex;
        }

        .navbar-links li {
            margin-left: 20px;
        }

        .navbar-links a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
        }

        .navbar-links a:hover {
            background: #0056b3;
            border-radius: 5px;
        }

        /* Additional styles */
        .logout-button {
            background-color: #ff4b5c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-left: 20px;
        }

        .logout-button:hover {
            background-color: #d43f4e;
        }

        .section {
            padding: 20px;
            margin: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .dashboard-card,
        .profile-card,
        .results-card,
        .feedback-card {
            padding: 20px;
            background-color: #e1e8f0;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .dashboard-card p,
        .profile-card p,
        .results-card p,
        .feedback-card p {
            margin: 10px 0;
            font-size: 16px;
        }

        .dashboard-buttons {
            margin-top: 20px;
        }

        .dashboard-buttons button {
            margin-right: 10px;
            padding: 10px;
            background-color: #005c97;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .dashboard-buttons button:hover {
            background-color: #00497a;
        }

        textarea {
            width: 100%;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }

        .feedback-card button {
            margin-top: 10px;
            padding: 10px;
            background-color: #005c97;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .feedback-card button:hover {
            background-color: #00497a;
        }

        <?php include 'styles.css'; ?>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #555;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #1e1e1e;
            color: cyan;
        }

        td {
            background-color: #222;
            color: white;
        }
    </style>
</head>

<body>
    <div class="navbar">
        <div class="navbar-logo">Welcome, <?= htmlspecialchars($patient['username']); ?></div>
        <ul class="navbar-links">
            <li><a href="#" onclick="showSection('dashboard-section')">Dashboard</a></li>
            <li><a href="#" onclick="showSection('profile-section')">Profile</a></li>
            <li><a href="#" onclick="showSection('results-section')">Results</a></li>
            <li><a href="#" onclick="showSection('feedback-section')">Feedback</a></li>
            <li><a href="../PHP/logout.php">Logout</a></li>
        </ul>
    </div>

    <!-- Dashboard -->
    <div class="section" id="dashboard-section">
        <h2>Health Overview</h2>
        <div class="dashboard-card">
            <p><strong>Age:</strong> <?= $reports ? htmlspecialchars($reports[0]['age']) : 'N/A'; ?> </p>
            <p><strong>Latest Blood Glucose:</strong> <?= $reports ? htmlspecialchars($reports[0]['BGL']) : 'N/A'; ?>
                mg/dL
            </p>
            <p><strong>BMI:</strong> <?= $reports ? htmlspecialchars($reports[0]['BMI']) : 'N/A'; ?></p>

            <div class="dashboard-buttons">
                <button onclick="showSection('profile-section')">View Profile</button>
                <button onclick="showSection('results-section')">View Results</button>
                <button onclick="showSection('feedback-section')">Give Feedback</button>
            </div>
        </div>
    </div>

    <!-- Profile Section -->
    <div class="section" id="profile-section" style="display:none;">
        <h2>Patient Profile</h2>
        <div class="profile-card">
            <p><strong>Name:</strong> <?= htmlspecialchars($patient['username']); ?></p>
            <p><strong>Age:</strong> <?= htmlspecialchars($patient['age']); ?></p>
            <p><strong>Gender:</strong> <?= htmlspecialchars($patient['gender']); ?></p>
            <p><strong>Email:</strong> <?= htmlspecialchars($patient['email']); ?></p>
        </div>
    </div>

    <!-- Results Section -->
    <div id="results-section" style="display: none;">
        <h2>Results</h2>
        <div class="results-card">
            <p><strong>Latest Blood Glucose:</strong> <?= $reports ? htmlspecialchars($reports[0]['BGL']) : 'N/A'; ?>
                mg/dL</p>

            <div>
                <h3 id="predicted-bgl">Predicted BGL: </h3>
                <h3 id="predicted-bmi">Predicted BMI: </h3>
                <h3 id="diabetes-level">Diabetes Level: </h3>

                <h3>Diet Plan:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Meal</th>
                            <th>Food</th>
                        </tr>
                    </thead>
                    <tbody id="diet-table-body"></tbody>
                </table>

                <h3>Exercise Plan:</h3>
                <table>
                    <thead>
                        <tr>
                            <th>Set</th>
                            <th>Exercise</th>
                        </tr>
                    </thead>
                    <tbody id="exercise-table-body"></tbody>
                </table>

                <h3 id="diabetes-advice">Advice: </h3>
            </div>
        </div>
    </div>




    <!-- Feedback Section -->
    <div class="section" id="feedback-section" style="display:none;">
        <h2>Feedback</h2>
        <div class="feedback-card">
            <p>We value your feedback. Please share your experience below:</p>
            <textarea id="feedback-input" rows="4" cols="50" placeholder="Write your feedback here..."></textarea>
            <button onclick="submitFeedback()">Submit</button>
        </div>
    </div>

    <!-- JavaScript -->
    <!-- Assuming this is within your PHP file -->
    <script>
        // Getting dynamic values from PHP
        const token = <?php echo json_encode($token); ?>;
        const reports = <?php echo json_encode($reports); ?>;
        const patientId = <?php echo json_encode($patient_id); ?>; // Dynamically from PHP session or query

        console.log("Received reports:", reports);
        console.log("Token:", token);
        console.log("Patient ID:", patientId);

        function showSection(sectionId) {
            const sections = ["dashboard-section", "profile-section", "results-section", "feedback-section"];
            sections.forEach(function (id) {
                const section = document.getElementById(id);
                if (section) {
                    section.style.display = "none";
                }
            });

            const targetSection = document.getElementById(sectionId);
            if (targetSection) {
                targetSection.style.display = "block";
                if (sectionId === "results-section") {
                    fetchAndPredict(reports); // Start prediction when results section opens
                }
            } else {
                console.error(`Section with ID "${sectionId}" not found.`);
            }
        }

        function formatReports(reports) {
            // Sort the reports if needed (example: by report_id ascending)
            reports.sort((a, b) => a.report_id - b.report_id);

            return reports.map(report => [report.BGL, report.BMI]);
        }

        function extractAge(reports) {
            if (reports.length > 0) {
                return reports[0].age;  // Assuming all reports have the same age
            } else {
                console.error("No reports available to extract age.");
                return null;
            }
        }

        function fetchAndPredict(reports) {
            const age = extractAge(reports);

            if (age === null) {
                console.error("Cannot proceed without age.");
                return;
            }

            const formattedData = formatReports(reports);
            const finalData = [age, ...formattedData.flat()];

            console.log("Final data to send:", finalData);
            sendDataToBackend(finalData);
        }

        async function sendDataToBackend(data) {
            try {
                const response = await fetch("http://localhost:5000/patient_dashboard", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Authorization": `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        patient_id: patientId,
                        data: data
                    })
                });

                if (response.ok) {
                    const result = await response.json();
                    console.log("Backend response:", result);
                    displayResults(result);
                } else {
                    console.error("Failed to send data:", response.statusText);
                }
            } catch (error) {
                console.error("Error sending request:", error);
            }
        }




        function displayResults(result, patient_id) {
            let bglElement = document.getElementById("predicted-bgl");
            let bmiElement = document.getElementById("predicted-bmi");
            let diabetesLevelElement = document.getElementById("diabetes-level");
            let dietTable = document.getElementById("diet-table-body");  // Get table body
            let exerciseTable = document.getElementById("exercise-table-body");  // Get table body
            let adviceElement = document.getElementById("diabetes-advice");
            let predictionDateElement = document.getElementById("predictionDateField");

            let predictedBGL = result.predicted_bgl?.toFixed(2) || "N/A";
            let predictedBMI = result.predicted_bmi?.toFixed(2) || "N/A";
            let diabetesLevel = result.diabetes_level || "N/A";

            if (bglElement) bglElement.innerText = `Predicted BGL: ${predictedBGL}`;
            if (bmiElement) bmiElement.innerText = `Predicted BMI: ${predictedBMI}`;
            if (diabetesLevelElement) diabetesLevelElement.innerText = `Diabetes Level: ${diabetesLevel}`;

            // Clear previous table data
            dietTable.innerHTML = "";
            exerciseTable.innerHTML = "";

            // Populate Diet Table
            let dietPlan = result.diet_plan || { breakfast: "No data", lunch: "No data", dinner: "No data" };
            Object.entries(dietPlan).forEach(([meal, food]) => {
                let row = `<tr><td>${getMealIcon(meal)} ${meal}</td><td>${food}</td></tr>`;
                dietTable.innerHTML += row;
            });

            // Populate Exercise Table
            let exercisePlan = result.exercise_plan || { "Set 1": "No data", "Set 2": "No data" };
            Object.entries(exercisePlan).forEach(([set, exercise]) => {
                let row = `<tr><td>${getExerciseIcon(set)} ${set}</td><td>${exercise}</td></tr>`;
                exerciseTable.innerHTML += row;
            });

            if (adviceElement) adviceElement.innerHTML = `<strong>⚠️ ${result.diabetes_specific_advice || "Consult a doctor for a medical plan."}</strong>`;

            let predictionDate = new Date().toISOString().split("T")[0];
            if (predictionDateElement) predictionDateElement.innerText = predictionDate;

            saveResultsToDB(predictedBGL, predictedBMI, diabetesLevel, dietPlan, exercisePlan, patient_id);
        }

        // Function to return meal icons
        function getMealIcon(meal) {
            const icons = { breakfast: "🍳", lunch: "🥗", dinner: "🍖" };
            return icons[meal.toLowerCase()] || "🍽️";
        }

        // Function to return exercise icons
        function getExerciseIcon(set) {
            const icons = { "Set 1": "🔥", "Set 2": "🏃", "Set 3": "💥" };
            return icons[set] || "🏋️";
        }


        function saveResultsToDB(predictedBGL, predictedBMI, diabetesLevel, dietPlan, exercisePlan) {
            // Replace with actual patient ID from session
            const predictionDate = new Date().toISOString().slice(0, 10); // YYYY-MM-DD format

            const requestData = {
                patient_id: patientID,
                predicted_bgl: predictedBGL,
                predicted_bmi: predictedBMI,
                diabetes_level: diabetesLevel,
                diet_plan: JSON.stringify(dietPlan),
                exercise_plan: JSON.stringify(exercisePlan),
                prediction_date: predictionDate
            };

            fetch("save_results.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData)
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        console.log("✅ Prediction saved:", data.message);
                    } else {
                        console.error("❌ Error saving prediction:", data.error);
                    }
                })
                .catch(error => console.error("❌ Fetch error:", error));
        }

        function submitFeedback() {
            console.log("🔵 submitFeedback() triggered");

            let feedbackText = document.getElementById("feedback-input").value.trim();
            let patientID = <?php echo json_encode($patient_id); ?>; // Ensure PHP passes the correct ID

            console.log("🟢 Patient ID before sending:", patientID);
            console.log("🟢 Feedback before sending:", feedbackText);

            if (!patientID || !feedbackText) {
                alert("❌ Patient ID and feedback are required!");
                console.error("❌ Patient ID or Feedback is missing!");
                return;
            }

            let requestData = { patient_id: patientID, feedback: feedbackText };
            console.log("🟢 Sending JSON Payload:", JSON.stringify(requestData));

            fetch("save_feedback.php", { // Ensure the correct path
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(requestData)
            })
                .then(response => {
                    console.log("🟡 Fetch response received:", response);
                    return response.text(); // Inspect raw response first
                })
                .then(text => {
                    console.log("🟢 Server Raw Response:", text);

                    let data;
                    try {
                        data = JSON.parse(text); // Attempt to parse JSON
                    } catch (error) {
                        console.error("❌ JSON Parse Error:", error);
                        alert("❌ Server response is not valid JSON. Check Console.");
                        return;
                    }

                    console.log("✅ Parsed JSON Response:", data);

                    if (data.success) {
                        console.log("✅ Feedback saved:", data.message);
                    } else {
                        console.error("❌ Error saving feedback:", data.error);
                    }
                })
                .catch(error => console.error("❌ Fetch error:", error));
        }






    </script>






</body>

</html>