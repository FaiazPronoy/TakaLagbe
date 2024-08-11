<!DOCTYPE html>

<html lang="en">
<head> 
    <title>Send Money</title>
    <style>
        body {
            font-family: 'Oswald';
            background-color: #f4f4f4;
        }

        .navbar {
            background-color: white;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .navbar a {
            margin: 0 15px;
            color: black; 
            text-decoration: none;
            font-weight: bold;
        }

        main {
            padding: 20px;
        }

        h2.lgn {
            font-size: 24px;
            margin-bottom: 20px;
            text-align: center;
        }

        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        form label {
            font-weight: bold;
        }

        form input[type="text"],
        form input[type="password"],
        form input[type="number"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form button[type="submit"] {
            background-color: #125A66;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
        }

        form button[type="submit"]:hover {
            background-color: #0E4760;
        }

        .message {
            font-size: 18px;
            margin-top: 20px;
            text-align: center;
        }
        h1 {
        text-align: center; /* Center-align the h1 element */
        }
        .long-press-button {
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 0 auto;
            transition: background-color 0.3s ease-in-out; /* Add a transition effect */
        }

        /* Add a class for the animation effect */
        .animated {
            animation: pulse 0.5s infinite alternate; /* Define the animation properties */
        }

        /* Define the keyframes for the animation */
        @keyframes pulse {
            0% {
                transform: scale(1); /* Initial scale */
            }
            100% {
                transform: scale(1.1); /* Scale up */
            }
        }
    </style>
</head>
<body>
    <script>
        function updateLocalTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true };
            const localTime = now.toLocaleString('en-US', options);
            document.getElementById('local-time').textContent = localTime;
        }
        updateLocalTime();
        setInterval(updateLocalTime, 1000); 
        function validatePassword(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 6) {
                input.value = input.value.slice(0, 6);
            }
            }
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                        } else {
                passwordInput.type = "password";
            }
            }
    </script>

    <main class="container">

        <form action="sendmoney.php" method="post">
            <label for="sender_phone">Your Phone Number:</label>
            <input type="text" name="sender_phone" value="<?php echo $loggedInUserPhone; ?>" required readonly>

            <label for="receiver_phone">Receiver's Phone Number:</label>
            <input type="text" name="receiver_phone" required>

            <label for="amount">Amount:</label>
            <input type="number" name="amount" required>
            <label for="sender_password">Your Password:</label>
            <div class="form-group position-relative">
            <input type="password" class="form-control" name="sender_password" id="password" maxlength="6" oninput="validatePassword(this)" required>
            <i onclick="togglePasswordVisibility()" class="toggle-password" style="position: absolute; right: 60px; top: 65%; transform: translateY(-50%); cursor: pointer;">👁</i>
            </div>

            <button type="button" class="long-press-button" id="sendMoneyButton" onmousedown="startSending()" onmouseup="stopSending()">Tap and Hold For Send Money</button>
        </form>
        
        <?php
            if ($transaction_success) {
                echo "<div class='message text-success'>Money sent successfully!</div>";
            } elseif (!empty($transaction_error)) {
                echo "<div class='message text-danger'>$transaction_error</div>";
            }
        ?>
    </main>
    <script>
        var longPressTimer;

        function startSending() {
            // Start a timer for the long-press event
            longPressTimer = setTimeout(sendMoney, 1000); // Adjust the duration (in milliseconds) as needed
            // Add the 'animated' class to apply the animation effect
            document.getElementById("sendMoneyButton").classList.add("animated");
        }

        function stopSending() {
            // Clear the long-press timer when the button is released
            clearTimeout(longPressTimer);
            // Remove the 'animated' class to stop the animation effect
            document.getElementById("sendMoneyButton").classList.remove("animated");
        }

        function sendMoney() {
            // This function will be called when the button is long-pressed
            document.getElementById("sendMoneyButton").disabled = true; // Disable the button to prevent multiple submissions
            document.forms[0].submit(); // Submit the form
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>