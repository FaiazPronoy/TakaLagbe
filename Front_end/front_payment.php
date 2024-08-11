<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Payment to Merchant</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body style="background-color: #40A2E3;" class="flex items-center justify-center h-screen">
    <div class="bg-white p-10 rounded-lg shadow-xl max-w-lg"> <!-- Increased padding and max width -->
        <h2 class="text-3xl font-bold mb-8 text-center">Pay Merchant</h2> <!-- Increased text size -->
        <form action="http://localhost/TakaLagbe!/payment.php" method="post">
            <div class="mb-6">
                <label for="amount" class="block text-gray-700 text-lg font-bold mb-3">Amount:</label> <!-- Increased text size -->
                <input type="number" id="amount" name="amount" required class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"> <!-- Increased padding -->
            </div>
            <div class="mb-8">
                <label for="merchant_id" class="block text-gray-700 text-lg font-bold mb-3">Merchant ID:</label> <!-- Increased text size -->
                <input type="text" id="merchant_id" name="merchant_id" required class="shadow appearance-none border rounded w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"> <!-- Increased padding -->
            </div>
            <button type="submit" style="background-color: #007bff;" class="text-white font-bold py-3 px-6 rounded focus:outline-none focus:shadow-outline w-full text-lg hover:bg-emerald-950">Submit Payment</button> <!-- Increased padding and text size -->
        </form>
    </div>
</body>
</html>
