<!DOCTYPE html>
<html>
<head>
    <title>Cash Out</title>
    <style>
        body {
            font-family: 'Oswald';
            background-color: #40A2E3;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 80vh;
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 40px;
            font-size: 28px;
            font-weight: 600;
            letter-spacing: 0.5px;
            height: 60vh;
            justify-content: center;
            align-items: center;
        }

        form {
            background-color: #ffffff;
            padding: 50px;
            border-radius: 8px;
            box-shadow: 0px 0px 20px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 600px;
        }

        label {
            display: block;
            margin-bottom: 10px;
            color: #555;
            font-size: 16px;
            font-weight: 500;
        }

        input[type="text"],
        input[type="number"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 400;
            transition: border-color 0.3s ease-in-out;
        }

        input[type="text"]:focus,
        input[type="number"]:focus {
            border-color: #333366;
            outline: none;
        }

        input[type="submit"] {
            background-color: #007bff;
            color: #ffffff;
            padding: 15px 30px;
            border: none;
            border-radius: 5px;
            font-size: 18px;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.3s ease-in-out;
        }

        input[type="submit"]:hover {
            background-color: #555;
        }

        @media (max-width: 768px) {
            form {
                padding: 40px;
            }
        }
    </style>
</head>
<body>

<h2>Cash Out from Agent</h2>

<form action="insertcashout.php" method="post">
    <label for="agent_id">Agent ID:</label>
    <input type="text" id="agent_id" name="agent_id" required>

    <label for="phone_number">Phone Number:</label>
    <input type="text" id="phone_number" name="phone_number" required>

    <label for="cash_in_amount">Cash-out Amount:</label>
    <input type="number" id="cash_in_amount" name="cash_in_amount" required>

    <input type="submit" value="Cash Out">
</form>

</body>
</html>
