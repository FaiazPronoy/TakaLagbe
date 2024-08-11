<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Issues</title>
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
            color: #ffffff;
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
        <h1 class="mb-4">Issue Records</h1>
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th>User</th>
                    <th>Issues Description</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row["user_name"]; ?></td>
                        <td><?php echo $row["message"]; ?></td>
                        <td><?php echo $row["timestamp"]; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
