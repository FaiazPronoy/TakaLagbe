<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About TakaLagbe!</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>

        body {
            font-family: 'Tilt Prism', 'Oswald';
            background-color: #40A2E3; 
            margin: 0;
            padding: 0;
            color: black; 
        }
        .navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }
        .navbar a {
            margin: 0 15px;
            color: #125A66; 
            text-decoration: none;
            font-weight: bold;
        }
        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 40px;
            background-color: #0E46A3; 
            border-radius: 12px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 900px;
            width: 100%;
        }
        .logo {
            max-width: 400px;
            height: auto;
        }
        .form-container {
            flex: 1;
            padding: 30px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .form-group {
            margin-bottom: 20px;
            width: 100%;
        }
        .form-control {
            border-radius: 8px;
            background-color: white; 
            color: #125A66; 
            width: 100%;
        }
        .btn-primary {
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 18px;
        }
        .text-center {
            text-align: center;
            color: white; 
        }
        .text-center a {
            color: #00ccff;
            text-decoration: none;
        }
        .text-center a:hover {
            text-decoration: underline;
        }
        .centered-text {
            text-align: center;
            margin-bottom: 20px;
        }
        .logo {
            max-width: 400px;
            height: auto;
            animation: fadeInUp 1s ease-in-out;
        }
        .welcome {
            animation: fadeInDown 1s ease-in-out;
        }
        .slogan {
            animation: fadeInUp 1s ease-in-out;
        }
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <a class="navbar-brand" href="http://localhost/TakaLagbe!/login.php">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/login.php">Log In</a></li>
                <li class="nav-item"><span id="local-time" class="nav-link"></span></li>
            </ul>
        </div>
    </nav>
    <script>
        function updateLocalTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
            const localTime = now.toLocaleString('en-US', options);
            document.getElementById('local-time').textContent = localTime;
        }
        updateLocalTime();
        setInterval(updateLocalTime, 1000); 
    </script>
    </nav>
    <div class="container">
        <div class="content">
            <div class="form-container">
                <div class="centered-text">
                    <h1 class="welcome" style="color:white">Empowering seamless financial interactions.</h1>
                    <p class="slogan" style="color:white">Money Wave is a cutting-edge online platform designed to revolutionize money transactions. With a user-friendly interface, it offers a range of essential financial services, including seamless money transfers, convenient bill payments, and hassle-free mobile recharges. Experience swift, secure, and efficient transactions with Money Wave, empowering you to manage your finances with confidence</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
