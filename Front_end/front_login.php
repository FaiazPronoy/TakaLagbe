<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Page</title>
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
            color: #7EA1FF; 
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
            color: #000000; 
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
        <a class="navbar-brand" href="#">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
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

        function toggleNewPasswordVisibility() {
            var passwordInput = document.getElementById("newPasswordInput");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
        function validateNumber(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 11) {
                input.value = input.value.slice(0, 11);
            }
            }
        function validatePassword(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 6) {
                input.value = input.value.slice(0, 6);
            }
        }
        function validateNid(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 18) {
                input.value = input.value.slice(0, 18);
            }
        }

    </script>


    <div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="resetPasswordModalLabel">Reset Password</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
            </button>
        </div>
        <div class="modal-body">
            <form id="resetPasswordForm">
            <div class="form-group">
                <label for="phoneInput">Phone Number:</label>
                <input type="text" class="form-control" id="phoneInput" name="phone" maxlength="11" oninput="validateNumber(this)" required>
            </div>
            <div class="form-group">
                <label for="nidInput">NID:</label>
                <input type="text" class="form-control" id="nidInput" name="nid" maxlength="18" oninput="validateNid(this)" required>
            </div>
            <div class="form-group ">
                    <label for="newPasswordInput">New Password:</label>
                    <input type="password" class="form-control" style="color:black" name="newPassword"  id="newPasswordInput" pattern="\d{6}" title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
                    <i onclick="toggleNewPasswordVisibility()" class="toggle-password" style="position: absolute; right: 30px; top: 65%; transform: translateY(-50%); cursor: pointer;">👁</i>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                <button type="submit" class="btn btn-primary">Change Password</button>
            </div>
            </form>
        </div>
        </div>
    </div>
    </div>

    <div class="container">
        <div class="content">
            <img src="assets/mw.jpg" alt="TakaLagbe! Logo" class="logo">
            <div class="form-container">
                <div class="centered-text">
                    <h1 class="welcome" style="color:white">Welcome to TakaLagbe!</h1>
                    <p class="slogan" style="color:white">Your financial solution for easy transactions.</p>
                </div>
                <form action="login.php" method="post" style="width: 100%;">
                    <div class="form-group">
                        <label for="phone" style="color:white">Phone No:</label>
                        <input type="text" class="form-control" id="phone" name="Phone" placeholder="Enter your Phone No."maxlength="11" oninput="validateNumber(this)" required>
                    </div>
                    <div class="form-group position-relative">
                        <label for="password" style="color:white">Password:</label>
                        <input type="password" class="form-control" name="Password" id="password" placeholder="Enter your Password" pattern="\d{6}" title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
                        <i onclick="togglePasswordVisibility()" class="toggle-password" style="position: absolute; right: 10px; top: 75%; transform: translateY(-50%); cursor: pointer;">👁</i>
                     </div>
                    <button type="submit" class="btn btn-primary btn-block">Login</button>
                </form>
                <div class="text-center mt-3">
                    <p>Don't have an account? <a href="http://localhost/TakaLagbe!/signup.php">Sign Up</a></p>

                    <p><a href="#" data-toggle="modal" data-target="#resetPasswordModal">Forgot Password?</a></p>


                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>

    $(document).ready(function(){
            $("#resetPasswordForm").submit(function(e){
                e.preventDefault(); // This will prevent the default form submission
                var phone = $("#phoneInput").val();
                var nid = $("#nidInput").val();
                var newPassword = $("#newPasswordInput").val();
                $.ajax({
                    url: 'reset_password.php', // Make sure this points to the correct path
                    type: 'POST',
                    data: {
                        phone: phone,
                        nid: nid,
                        newPassword: newPassword
                    },
                    success: function(response) {
                        alert(response); // You should see the message here
                    },
                    error: function(xhr, status, error) {
                        alert('Error: ' + error.message);
                    }
                });
            });
        });

    </script>



</body>
</html>

