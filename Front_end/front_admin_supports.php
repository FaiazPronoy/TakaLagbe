<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Support Page</title>
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

        /* Style radio buttons */
        .form-check-input[type="radio"] {
            width: 20px;
            height: 20px;
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

        /* Add some padding to the cells in the "Action" column */
        td:last-child {
            padding-right: 20px;
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
        <h1 class="mb-4">Support Messages</h1>
        <form action="admin_supports.php" method="post">
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>User</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Message</th>
                        <th>Resolved</th>
                        <th>Time</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo $row["id"]; ?></td>
                            <td><?php echo $row["user_name"]; ?></td>
                            <td><?php echo $row["user_email"]; ?></td>
                            <td><?php echo $row["user_phone"]; ?></td>
                            <td><?php echo substr($row["message"], 0, 100); ?></td>
                            <td>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="issue_solved[<?php echo $row['id']; ?>]" value="1">
                                    <label class="form-check-label">Yes</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="issue_solved[<?php echo $row['id']; ?>]" value="0">
                                    <label class="form-check-label">No</label>
                                </div>
                            </td>
                            <td><?php echo $row["timestamp"]; ?></td>
                            <td>
                                <!-- <button type="submit" class="btn btn-primary">Submit</button> -->
                                <button type="submit" class="btn btn-outline-primary">Submit</button>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
