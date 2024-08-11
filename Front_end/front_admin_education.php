<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Education Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .navbar {
            background-color: white;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .navbar a {
            margin: 0 15px;
            color: #000;
            text-decoration: none;
            font-weight: bold;
        }

        .container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dee2e6;
        }

        th, td {
            padding: 8px;
            text-align: left;
            border: 1px solid #dee2e6;
        }

        th {
            background-color: #f2f2f2;
            color: #333;
        }

        /* Style table rows and alternate row colors */
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        /* Style the "Submit" buttons */
        .btn-primary {
            border-radius: 4px;
            padding: 8px 15px;
            font-size: 14px;
            background-color: #007BFF;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <a class="navbar-brand" href="#">TakaLagbe!</a>
                <li class="nav-item"><a class="nav-link" href="http://localhost:8200/admin_dashboard.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost:8200/adminlogout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <h1 class="mb-4">Education Fee Records</h1>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <!-- <th>ID</th> -->
                    <th>Institute ID</th>
                    <th>Institute Name</th>
                    <th>Student ID</th>
                    <th>Student Name</th>
                    <th>Semester</th>
                    <th>Month</th>
                    <th>Amount</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <!-- <td><?php echo $row["id"]; ?></td> -->
                        <td><?php echo $row["institute_id"]; ?></td>
                        <td><?php echo $row["institute_name"]; ?></td>
                        <td><?php echo $row["student_id"]; ?></td>
                        <td><?php echo $row["student_name"]; ?></td>
                        <td><?php echo $row["semester"]; ?></td>
                        <td><?php echo $row["month"]; ?></td>
                        <td><?php echo $row["amount"]; ?></td>
                        <td><?php echo $row["timestamp"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
