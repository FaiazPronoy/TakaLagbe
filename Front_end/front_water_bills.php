<!DOCTYPE html>
<html>
<head>
    <title>Water Bill Payment</title>
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

        form select {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            background: url('down-arrow-icon.png') no-repeat right;
            background-size: 12px 12px;
            padding-right: 30px;
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

        .form-group {
            position: relative;
            display: flex;
            align-items: center;
        }

        .toggle-password {
            position: absolute;
            right: 10px;
            cursor: pointer;
            top: 50%;
            transform: translateY(-50%);
        }

        .message {
            font-size: 18px;
            margin-top: 20px;
            text-align: center;
        }

        h1 {
            text-align: center;
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
            transition: background-color 0.3s ease-in-out;
        }

        .animated {
            animation: pulse 0.5s infinite alternate;
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }
            100% {
                transform: scale(1.1);
            }
        }
    </style>
    <script>
        var longPressTimer;

        function startPaybill() {
            longPressTimer = setTimeout(PayBill, 1000);
            document.getElementById("Paybill_Button").classList.add("animated");
        }

        function stopPaybill() {
            clearTimeout(longPressTimer);
            document.getElementById("Paybill_Button").classList.remove("animated");
        }

        function PayBill() {
            document.getElementById("Paybill_Button").disabled = true;
            document.forms[0].submit();
        }

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
</head>
<body>
    <h1>Water Bill</h1>
    <?php if (!empty($error_message)) { ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php } ?>
    <form action="water_bills.php" method="POST">
        <label for="organization_id">Organization ID:</label>
        <input type="text" id="organization_id" name="organization_id" required>
        <label for="customer_no">Customer No:</label>
        <input type="text" id="customer_no" name="customer_no" required>
        <label for="amount">Amount:</label>
        <input type="text" id="amount" name="amount" required>
        <label for="month">Bill Month:</label>
        <select id="month" name="month" required>
            <option value="">--Select a Month--</option>
            <option value="January">January 2024</option>
            <option value="February">February 2024</option>
            <option value="March">March 2024</option>
            <option value="April">April 2024</option>
            <option value="May">May 2024</option>
            <option value="June">June 2024</option>
            <option value="July">July 2024</option>
            <option value="August">August 2024</option>
            <option value="September">September 2024</option>
            <option value="October">October 2024</option>
            <option value="November">November 2024</option>
            <option value="December">December 2024</option>
        </select>
        <label for="password">Password:</label>
        <div class="form-group position-relative">
            <input type="password" class="form-control" name="password" id="password" pattern="\d{6}" title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
            <i onclick="togglePasswordVisibility()" class="toggle-password" style="position: absolute; right: 15px; top: 35%; transform: translateY(-50%); cursor: pointer;">👁</i>
        </div>
        <button type="button" class="long-press-button" id="Paybill_Button" onmousedown="startPaybill()" onmouseup="stopPaybill()">Tap and Hold For Pay Bill</button>
    </form>
</body>
</html>
