
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Diabetes Management System</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: Arial, sans-serif;
        }

        body {
            background-color: #f4f4f4;
            text-align: center;
        }

        .navbar {
            background: #007bff;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-radius: 20px;
        }

        .navbar a {
            color: white;
            text-decoration: none;
            font-size: 18px;
            padding: 10px 20px;
        }

        .navbar a:hover {
            background: #0056b3;
            border-radius: 5px;
        }

        .container {
            padding: 50px;
        }

        .btn {
            display: inline-block;
            padding: 12px 20px;
            margin: 10px;
            color: white;
            background: #28a745;
            text-decoration: none;
            border-radius: 5px;
        }

        .btn:hover {
            background: #218838;
        }

        /* Carousel container */
        .carousel {
            max-width: 800px;
            /* Adjust width as needed */
            margin: auto;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            margin-bottom: 50px;
        }

        .carousel img {
            object-fit: cover;
            height: 400px;
            /* Adjust height as needed */
        }

        .carousel-control-prev-icon,
        .carousel-control-next-icon {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            padding: 10px;
        }

        .carousel-indicators li {
            background-color: gray;
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }

        .carousel-indicators .active {
            background-color: white;
        }


        .get-started {
            background-color: #007BFF;
            color: white;
            text-align: center;
            padding: 20px 0;
        }

        .get-started h2 {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .get-started p {
            font-size: 18px;
            margin-bottom: 30px;
        }

        .get-started button {
            background-color: #fff;
            color: #007BFF;
            border: none;
            padding: 10px 20px;
            font-size: 18px;
            cursor: pointer;
            border-radius: 5px;
        }

        .get-started button:hover {
            background-color: #e0e0e0;
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            transition: transform 0.3s ease-in-out;
        }

        .card-body {
            background-color: gray;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card {
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            transition: transform 0.3s ease-in-out;
        }

        .card:hover {
            transform: scale(1.05);
        }

        .card img {
            height: 150px;
            /* Adjust image size */
            object-fit: cover;
        }

        .card-title {
            font-weight: bold;
        }

        .footer {
            text-align: center;
            padding: 15px 0;
            background: #007bff;
            color: white;
            width: 100%;
            position: relative;
            bottom: 0;
        }

        .footer-links {
            margin-bottom: 10px;
        }

        .footer-links a {
            text-decoration: none;
            color: white;
            margin: 0 10px;
            font-size: 16px;
        }

        .footer-links a:hover {
            text-decoration: underline;
        }

        hr {
            width: 80%;
            margin: 10px auto;
            border: 0;
            border-top: 1px solid #ccc;
        }
    </style>
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <!-- jQuery & Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

</head>

<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="index.php">Home</a>
        <div>
            <a href="../HTML/about.html" class="btn">About</a>
            <a href="./login.php" class="btn">Login</a>
            <a href="./register.php" class="btn">Register</a>
        </div>
    </div>

    <!-- Welcome Section -->
    <div class="container">

        <center>
            <h1>Welcome to the Diabetes Management System</h1>
        </center>

    </div>
    <div id="carouselExampleIndicators" class="carousel slide" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#carouselExampleIndicators" data-slide-to="0" class="active"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="1"></li>
            <li data-target="#carouselExampleIndicators" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner">
            <div class="carousel-item active">
                <img class="d-block w-100"
                    src="https://th.bing.com/th/id/OIP.zODQmO-rdPOuqvF9X0eQXgHaEK?rs=1&pid=ImgDetMain"
                    alt="First slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="https://wallpaperaccess.com/full/3702009.jpg" alt="Second slide">
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="https://wallpaperaccess.com/full/3702017.jpg" alt="Third slide">
            </div>
        </div>
        <a class="carousel-control-prev" href="#carouselExampleIndicators" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#carouselExampleIndicators" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>
    </div>
    <div class="get-started">
        <p>This system helps patients and doctors manage diabetes effectively.Track and manage your diabetes easily and
            effectively. Start today!</p>
        <p>Please log in or register to get started.</p>
        <a href="./login.php"><button>Get Started</button></a>

    </div>

    <div class="container mt-4">
        <div class="row">
            <!-- Diet Plan Card -->
            <div class="col-md-3">
                <div class="card">
                    <img class="card-img-top"
                        src="https://img.freepik.com/premium-photo/pineapple-healthy-fruits-wonderful-pineapple-juice_843175-11589.jpg"
                        alt="Diet Plan">
                    <div class="card-body">
                        <h5 class="card-title">Personalized Diet Plan </h5>
                        <p class="card-text">Get a custom diet plan based on your diabetes level.</p>
                        <a href="../html/diet.html" class="btn btn-primary">Diet Plan</a>
                    </div>
                </div>
            </div>

            <!-- Exercise Guide Card -->
            <div class="col-md-3">
                <div class="card">
                    <img class="card-img-top"
                        src="https://th.bing.com/th/id/OIP.-WhfyyKe8P5ctbqJ3KwcywHaEK?rs=1&pid=ImgDetMain"
                        alt="Exercise Guide">
                    <div class="card-body">
                        <h5 class="card-title">Exercise Recommendations</h5>
                        <p class="card-text">Discover the best exercises to control your blood sugar.</p>
                        <a href="../html/excercise.html" class="btn btn-primary">Workouts</a>
                    </div>
                </div>
            </div>

            <!-- Health Reports Card -->
            <div class="col-md-3">
                <div class="card">
                    <img class="card-img-top" src="https://thumbs.dreamstime.com/b/medical-report-14709207.jpg"
                        alt="Health Reports">
                    <div class="card-body">
                        <h5 class="card-title">Health Reports & Monitoring</h5>
                        <p class="card-text">Track your blood glucose, cholesterol, and vitals.</p>
                        <a href="../html/health.html" class="btn btn-primary">Reports</a>
                    </div>
                </div>
            </div>

            <!-- Doctor Consultation Card -->
            <div class="col-md-3">
                <div class="card">
                    <img class="card-img-top"
                        src="https://th.bing.com/th/id/OIP.LoqQ15EiLvHxNJVj_k8mXgHaHa?rs=1&pid=ImgDetMain"
                        alt="Doctor Consultation">
                    <div class="card-body">
                        <h5 class="card-title">Consult a Specialist</h5>
                        <p class="card-text">Get expert advice from professional healthcare providers.</p>
                        <a href="../html/doctor.html" class="btn btn-primary">Doctor</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div>
        <footer class="footer">
            <div class="footer-links">
                <a href="#">Home</a>
                <a href="./login.php">Login</a>
                <a href="./register.php">Register</a>
                <a href="../HTML/about.html">About</a>
            </div>
            <hr>
            <p>© 2025 Company, Inc</p>
        </footer>

    </div>

    <script>

        document.addEventListener("DOMContentLoaded", function () {
            // Auto cycle through slides every 3 seconds
            $('#carouselExampleIndicators').carousel({
                interval: 3000,
                pause: "hover"
            });

            // Manually switch slides on indicator click
            document.querySelectorAll(".carousel-indicators li").forEach((indicator, index) => {
                indicator.addEventListener("click", function () {
                    $('#carouselExampleIndicators').carousel(index);
                });
            });
        });

    </script>
</body>

</html>