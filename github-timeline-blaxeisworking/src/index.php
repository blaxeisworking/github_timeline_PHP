<?php
require_once __DIR__ . '/functions.php';

session_start();

$successMessage = '';
$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["email"])) {
    $email = trim($_POST["email"]);
    $code = generateVerificationCode();
    $mailStatus = sendVerificationEmail($email, $code);

    if ($mailStatus) {
        $_SESSION["verification_code"] = $code;
        $_SESSION["pending_email"] = $email;
        $successMessage = "Verification code sent to your email.";
    } else {
        $errorMessage = "Failed to send verification email.";
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["verification_code"])) {
    $enteredCode = trim($_POST["verification_code"]);

    if (!isset($_SESSION['verification_code']) || !isset($_SESSION['pending_email'])) {
        $errorMessage = 'Session expired. Please enter your email again.';
    } elseif ($enteredCode === $_SESSION['verification_code']) {
        if (registerEmail($_SESSION['pending_email'])) {
            $successMessage = 'Your email has been successfully verified and registered.';
        } else {
            $errorMessage = 'Failed to register your email.';
        }
        unset($_SESSION['verification_code'], $_SESSION['pending_email']);
    } else {
        $errorMessage = 'Incorrect verification code.';
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>GitHub Timeline Subscription</title>
</head>
<body>
    <h2>Subscribe to GitHub Timeline Updates</h2>

    <?php if ($successMessage): ?>
        <p style="color: green;"><?php echo htmlspecialchars($successMessage); ?></p>
    <?php endif; ?>

    <?php if ($errorMessage): ?>
        <p style="color: red;"><?php echo htmlspecialchars($errorMessage); ?></p>
    <?php endif; ?>

    <!-- Email Submission Form -->
    <form method="post">
        <label>Email:</label>
        <input type="email" name="email" required>
        <button type="submit">Submit</button>
    </form>

    <br>

    <!-- Verification Code Form -->
    <form method="post">
        <label>Verification Code:</label>
        <input type="text" name="verification_code" maxlength="6" required>
        <button type="submit">Verify</button>
    </form>
</body>
</html>
