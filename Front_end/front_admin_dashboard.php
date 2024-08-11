<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Dashboard</title>
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
        color: #007BFF;
        text-decoration: none;
        font-weight: bold;
    }

    .container {
        margin-top: 20px;
    }

    /* Style for the dashboard boxes */
    .dashboard-box {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        transition: transform 0.3s ease-in-out;
        text-align: center;
    }

    .dashboard-box h2 {
        font-size: 24px;
        margin-bottom: 20px;
        color: #333; /* Darker text color */
    }

    /* Style for links within the boxes */
    .dashboard-box a {
        color: #007BFF;
        text-decoration: none;
        transition: color 0.2s;
    }

    /* Hover animation for boxes */
    .dashboard-box:hover {
        transform: scale(1.05);
        box-shadow: 0px 8px 20px rgba(0, 0, 0, 0.3);
    }

    /* Hover animation for links */
    .dashboard-box a:hover {
        color: #0056b3; /* Change link color on hover */
    }
</style>

</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="#">Admin Dashboard</a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <a class="navbar-brand" href="#">TakaLagbe!</a>
                <!-- <li class="nav-item"><a class="nav-link" href="http://localhost:8200/admin_user.php">User Info</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost:8200/admin_merchant.php">Merchant Info</a></li> -->
                <li class="nav-item"><a class="nav-link" href="http://localhost:8200/adminlogout.php">Logout</a></li>
            </ul>
        </div>
    </nav>
    <div class="container">
        <div class="row">
            <div class="col-md-3">
                <div class="dashboard-box">
                    <h2><a href="http://localhost:8200/admin_supports.php">Customer Supports</a></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-box">
                    <h2><a href="http://localhost:8200/admin_user.php">User Info</a></h2>
                </div>
            </div>
            <!-- <div class="col-md-3">
                <div class="dashboard-box">
                    <h2><a href="http://localhost:8200/admin_merchant.php">Merchant Info</a></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="dashboard-box">
                    <h2><a href="http://localhost:8200/admin_education.php">Education Info</a></h2>
                </div>
            </div> -->
            <div class="col-md-3">
    
                <div class="dashboard-box">
                    <h2><a href="http://localhost:8200/admin_issue.php">Resolved Issues</a></h2>
                </div>
            </div>
        </div>
    </div>

    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
