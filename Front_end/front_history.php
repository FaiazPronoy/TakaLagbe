<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="Styles/history.css">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color:#40A2E3;
            color: #333;
            font-family: 'Oswald';
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
        .container {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #ddd;
        }
        h2 {
            font-size: 24px;
            margin-bottom: 20px;
        }
        /* Improved table styling */
        .table {
            font-size: 14px;
        }
        .table thead th {
            background-color: #f2f2f2;
            color: #333;
            font-weight: bold;
            border: none;
        }
        .table tbody td {
            border: none;
            color: #555;
        }
        .table tbody tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .table tbody tr:hover {
            background-color: #ddd;
        }

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="#" style="color: black;">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/account.php">Home</a></li>
                <li class="nav-item">


                </li>
                <li class="nav-item"><span id="local-time" class="nav-link"></span></li>
            </ul>
        </div>
    </nav>

   
    <main>
        <div class="container">
            <h2>Transaction History</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Transaction From</th>
                        <th>Transaction To</th>
                        <th>Transaction Type</th>
                        <th>Transaction Time</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($history_result->num_rows > 0) {
                        while ($row = $history_result->fetch_assoc()) {
                            $time24 = $row["transaction_time"];
                            $time12 = date("Y-m-d h:i A", strtotime($time24)); 
                            echo "<tr>";
                            echo "<td>" . $row["transaction_from"] . "</td>";
                            echo "<td>" . $row["transaction_to"] . "</td>";
                            echo "<td>" . $row["transaction_type"] . "</td>";
                            echo "<td>" . $time12 . "</td>"; 
                            echo "<td>" . number_format($row["amount"], 2) . "</td>";                                                
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='5'>No transactions found.</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>

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
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
