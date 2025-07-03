<?php

function generateVerificationCode(): string {
    return str_pad(strval(random_int(0, 999999)), 6, '0', STR_PAD_LEFT);
}

function sendVerificationEmail(string $email, string $code): bool {
    $subject = "Your Verification Code";
    $message = "<p>Your verification code is: <strong>$code</strong></p>";
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8\r\n";
    $headers .= "From: no-reply@example.com\r\n";

    return mail($email, $subject, $message, $headers);
}

function registerEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;

    $emails = file_exists($file) ? file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) : [];

    if (in_array($email, $emails)) return true; // Already registered

    $emails[] = $email;

    return file_put_contents($file, implode(PHP_EOL, $emails) . PHP_EOL) !== false;
}

function unsubscribeEmail(string $email): bool {
    $file = __DIR__ . '/registered_emails.txt';

    if (!file_exists($file)) return false;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $updatedEmails = array_filter($emails, fn($e) => trim($e) !== trim($email));

    return file_put_contents($file, implode(PHP_EOL, $updatedEmails) . PHP_EOL) !== false;
}

function fetchGitHubTimeline() {
    $url = 'https://www.github.com/timeline';
    $context = stream_context_create([
        "http" => ["method" => "GET", "header" => "User-Agent: PHP"]
    ]);
    $response = @file_get_contents($url, false, $context);
    if ($response === false) return null;

    $json = json_decode($response, true);
    return $json;
}

function formatGitHubData(array $data): string {
    $html = '<h2>GitHub Timeline Updates</h2>';
    $html .= '<table border="1"><tr><th>Event</th><th>User</th></tr>';

    foreach ($data as $item) {
        $event = htmlspecialchars($item['type'] ?? 'Unknown');
        $user = htmlspecialchars($item['actor']['login'] ?? 'Unknown');
        $html .= "<tr><td>$event</td><td>$user</td></tr>";
    }

    $html .= '</table>';
    return $html;
}

function sendGitHubUpdatesToSubscribers(): void {
    $file = __DIR__ . '/registered_emails.txt';
    if (!file_exists($file)) return;

    $emails = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $data = fetchGitHubTimeline();
    if (!$data) return;

    $htmlContent = formatGitHubData($data);

    foreach ($emails as $email) {
        $unsubscribeLink = 'http://localhost/github-timeline-blaxeisworking/src/unsubscribe.php?email=' . urlencode($email);
        $message = $htmlContent . '<p><a href="' . $unsubscribeLink . '" id="unsubscribe-button">Unsubscribe</a></p>';

        $subject = 'Latest GitHub Updates';
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8\r\n";
        $headers .= "From: no-reply@example.com\r\n";

        mail($email, $subject, $message, $headers);
    }
}
