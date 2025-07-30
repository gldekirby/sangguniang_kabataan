<?php
require '../vendor/autoload.php';

use AndroidSmsGateway\Client;
use AndroidSmsGateway\Domain\Message;

$response = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'], $_POST['number'], $_POST['username'], $_POST['password'])) {
        $login = $_POST['username'];
        $password = $_POST['password'];
        $messageContent = $_POST['message'];
        $numberInput = $_POST['number'];

        // Format and validate number
        $number = "+63" . ltrim($numberInput, '0');

        if (!preg_match('/^\+639\d{9}$/', $number)) {
            $response = '❌ Error: Philippine mobile number must start with 9 (format: +639XXXXXXXXX) and be 10 digits long after +63.';
        } else {
            $client = new Client($login, $password);
            $message = new Message($messageContent, [$number]);

            try {
                $messageState = $client->Send($message);
                $response = "✅ Message sent! ID: " . $messageState->ID();

                // Optionally check message state
                $messageState = $client->GetState($messageState->ID());
                $response .= "<br>📬 Message state: " . $messageState->State();
            } catch (Exception $e) {
                $response = "❌ Error: " . $e->getMessage();
            }
        }
    } else {
        $response = '❌ Please provide all required fields: message, number, username, and password.';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SMS Sender</title>
</head>
<body>
    <h1>Send SMS</h1>
    <?php if ($response): ?>
        <p><strong><?= $response ?></strong></p>
    <?php endif; ?>

    <form method="post" action="">
        <!-- Hidden credentials -->
        <input type="hidden" name="username" value="KWBUN-" />
        <input type="hidden" name="password" value="2342Gldekirby@21" />
        
        <!-- User inputs -->
        <label>Message:</label><br>
        <input type="text" name="message" required><br><br>

        <label>Phone number:</label><br>
        <input type="text" name="number" placeholder="9XXXXXXXXX" required><br><br>

        <button type="submit">Send</button>
    </form>
</body>
</html>
