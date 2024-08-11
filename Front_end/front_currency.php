<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Currency Converter</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
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
        
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.2);
            margin-top: 20px;
        }
        .form-group {
            margin-bottom: 1rem;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .btn-primary:hover {
            background-color: #0A3640;
        }
        .table {
            width: 100%;
            margin-bottom: 1rem;
            color: #333; /* Dark text color */
        }

        .table thead th {
            background-color: #007bff;
            color: white;
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }

        .table tbody td {
            background-color: #E0E0E0;
            text-align: center;
            vertical-align: middle;
            padding: 10px;
        }
        
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-md navbar-light">
        <a class="navbar-brand" href="#">TakaLagbe!</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ml-auto">
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/account.php">Home</a></li>
                <li class="nav-item"><a class="nav-link" href="http://localhost/TakaLagbe!/history.php">History</a></li>
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

    <div class="container">
        <h2 class="text-center">Currency Converter</h2>
        <div class="row justify-content-center mt-4">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="amount">Amount:</label>
                    <input type="number" id="amount" class="form-control" placeholder="Enter Amount">
                </div>
                <select id="fromCurrency" class="custom-select">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="JPY">JPY</option>
                    <option value="BDT">BDT</option>
                </select>
            </div>
            <div class="col-md-1 align-self-center text-center">
                <i class="fas fa-exchange-alt fa-2x"></i>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="convertedAmount">Converted Amount:</label>
                    <input type="text" id="convertedAmount" class="form-control" placeholder="Converted Amount" readonly>
                </div>
                <select id="toCurrency" class="custom-select">
                    <option value="USD">USD</option>
                    <option value="EUR">EUR</option>
                    <option value="JPY">JPY</option>
                    <option value="BDT">BDT</option>
                </select>
            </div>
        </div>
        <div class="row justify-content-center mt-3">
            <div class="col-md-8 text-center">
                <button id="convertButton" class="btn btn-primary btn-block">Convert</button>
            </div>
        </div>
    </div>

    <div class="container mt-5">
        <h2 class="text-center mb-4">Currency Conversion Table</h2>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Currency</th>
                    <th>Conversion Rate to BDT</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>USD</td>
                    <td>109.86</td>
                </tr>
                <tr>
                    <td>EUR</td>
                    <td>118.49</td>
                </tr>
                <tr>
                    <td>JPY</td>
                    <td>0.75</td>
                </tr>
                <tr>
                    <td>BDT</td>
                    <td>1.00</td>
                </tr>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.3/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('convertButton').addEventListener('click', function () {
            const amount = parseFloat(document.getElementById('amount').value);
            const fromCurrency = document.getElementById('fromCurrency').value;
            const toCurrency = document.getElementById('toCurrency').value;

            const conversionRates = {
                USD: 109.86,
                EUR: 118.49,
                JPY: 0.75,
                BDT: 1.00
            };

            let convertedAmount;

            if (fromCurrency === 'BDT') {
                convertedAmount = amount / conversionRates[toCurrency];
            } else if (toCurrency === 'BDT') {
                convertedAmount = amount * conversionRates[fromCurrency];
            } else {
                const fromRate = conversionRates[fromCurrency];
                const toRate = conversionRates[toCurrency];
                convertedAmount = (amount * toRate) / fromRate;
            }

            document.getElementById('convertedAmount').value = convertedAmount.toFixed(2);
        });
    </script>
</body>
</html>
