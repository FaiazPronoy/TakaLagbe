<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TakaLagbe! - Mobile Recharge</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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
        form input[type="number"],
        form select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }

        form button[type="submit"] {
            background-color: #007bff;
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
    </style>
</head>
<body>
    <!-- <nav class="navbar navbar-expand-md navbar-light bg-light">
        <a class="navbar-brand" href="#">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/account.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/currency.php">Currency Converter</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/history.php">History</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/logout.php">Logout</a></li>
                <li class="nav-item"><span id="local-time" class="nav-link"></span></li>
            </ul>
        </div>
    </nav> -->

    <main class="container">
        <h2 class="lgn">Mobile Recharge</h2>
        <form action="mobilerecharge.php" method="post">
            <div class="form-group">
                <label for="operatorSelect">Select Operator:</label>
                <select class="form-control" id="operatorSelect" name="operatorSelect" onchange="togglePhoneNumberInput()">
                    <option value="">Select Operator</option>
                    <option value="airtel">Airtel</option>
                    <option value="robi">Robi</option>
                    <option value="banglalink">Banglalink</option>
                    <option value="skitto">Skitto</option>
                    <option value="teletalk">Teletalk</option>
                    <option value="grameenphone">Grameenphone</option>
                </select>
            </div>
            <div class="form-group" id="phoneNumberGroup" style="display: none;">
                <label for="rechargePhoneNumber">Phone Number:</label>
                <input type="text" class="form-control" id="rechargePhoneNumber" name="rechargePhoneNumber" required>
            </div>
            <div class="form-group">
                <label for="rechargeAmount">Recharge Amount:</label>
                <input type="number" class="form-control" id="rechargeAmount" name="rechargeAmount" required>
            </div>
            <div class="form-group">
                <label for="rechargePassword"> Your Password:</label>
                <input type="password" class="form-control" id="rechargePassword" name="rechargePassword" required>
            </div>
            <button type="submit" class="btn btn-primary">Recharge</button>
        </form>

        <?php
        if (!empty($recharge_success)) {
            echo "<p class='message text-success'>$recharge_success</p>";
        } elseif (!empty($recharge_error)) {
            echo "<p class='message text-danger'>$recharge_error</p>";
        }
        ?>

        <!-- <p>Your current balance: <?php echo $balance; ?></p> -->
    </main>

    <script>
        // Function to toggle phone number input based on operator selection
        function togglePhoneNumberInput() {
            var operatorSelect = document.getElementById("operatorSelect");
            var phoneNumberGroup = document.getElementById("phoneNumberGroup");
            var rechargePhoneNumber = document.getElementById("rechargePhoneNumber");

            // Get the selected operator
            var selectedOperator = operatorSelect.value;

            // Define the operator prefixes
            var operatorPrefixes = {
                "airtel": "+88016",
                "robi": "+88018",
                "banglalink": "+88019",
                "skitto": "+88013",
                "teletalk": "+88015",
                "grameenphone": "+88017"
            };

            // Set the phone number input value based on the selected operator
            if (selectedOperator && operatorPrefixes[selectedOperator]) {
                rechargePhoneNumber.value = operatorPrefixes[selectedOperator];
                phoneNumberGroup.style.display = "block";
            } else {
                rechargePhoneNumber.value = "";
                phoneNumberGroup.style.display = "none";
            }
        }
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>