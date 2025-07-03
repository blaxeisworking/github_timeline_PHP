<?php
require_once __DIR__ . '/functions.php';

session_start();

$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['unsubscribe_email'])) {
        $email = trim($_POST['unsubscribe_email']);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Invalid email address.';
        } else {
            $code = generateVerificationCode();
            $_SESSION['unsubscribe_email'] = $email;
            $_SESSION['unsubscribe_code'] = $code;
            $subject = 'Confirm Unsubscription';
            $message = "<p>To confirm unsubscription, use this code: <strong>$code</strong></p>";
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8\r\n";
            $headers .= "From: no-reply@example.com\r\n";

            if (mail($email, $subject, $message, $headers)) {
                $successMessage = 'Unsubscribe confirmation code sent to your email.';
            } else {
                $errorMessage = 'Failed to send unsubscription code.';
            }
        }
    }
    if (isset($_POST['unsubscribe_verification_code'])) {
        $enteredCode = trim($_POST['unsubscribe_verification_code']);

        if (!isset($_SESSION['unsubscribe_email']) || !isset($_SESSION['unsubscribe_code'])) {
            $errorMessage = 'Session expired. Please try again.';
        } elseif ($enteredCode === $_SESSION['unsubscribe_code']) {
            if (unsubscribeEmail($_SESSION['unsubscribe_email'])) {
                $successMessage = 'You have been successfully unsubscribed.';
            } else {
                $errorMessage = 'Unsubscription failed or email not found.';
            }
            unset($_SESSION['unsubscribe_email'], $_SESSION['unsubscribe_code']);
        } else {
            $errorMessage = 'Incorrect unsubscription code.';
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Unsubscribe</title>
</head>
<body>
    <h2>Unsubscribe from GitHub Timeline Emails</h2>

    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Email:</label>
        <input type="email" name="unsubscribe_email" required>
        <button type="submit" id="submit-unsubscribe">Unsubscribe</button>
    </form>

    <br>
    <form method="post">
        <label>Verification Code:</label>
        <input type="text" name="unsubscribe_verification_code" maxlength="6">
        <button type="submit" id="verify-unsubscribe">Verify</button>
    </form>
</body>
</html>
