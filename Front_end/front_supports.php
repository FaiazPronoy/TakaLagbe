<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Support Page - TakaLagbe! </title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        /* Global styles */
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

        /* Container styles */
        .container {
            margin-top: 50px;
            max-width: 800px;
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }

        .container h1 {
            text-align: center;
            margin-bottom: 20px;
            color: #125A66;
        }

        /* Form styles */
        .form-group label {
            font-weight: bold;
            color: #555;
        }

        .form-control {
            border-radius: 8px;
        }

        .btn-primary {
            border-radius: 8px;
            padding: 10px 20px;
            font-size: 18px;
            width: 100%;
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0a3b41;
        }

        /* Style the help message */
        .help-message {
            text-align: center;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #125A66;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-light bg-light">
        <a class="navbar-brand" href="http://localhost/TakaLagbe!/admin_login.php">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/Front_end/front_about.php">About Us</a></li>
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
<div class="container">
    <h1>Contact Us</h1>
    <?php if(isset($message_status)) { ?>
        <div class="alert alert-success"><?php echo $message_status; ?></div>
    <?php } ?>

    <form action="supports.php" method="post">
        <div class="form-group">
            <label for="user_name">Your Name:</label>
            <input type="text" class="form-control" id="user_name" name="user_name" required>
        </div>
        <div class="form-group">
            <label for="user_email">Your Email:</label>
            <input type="email" class="form-control" id="user_email" name="user_email">
        </div>
        <div class="form-group">
            <label for="user_phone">Your Phone Number:</label>
            <input type="text" class="form-control" id="user_phone" name="user_phone">
        </div>
        <div class="form-group">
            <label for="message">Message:</label>
            <textarea class="form-control" id="message" name="message" rows="4" required></textarea>
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-primary">Submit</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
