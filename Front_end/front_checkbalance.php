<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Check Balance</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f0f0;
            font-family: 'Oswald';
            color: #333;
        }
        header {
            background-color: #125A66;
            color: white;
            text-align: center;
            padding: 20px 0;
        }
        .container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0px 0px 15px rgba(0, 0, 0, 0.1);
            margin-top: 40px;
            text-align: center;
        }
        h1 {
            font-size: 36px;
            margin-bottom: 20px;
        }
        .balance-display {
            font-size: 48px;
            margin-top: 20px;
            color: #125A66;
            font-weight: bold;
        }
        .timestamp {
            color: #888;
        }
    </style>
</head>
<body>
    <!-- <header>
        <h1>Check Balance</h1>
    </header> -->

    <main>
        <div class="container">
            <h2>Your Balance</h2>
            <p class="balance-display">&#2547; <?php echo $balance; ?></p>
            <p class="timestamp">Last updated: <span id="local-time"></span></p>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/5.0.0-alpha2/js/bootstrap.min.js"></script>
</body>
</html>
