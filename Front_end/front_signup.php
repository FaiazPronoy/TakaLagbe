<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SignUp Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>

        body {
            font-family: 'Oswald';
            background-color: #40A2E3; 
            margin: 0;
            padding: 0;
            color: #ffffff; 
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
            text-align: center;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.9); 
            border-radius: 8px;
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
            max-width: 800px;
            width: 100%;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .form-group {
            margin-right: 20px;
        }
        .form-control {
            border-radius: 8px;
            background-color: #ffffff; 
            color: #000000; 
            width: 100%;
        }
        .submit-button {
            background-color: #007bff; 
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
        }
        .submit-button:hover {
            background-color: #0056b3;
        }
        .form-group {
            position: relative;
            width: fit-content;
        }

        .form-control {
            padding-right: 30px; 
        }

        .toggle-password {
            color: #808080; 
            background: none; 
            cursor: pointer; 
        }


    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <a class="navbar-brand" href="#">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
            <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/login.php">Login</a></li> 
            <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/supports.php">Support</a></li>  
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
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                        } else {
                passwordInput.type = "password";
            }
            }
        function toggleConfirmPasswordVisibility() {
            var confirmPasswordInput = document.getElementById("confirm-password");
            if (confirmPasswordInput.type === "password") {
                confirmPasswordInput.type = "text";
            } else {
                confirmPasswordInput.type = "password";
            }
            }
        function validatePassword(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 6) {
                input.value = input.value.slice(0, 6);
            }
            }
        function validateNumber(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 11) {
                input.value = input.value.slice(0, 11);
            }
            }
        function validateNid(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 18) {
                input.value = input.value.slice(0, 18);
            }
            }
    </script>
    <div class="container">
        <div class="content">
            <div style="flex: 1;">
                <h2 style="font-weight: bold; color: #000000;">Welcome to TakaLagbe!</h2>
                <p style="font-weight: bold; color: #000000;">Your financial solution for easy transactions.</p>
                <h2 style="color: #000000;">Request for your Money Transaction service</h2>
            </div>
            <form action="signup.php" method="post" style="flex: 1;">
                <div class="form-group">
                    <input type="text" class="form-control" name="Firstname" placeholder="First Name" required>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="Lastname" placeholder="Last Name" required>
                </div>
                <div class="form-group">
                   <input type="text" class="form-control" name="Nid" placeholder="NID" maxlength="18" oninput="validateNid(this)" required>

                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="Email" placeholder="Email" required>
                </div>
                <div class="form-group">
                    <input type="text" class="form-control" name="Phone" placeholder="Phone" maxlength="11" oninput="validateNumber(this)" required>
                </div>
                <div class="form-group">
                    <select class="form-control" name="Gender" required>
                        <option value="" disabled selected>Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Others">Others</option>
                    </select>
                </div>
                <div class="form-group">
                    <input type="date" class="form-control" name="Dob" placeholder="Date of Birth" required>
                </div>
                <div class="form-group position-relative">
                <input type="password" class="form-control" name="Password" id="password" placeholder="Password"  title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
                <i onclick="togglePasswordVisibility()" class="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁</i>
                </div>
                <div class="form-group position-relative">
                <input type="password" class="form-control" name="ConfirmPassword" id="confirm-password" placeholder="Confirm Password"  title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
                <i onclick="toggleConfirmPasswordVisibility()" class="toggle-password" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); cursor: pointer;">👁</i>
                </div>
                <div class="text-center">
                <button class="submit-button" type="submit">Sign Up</button>
                </div>
                </form>

  
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
