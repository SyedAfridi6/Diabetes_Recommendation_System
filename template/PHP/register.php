<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <script>
        function registerPatient(event) {
            event.preventDefault();  // Prevent page reload

            let formData = {
                username: document.getElementById("username").value,
                email: document.getElementById("email").value,
                password: document.getElementById("password").value,
                age: document.getElementById("age").value,
                gender: document.getElementById("gender").value,
            };

            fetch("http://localhost:5000/register", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify(formData)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Patient registered successfully!");
                    window.location.href = "login.php";
                } else {
                    document.getElementById("error-message").innerText = data.message;
                }
            })
            .catch(error => console.error("Error:", error));
        }
    </script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 75vh;
            margin: 0;
            background-color: #f4f4f4;
            font-family: Arial, sans-serif;
        }

        .form-container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            width: 320px;
            text-align: center;
        }

        .form-container h2 {
            margin-bottom: 15px;
            font-size: 22px;
        }

        .form-container label {
            display: block;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .form-container input,
        .form-container select {
            width: calc(100% - 20px);
            padding: 8px;
            margin-bottom: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 14px;
        }

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

        .form-container button:hover {
            background-color: #0056b3;
        }

        #error-message {
            color: red;
            margin-top: 10px;
        }
    </style>
</head>
<body>
<div class="form-container">
        <h2>Register Patient</h2>
        <form onsubmit="registerPatient(event)">
            
            <label for="username">Username:</label>
            <input type="text" id="username" placeholder="Enter your name" required>

            <label for="email">Email:</label>
            <input type="email" id="email" placeholder="Enter your email" required>

            <label for="password">Password:</label>
            <input type="password" id="password" placeholder="Enter your password" required>

            <label for="age">Age:</label>
            <input type="number" id="age" placeholder="Enter your age" required>

            <label for="gender">Gender:</label>
            <select name="gender" id="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Others">Others</option>
            </select>

            <button type="submit">Register</button>

            <p id="error-message"></p>
            <p class="register-link">already have an account? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>
