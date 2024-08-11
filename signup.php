<?php
session_start();
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $phone = $_POST["Phone"];
    $firstname = ucwords(strtolower($_POST["Firstname"]));
    $lastname = ucwords(strtolower($_POST["Lastname"]));
    $nid = $_POST["Nid"];
    $email = $_POST["Email"];
    $password = $_POST["Password"];
    $confirmPassword = $_POST["ConfirmPassword"]; 
    $dob = $_POST["Dob"];
    $selectedGender = $_POST["Gender"];

    $today = new DateTime();
    $birthdate = new DateTime($dob);
    $age = $today->diff($birthdate)->y;

    $isMale = $isFemale = $isOthers = false;

    if ($selectedGender === "Male") {
        $isMale = true;
    } elseif ($selectedGender === "Female") {
        $isFemale = true;
    } elseif ($selectedGender === "Others") {
        $isOthers = true;
    }

    if ($age < 18) {
        echo "Error: You must be at least 18 years old to sign up.";
    } elseif (strlen($password) !== 6) {
        echo "Error: Password must be 6 digits in length.";
    } elseif (strlen($phone) !== 11) {
        echo "Error: Phone Number must be 11 digits in length.";
    } elseif (!in_array(strlen($nid), [10, 13, 18])) {
        echo "Error: NID must be 10 Digits / 13 Digits / 18 Digits in length.";
    } elseif ($password !== $confirmPassword) { 
        echo "Error: Password and Confirm Password do not match.";

    } else {
        $servername = "localhost";
        $username_db = "root";
        $password_db = "";
        $dbname = "TakaLagbe";
        $conn = new mysqli($servername, $username_db, $password_db, $dbname);
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        // Check if email, NID, or phone number already exists
        $email_query = "SELECT * FROM user WHERE email = '$email'";
        $email_result = $conn->query($email_query);

        $nid_query = "SELECT * FROM user WHERE nid = '$nid'";
        $nid_result = $conn->query($nid_query);

        $phone_query = "SELECT * FROM user WHERE phone = '$phone'";
        $phone_result = $conn->query($phone_query);

        if ($email_result->num_rows > 0) {
            echo "Error: Email already exists. Please use a different one.";
        } elseif ($nid_result->num_rows > 0) {
            echo "Error: NID already exists. Please use a different one.";
        } elseif ($phone_result->num_rows > 0) {
            echo "Error: Phone number already exists. Please use a different one.";
        } else {
            // Unique nid, email, and phone number
            $insert_user_sql = "INSERT INTO user (phone, firstname, lastname, nid, email, password, dob, male, female, others) VALUES ('$phone', '$firstname', '$lastname', '$nid', '$email', '$password', '$dob', '$isMale', '$isFemale', '$isOthers')";
            if ($conn->query($insert_user_sql) === TRUE) {
                $default_balance = 0;
                $insert_account_sql = "INSERT INTO account (phone, password, balance) VALUES ('$phone', '$password', '$default_balance')";
                if ($conn->query($insert_account_sql) === TRUE) {
                    header("Location: login.php");
                    exit;
                } else {
                    echo "Error inserting account info: " . $conn->error;
                }
            } else {
                echo "Error inserting user info: " . $conn->error;
            }
        }
        $conn->close();
    }
}
?>

<?php
include('Front_end/front_signup.php');
?>


