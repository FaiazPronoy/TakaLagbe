<!DOCTYPE html>
<html>
<head>
    <title>Education Fee</title>
    
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
            width: 100%; /* Match width */
            padding: 10px; /* Match padding */
            margin-bottom: 15px; /* Match margin */
            border: 1px solid #ccc; /* Match border */
            border-radius: 4px; /* Match border-radius */
            box-sizing: border-box; /* Use box-sizing to include padding and border in the width */
        }

        form select {
            -webkit-appearance: none; /* Remove default styling */
            -moz-appearance: none; /* Remove default styling */
            appearance: none; /* Remove default styling */
            background: url('down-arrow-icon.png') no-repeat right; /* Add custom arrow icon */
            background-size: 12px 12px; /* Size of the custom arrow */
            padding-right: 30px; /* Adjust padding to make room for the arrow */
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
            text-align: center; /* Center-align the h1 element */
        }

        .long-press-button {
            font: 'Oswald';
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

        .institute_list {
            font-family: 'Oswald';
            background-color: #007bff;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 4px;
            cursor: pointer;
            display: block;
            margin: 20px auto; /* Center the button */
            transition: background-color 0.3s ease-in-out;
        }
        .institute_list:hover {
            background-color: #0056b3;
        }
        .modal {
            display: none; /* Hidden by default */
            /* ... other styles ... */
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
<script>
        var longPressTimer;

        function startEducation_fee() {
            // Start a timer for the long-press event
            longPressTimer = setTimeout(Education_fee, 1000); // Adjust the duration (in milliseconds) as needed
            // Add the 'animated' class to apply the animation effect
            document.getElementById("Education_fee_Button").classList.add("animated");
        }

        function stopEducation_fee() {
            // Clear the long-press timer when the button is released
            clearTimeout(longPressTimer);
            // Remove the 'animated' class to stop the animation effect
            document.getElementById("Education_fee_Button").classList.remove("animated");
        }

        function Education_fee() {
            // This function will be called when the button is long-pressed
            document.getElementById("Education_fee_Button").disabled = true; // Disable the button to prevent multiple submissions
            document.forms[0].submit(); // Submit the form

        }
        function togglePasswordVisibility() {
            var passwordInput = document.getElementById("password");
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
            } else {
                passwordInput.type = "password";
            }
        }
        function validatePassword(input) {
            input.value = input.value.replace(/[^0-9]/g, '');
            if (input.value.length > 6) {
                input.value = input.value.slice(0, 6);
            }
        }
        function toggleInstituteInfoModal() {
            var modal = document.getElementById('instituteInfoModal');

            // Check if the modal is currently displayed
            if (modal.style.display === 'block') {
                modal.style.display = 'none'; // Hide the modal
            } else {
                modal.style.display = 'block'; // Show the modal
            }
        }

        
</script>
<body>
    <h1>Education Fee </h1>


    <button type="button" id="institute_info_button" class="institute_list" onclick="toggleInstituteInfoModal()">Institutes Info</button>


    <!-- Modal Structure -->
    <div id="instituteInfoModal" class="modal">
        <!-- Modal content -->
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2>Institutes List</h2>
            <!-- Include institutes_list.php within an iframe -->
            <iframe id="instituteListFrame" style="width:100%; height:300px; border:none;" src="institutes_list.php"></iframe>
        </div>
    </div>


    <?php if (!empty($error_message)) { ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php } ?>
    <form action="education_fee.php" method="POST">
        <label for="institute_id">Institute ID:</label>
        <input type="text" id="institute_id" name="institute_id" value="<?php echo htmlspecialchars($institute_id); ?>" required>

        <label for="institute_name">Institute Name:</label>
        <input type="text" id="institute_name" name="institute_name" value="<?php echo htmlspecialchars($institute_name); ?>" >

        <label for="student_id">Student ID:</label>
        <input type="text" id="student_id" name="student_id" value="<?php echo htmlspecialchars($student_id); ?>" required>

        <label for="student_name">Student Name:</label>
        <input type="text" id="student_name" name="student_name" value="<?php echo htmlspecialchars($student_name); ?>" required>

        <label for="semester">Semester:</label>
        <input type="text" id="semester" name="semester" value="<?php echo htmlspecialchars($semester); ?>" >

        <label for="amount">Amount:</label>
        <input type="text" id="amount" name="amount" value="<?php echo htmlspecialchars($amount); ?>" required >
        <label for="password">Password:</label>
        <div class="form-group position-relative">
            <input type="password" class="form-control" name="password" id="password" pattern="\d{6}" title="6-digit number" maxlength="6" oninput="validatePassword(this)" required>
            <i onclick="togglePasswordVisibility()" class="toggle-password" style="position: absolute; right: 15px; top: 35%; transform: translateY(-50%); cursor: pointer;">👁</i>
        </div>
        <button type="button" class="long-press-button" id="Education_fee_Button" onmousedown="startEducation_fee()" onmouseup="stopEducation_fee()">Tap and Hold For Education Fee</button>
    </form>
</body>
</html>
