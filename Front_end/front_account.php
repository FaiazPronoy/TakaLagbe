<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <!-- Add your CSS styling here -->
    <style>
        body {
            font-family: 'Oswald';
            background-color: #40A2E3; /* Background color */
            margin: 0;
            padding: 0;
            color: black; /* Text color */
        }
        .navbar {
            background-color: white;
            border-bottom: 1px solid #ddd;
            padding: 10px 0;
        }

        .navbar a {
            margin: 0 15px;
            color: black; /* Dark blue text color */
            text-decoration: none;
            font-weight: bold;
        }

        .account-title {
            text-align: center;
            margin-bottom: 20px;
        }

        .user-box {
            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 30px;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
        }

        .user-box p {
            margin: 0;
        }

        .qr-code img {
            max-width: 100%;
        }

        /* Your existing CSS */

        .action-box {
            background-color: #ffffff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s;
            width: 100%; /* Adjust the width as needed */
            margin-bottom: 20px; /* Add margin at the bottom of each action box */
        }

        /* Additional styles for the rows of action boxes */
        .row.text-center:last-child .action-box {
            margin-bottom: 0; /* Remove margin for the last row */
        }

/* Adjust other styles as needed */


        .action-box:hover {
            transform: translateY(-5px);
        }

        .text-below {
            margin-top: 10px;
        }

        .action-box a {
            color: #000000; /* Dark blue text color */
            text-decoration: none;
        }

        .btn-check-balance {
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s;
    /* New styles for basic size */
    padding: 5px 10px; /* Adjust padding as needed */
    font-size: 14px; /* Adjust font size as needed */
}

.btn-check-balance:hover {
    background-color: #0B3B44;
}
.nav-link .emoji {
        color: grey;
    }
        /* Your existing styles here */

        /* Custom styles for the modal */
        .modal-content {
            border-radius: 10px;
        }

        /* Style the iframe to fill the modal body */
        .modal-body iframe {
            width: 100%;
            height: 500px; /* Adjust the height as needed */
            border: none;
            opacity: 0; /* Initially hidden */
            transition: opacity 0.5s ease-in-out; /* Add a fade-in transition */
        }

        /* Show the iframe when it's loaded */
        .modal-body iframe.loaded {
            opacity: 1;
        }


</style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light bg-light">
        <a class="navbar-brand" href="#">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="history.php">History</a></li>
                <?php include('front_notifications.php'); ?>
                <li class="nav-item"><a class="nav-link" href="Front_end/front_currency.php">Currency Converter</a></li> 
                <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                <li class="nav-item"><span id="local-time" class="nav-link"></span></li>
            </ul>
        </div>
    </nav>   


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

    <main class="container mt-5">
        <h2 class="account-title,"style="color:white;">Welcome to your Account</h2>
        <div class="user-box">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Hello,</strong> <?php echo $first_name." ".$last_name ; ?></p>
                    <p><strong>Phone Number:</strong> <?php echo $_SESSION["user_phone"]; ?></p>
                </div>
            </div>
            <a href="#" class="btn btn-primary btn-block btn-check-balance" id="opencheckbalanceModal">Check Balance</a>

        </div>

        <div class="row text-center">
            <div class="col-md-3">
                <div class="action-box">
                    <a href="#" id="openSendMoneyModal">
                        <img src="Assets/sendmoney.jpg" width="180" alt="Send Money">
                    </a>
                    <div class="text-below">
                        <a href="#">Send Money</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="http://localhost/TakaLagbe!/cash_in.php" id="openCashInModal">
                        <img src="Assets/cashin.jpg" width="180" height="180" alt="Cash In">
                    </a>
                    <div class="text-below">
                        <a href="#">Cash in</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="http://localhost/TakaLagbe!/cash_out.php" id="openCashOutModal">
                        <img src="Assets/cashout.jpg" width="180" height="180" alt="Cash Out">
                    </a>
                    <div class="text-below">
                        <a href="#">Cash Out</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="#" id="openMobileRechargeModal">
                        <img src="Assets/mobilerecharge.jpg" width="180" height="180" alt="Mobile Recharge">
                    </a>
                    <div class="text-below">
                        <a href="#">Mobile Recharge</a>
                    </div>
                </div>
                <br>

            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="http://localhost/TakaLagbe!/Front_end/front_payment.php">
                        <img src="Assets/makepayment.jpg" width="180" height="180" alt="Payment">
                    </a>
                    <div class="text-below">
                        <a href="#">Payment</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="http://localhost/TakaLagbe!/Front_end/front_paybill.php">
                        <img src="Assets/bill.jpg" width="180" height="180" alt="Pay Bill">
                    </a>
                    <div class="text-below">
                        <a href="#">Pay Bill</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="#" id="openeducationfeeModal">
                        <img src="Assets/education.jpg" width="180" height="180" alt="Education Fee">
                    </a>
                    <div class="text-below">
                        <a href="#">Education Fee</a>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="action-box">
                    <a href="#" id="openaddMoneyModal">
                        <img src="Assets/add_money.jpg" width="180" height="180" alt="Add Money">
                    </a>
                    <div class="text-below">
                        <a href="#">Add Money</a>
                    </div>
                </div>
            </div>
    </main>


    <div class="modal fade" id="sendMoneyModal" tabindex="-1" role="dialog" aria-labelledby="sendMoneyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sendMoneyModalLabel">Send Money</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="loaded"></iframe> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="addMoneyModal" tabindex="-1" role="dialog" aria-labelledby="addMoneyModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addMoneyModalLabel">Add Money</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="loaded"></iframe> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="mobileRechargeModal" tabindex="-1" role="dialog" aria-labelledby="mobileRechargeModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="mobileRechargeModalLabel">Mobile Recharge</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="loaded"></iframe> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="checkbalance" tabindex="-1" role="dialog" aria-labelledby="checkbalanceModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="checkbalanceLabel">Check Balance</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="loaded"></iframe> 
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="educationfeeModal" tabindex="-1" role="dialog" aria-labelledby="educationfeeLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="educationfeeLabel">Education Fee</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe src="" class="loaded"></iframe> 
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function openModal(modalId, contentUrl) {
            $(modalId + ' iframe').attr('src', contentUrl);
            $(modalId).modal('show'); 
        }

    
        $('.modal-body iframe').on('load', function() {
            $(this).addClass('loaded');
        });

        $('#openSendMoneyModal').click(function (e) {
            e.preventDefault(); // Prevent the default link behavior
            openModal('#sendMoneyModal', 'http://localhost/TakaLagbe!/sendmoney.php');
        });

        $('#openaddMoneyModal').click(function (e) {
            e.preventDefault(); // Prevent the default link behavior
            openModal('#addMoneyModal', 'http://localhost/TakaLagbe!/add_money.php');
        });

        $('#openMobileRechargeModal').click(function (e) {
            e.preventDefault(); // Prevent the default link behavior
            openModal('#mobileRechargeModal', 'http://localhost/TakaLagbe!/mobilerecharge.php');
        });
        $('#opencheckbalanceModal').click(function (e) {
        e.preventDefault(); // Prevent the default link behavior
        openModal('#checkbalance', 'http://localhost/TakaLagbe!/checkbalance.php');
        });
        $('#openeducationfeeModal').click(function (e) {
            e.preventDefault(); // Prevent the default link behavior
            openModal('#educationfeeModal', 'http://localhost/TakaLagbe!/education_fee.php');
        });


    </script>
</body>
</html>