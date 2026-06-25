<?php
// 1. Database Connection Configuration
$host = "sql211.infinityfree.com"; // Your exact host from dashboard
$user = "if0_42261444";            // Your DB username
$pass = "25P6Kxtv9d";  // Change to your hidden DB password string if it updates
$dbname = "if0_42261444_backup";   // Your backup database name

// 2. Telegram API Credentials
$telegram_token = "8614805045:AAGkonSeO5zV0bAFE8pGdzJXWzS5YZetoBA"; // Token from BotFather
$telegram_chat_id = "1511328981"; // Your personal Chat ID verified by userinfobot

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database Error: " . $e->getMessage());
}

$status_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim(htmlspecialchars($_POST['name']));
    $email = trim(htmlspecialchars($_POST['email']));
    $message_text = trim(htmlspecialchars($_POST['message']));

    if (!empty($name) && !empty($email) && !empty($message_text)) {
        
        // A. Save message into the secure local MySQL database archive
        $stmt = $conn->prepare("INSERT INTO contact_messages (name, email, message) VALUES (:name, :email, :message)");
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':message', $message_text);
        $stmt->execute();

        // B. Compile the Telegram alert text layout
        $telegram_text = "📩 *New Contact Form Submission!*\n\n";
        $telegram_text .= "*Name:* " . $name . "\n";
        $telegram_text .= "*Email:* " . $email . "\n\n";
        $telegram_text .= "*Message:*\n" . $message_text;

        // C. Dispatch the message directly to your Telegram chat app instantly (High-Compatibility Mode)
        $telegram_url = "https://api.telegram.org/bot" . $telegram_token . "/sendMessage";
        $post_fields = [
            'chat_id' => $telegram_chat_id,
            'text' => $telegram_text,
            'parse_mode' => 'Markdown'
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $telegram_url);
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_fields)); // Formats variables perfectly for free host firewalls
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // Skips deep certificate validation drops
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);    // Gives the network gateway 10 seconds to respond
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);

        $status_message = "Your message has been dispatched successfully!";
    } else {
        $status_message = "Please populate all fields before sending.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NRG Hub | Dispatch Communication</title>
    <style>
        :root {
            --bg-dark: #0a0a0f;
            --neon-blue: #00f0ff;
            --neon-pink: #ff00ff;
            --glass: rgba(255, 255, 255, 0.05);
        }
        body {
            font-family: 'Segoe UI', Arial, sans-serif;
            background-color: var(--bg-dark);
            color: #ffffff;
            padding: 2rem 1.5rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
        }
        .form-container { width: 100%; max-width: 550px; background: var(--glass); border: 1px solid rgba(255,255,255,0.1); padding: 2.5rem; border-radius: 16px; box-shadow: 0 15px 35px rgba(0,0,0,0.5); }
        h1 { color: var(--neon-pink); text-transform: uppercase; font-size: 1.75rem; margin-bottom: 1.5rem; letter-spacing: 1.5px; text-shadow: 0 0 10px rgba(255, 0, 255, 0.3); text-align: center; }
        label { display: block; margin-bottom: 0.5rem; text-transform: uppercase; font-size: 0.8rem; color: #b5b5c0; letter-spacing: 0.5px; }
        input, textarea { width: 100%; padding: 0.85rem; background: rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.1); border-radius: 6px; color: #fff; margin-bottom: 1.5rem; box-sizing: border-box; font-size: 1rem; transition: 0.3s; }
        input:focus, textarea:focus { border-color: var(--neon-pink); outline: none; box-shadow: 0 0 8px rgba(255,0,255,0.2); }
        button { background: transparent; border: 1px solid var(--neon-pink); color: var(--neon-pink); padding: 0.9rem 2rem; text-transform: uppercase; font-weight: 600; letter-spacing: 1px; border-radius: 6px; cursor: pointer; transition: 0.3s; width: 100%; font-size: 1rem; }
        button:hover { background: var(--neon-pink); color: #000; box-shadow: 0 0 15px rgba(255,0,255,0.4); }
        .status { text-align: center; color: var(--neon-blue); font-weight: 600; margin-bottom: 1.5rem; text-shadow: 0 0 8px rgba(0,240,255,0.3); }
    </style>
</head>
<body>

    <div class="form-container">
        <h1>Secure Communication</h1>
        
        <?php if(!empty($status_message)): ?>
            <div class="status"><?php echo $status_message; ?></div>
        <?php endif; ?>

        <form method="POST" action="contact.php">
            <label>Identification / Name</label>
            <input type="text" name="name" required placeholder="Anonymous or Identity">

            <label>Return Transmission Email</label>
            <input type="email" name="email" required placeholder="name@domain.com">

            <label>Message Content</label>
            <textarea name="message" rows="6" required placeholder="Write your reflection or inquiry here..."></textarea>

            <button type="submit">Transmit Message</button>
        </form>
    </div>

</body>
</html>