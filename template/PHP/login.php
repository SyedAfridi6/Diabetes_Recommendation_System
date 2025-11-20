<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');

    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'Email and password are required.']);
        exit();
    }

    $flask_url = 'http://localhost:5000/login';
    $payload = json_encode(['email' => $email, 'password' => $password]);

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/json\r\n",
            'content' => $payload
        ]
    ];
    $context = stream_context_create($options);
    $result = file_get_contents($flask_url, false, $context);

    if ($result === FALSE) {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to connect to authentication server.']);
        exit();
    }

    $response = json_decode($result, true);

    if (!isset($response['token'])) {
        http_response_code(401);
        echo json_encode(['error' => $response['error'] ?? 'Invalid credentials.']);
        exit();
    }

    // ✅ Set admin session
    $_SESSION['auth_token'] = $response['token'];
    $_SESSION['user_type'] = 'admin';

    echo json_encode([
        'message' => $response['message'],
        'token' => $response['token'],
        'redirect' => 'http://localhost:5000' . $response['redirect'] . '?token=' . $response['token']
    ]);
    exit();
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <script>
        function loginUser(event) {
            event.preventDefault();

            let formData = new FormData();
            formData.append("email", document.getElementById("email").value);
            formData.append("password", document.getElementById("password").value);

            fetch("login.php", {
                method: "POST",
                body: formData
            })
            .then(response => response.text()) // ✅ First get raw text for debugging
            .then(text => {
                console.log("Raw response:", text); // 👀 Debug raw response
                let data = JSON.parse(text);
                alert(data.message);
                localStorage.setItem("authToken", data.token);
                window.location.href = data.redirect;
            })
            .catch(error => {
                console.error("Error:", error);
                document.getElementById("error-message").textContent = "Login failed: " + error.message;
            });
        }
    </script>
    <style>
        /* Centering the form */
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 70vh;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
            background-image: url("https://img.freepik.com/free-vector/flat-design-polygonal-background_23-2148900723.jpg?t=st=1741857577~exp=1741861177~hmac=2e9d6dfb9de9e77d544962cef255ee219b58b7e3dcc3ff8caab14f12fe322f6f&w=1380");
            background-size: cover;
            background-repeat: no-repeat;
            /* background-image: url('https://img.freepik.com/free-vector/gradient-geometric-shapes-dark-background-design_23-2148433740.jpg?t=st=1741857384~exp=1741860984~hmac=c6f85eb3b364ec42da1c9a810f2828bb74d30f0802f765cf924c5b8840fef987&w=1380'); */
        }

        /* Form container */
        .form-container {
            background: transparent;
            -webkit-backdrop-filter: blur(5px) !important;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 300px;
            text-align: center;
        }

        /* Form heading */
        .form-container h2 {
            /* margin-bottom: 15px; */
            font-size: 22px;
        }

        /* Input fields */
        .form-container input {
            width: calc(100% - 20px);
            padding: 10px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

        /* Submit button */
        .form-container button {
            background-color: #007BFF;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
        }

        /* Hover effect */
        .form-container button:hover {
            background-color: #0056b3;
        }

        /* Error message */
        #error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Login</h2>
        <form onsubmit="loginUser(event)">
            <input type="email" id="email" placeholder="Email" required>
            <input type="password" id="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p id="error-message"></p>
        <p class="register-link">Don't have an account? <a href="register.php">Register here</a></p>
    </div>
</body>
</html>
