<?php
session_start();
include 'db.php';
include 'verify_jwt.php';

$token = $_GET['token'] ?? ($_SESSION['auth_token'] ?? null);

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

$user_data = $verification['user_data'] ?? null;

if (!$user_data || $user_data['user_type'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$admin_id = $user_data['user_id']; // Ensure 'user_id' refers to the admin's ID

// Store token and user type in session for future requests
$_SESSION['auth_token'] = $token;
$_SESSION['user_type'] = 'admin';

// Fetch admin profile data
$stmt = $conn->prepare("SELECT username, email FROM admins WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();

if (!$admin) {
    http_response_code(404);
    echo json_encode(['error' => 'Admin not found']);
    exit();
}

// Fetch all patients
$query = "SELECT patient_id, username, email, age, gender FROM patients";
$result = $conn->query($query);

if (!$result) {
    die("Error fetching patients: " . $conn->error);
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diabetes Admin Dashboard</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f9;
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

        .container {
            padding: 20px;
        }

        .section {
            padding: 20px;
            margin: 20px;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 15px;
        }

        input,
        select,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }

        button {
            background-color: #005c97;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background-color: #003f6d;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: center;
        }

        th {
            background-color: #005c97;
            color: white;
        }

        .hidden {
            display: none;
        }
    </style>
</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <div class="navbar-logo">Admin Dashboard</div>
        <ul class="navbar-links">
            <li><a href="#" id="home-link">Home</a></li>
            <li><a href="#" id="post-result-link">Post Result</a></li>
            <li><a href="#" id="view-patients-link">View Patients</a></li>
            <li><a href="#" id="profile-link">Admin Profile</a></li>
            <li><a href="../PHP/logout.php">Logout</a></li>
        </ul>
    </div>

    <div class="container">

        <!-- Home Section -->
        <div class="section" id="home-section">
            <h2>Welcome, <?= htmlspecialchars($admin['username']); ?></p></h2>
            <p>Manage patient records and post blood glucose results.</p>
        </div>

        <!-- Post Result Section -->
        <div class="section hidden" id="post-result-section">
            <h2>Post Blood Glucose Result</h2>
            <form id="post-result-form">
                <div class="form-group">
                    <label for="patient-id">Patient ID:</label>
                    <input type="text" id="patient-id" name="patient_id" required placeholder="Enter Patient ID">
                </div>

                <div class="form-group">
                    <label for="patient-name">Patient Name:</label>
                    <input type="text" id="patient-name" name="patient_name" required readonly>
                </div>

                <div class="form-group">
                    <label for="age">Age:</label>
                    <input type="number" id="age" name="age" required readonly>
                </div>

                <div class="form-group">
                    <label for="blood-glucose">Blood Glucose Level:</label>
                    <input type="number" id="blood-glucose" name="blood_glucose" step="0.1" min="0" max="2750" required>
                </div>

                <div class="form-group">
                    <label for="bmi">BMI:</label>
                    <input type="number" id="bmi" name="bmi" step="0.1" min="3" max="50" required>
                </div>

                <div class="form-group">
                    <button type="submit">Submit Result</button>
                </div>
            </form>

        </div>



        <!-- View Patients Section -->
        <div class="section" id="view-patients-section">
            <h2>Patient List</h2>
            <table>
                <thead>
                    <tr>
                        <th>Patient ID</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Age</th>
                        <th>gender</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($patient = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars(string: $patient['patient_id']); ?></td>
                            <td><?php echo htmlspecialchars($patient['username']); ?></td>
                            <td><?php echo htmlspecialchars($patient['email']); ?></td>
                            <td><?php echo htmlspecialchars($patient['age']); ?></td>
                            <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Admin Profile Section -->
        <div class="section hidden" id="profile-section">
            <h2>Admin Profile</h2>
            <form id="admin-profile-form">
                <div class="form-group">
                
                    <p><strong>Name:</strong> <?= htmlspecialchars($admin['username']); ?></p>

                </div>

                <div class="form-group">
                
                    <p><strong>Email:</strong> <?= htmlspecialchars($admin['email']); ?></p>
                </div>
            </form>
        </div>

    </div>

    <script>



        // Trigger fetch on pressing Enter in Patient ID field
// Trigger patient details fetch on Enter key
document.getElementById('patient-id').addEventListener('keypress', function (e) {
    if (e.key === 'Enter') {
        e.preventDefault();
        fetchPatientDetails();
    }
});

function fetchPatientDetails() {
    var patientId = document.getElementById('patient-id').value.trim();
    if (patientId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "get_patient_details.php?id=" + patientId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4) {
                try {
                    console.log("Raw Response:", xhr.responseText);
                    var response = JSON.parse(xhr.responseText);

                    if (xhr.status === 200 && response.success) {
                        document.getElementById('patient-name').value = response.name;
                        document.getElementById('age').value = response.age;
                        alert("✅ Patient Found!");
                    } else {
                        alert("❌ " + (response.message || "Patient not found!"));
                        clearPatientFields();
                    }
                } catch (error) {
                    console.error("❌ Error parsing JSON:", error);
                    console.log("❌ Full server response:", xhr.responseText);
                    alert("❌ Error parsing server response. Check the console for details.");
                }
            }
        };
        xhr.send();
    }
}

function clearPatientFields() {
    document.getElementById('patient-name').value = '';
    document.getElementById('age').value = '';
}

// Handle form submission
document.getElementById('post-result-form').addEventListener('submit', function (e) {
    e.preventDefault();

    var patientId = document.getElementById('patient-id').value.trim();
    var patientName = document.getElementById('patient-name').value.trim();
    var age = document.getElementById('age').value.trim();
    var bloodGlucose = document.getElementById('blood-glucose').value.trim();
    var bmi = document.getElementById('bmi').value.trim();

    if (!patientId || !patientName || !age || !bloodGlucose || !bmi) {
        alert("⚠️ Please fill all fields before submitting.");
        return;
    }

    var formData = new FormData();
    formData.append('patient_id', patientId);
    formData.append('patient_name', patientName);
    formData.append('age', age);
    formData.append('blood_glucose', bloodGlucose);
    formData.append('bmi', bmi);

    var xhr = new XMLHttpRequest();
    xhr.open("POST", "post_result.php", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            try {
                var response = JSON.parse(xhr.responseText);
                if (xhr.status === 200 && response.success) {
                    alert("✅ " + response.message);
                    document.getElementById('post-result-form').reset();
                    clearPatientFields();
                } else {
                    alert("❌ " + (response.message || "Error submitting report."));
                }
            } catch (e) {
                alert("❌ Error parsing submission response.");
            }
        }
    };
    xhr.send(formData);
});

// Fetch patients list for table
function fetchPatients() {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", "get_patient_details.php", true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            if (xhr.status === 200) {
                document.getElementById('patients-table-body').innerHTML = xhr.responseText;
            } else {
                alert("Failed to load patients list.");
            }
        }
    };
    xhr.open("GET", "get_patients.php", true);

    

}
setInterval(fetchPatients, 5000);

// Initial fetch
fetchPatients();




        function toggleSection(sectionId) {
            document.querySelectorAll('.section').forEach(sec => sec.classList.add('hidden'));
            document.getElementById(sectionId).classList.remove('hidden');
        }

        document.getElementById('home-link').addEventListener('click', () => toggleSection('home-section'));
        document.getElementById('post-result-link').addEventListener('click', () => toggleSection('post-result-section'));
        document.getElementById('view-patients-link').addEventListener('click', () => {
            fetchPatients();
            toggleSection('view-patients-section');
        });
        document.getElementById('profile-link').addEventListener('click', () => toggleSection('profile-section'));


        // Optional, ensures Home is visible on page load
        toggleSection('home-section');

    </script>

</body>

</html>