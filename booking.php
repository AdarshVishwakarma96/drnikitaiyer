<?php
$errors = '';

// IMPORTANT: use domain email
$fromEmail   = 'noreply@drnikitaiyer.com';
$adminEmail1 = 'dr.nikitaiyerent@gmail.com';
$adminEmail2 = 'nishimakkarlbm@gmail.com';
$adminEmail3 = 'adarsh96.av@gmail.com';
$adminEmail4 = 'adarshdesigndeveloper@gmail.com';

/* =========================
   Timezone (India)
========================= */
date_default_timezone_set('Asia/Kolkata');
$receivedOn = date('d-m-Y h:i A');

/* =========================
   reCAPTCHA verification
========================= */
if (!empty($_POST['recaptcha_response'])) {
    $recaptcha_url = 'https://www.google.com/recaptcha/api/siteverify';
    $recaptcha_secret = '6LdnHCcsAAAAALPBuCNGdd4MJG_5vixfL8eeTbaU';
    $recaptcha_response = $_POST['recaptcha_response'];

    $response = file_get_contents(
        $recaptcha_url . '?secret=' . $recaptcha_secret . '&response=' . $recaptcha_response
    );
    $response_keys = json_decode($response, true);

    if (!$response_keys['success'] || $response_keys['score'] < 0.5) {
        $errors .= "\nError: reCAPTCHA verification failed.";
    }
} else {
    $errors .= "\nError: reCAPTCHA response is missing.";
}

/* =========================
   Required fields
========================= */
if (
    empty($_POST['name']) ||
    empty($_POST['email']) ||
    empty($_POST['phone']) ||
    empty($_POST['concern']) ||
    empty($_POST['date']) ||
    empty($_POST['time'])
) {
    $errors .= "\nError: All fields are required.";
}

/* =========================
   Sanitize input
========================= */
$name    = htmlspecialchars(trim($_POST['name']));
$email   = filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL);
$phone   = htmlspecialchars(trim($_POST['phone']));
$concern = htmlspecialchars(trim($_POST['concern']));
$date    = htmlspecialchars(trim($_POST['date']));
$time    = htmlspecialchars(trim($_POST['time']));

if (!$email) {
    $errors .= "\nError: Invalid email address.";
}

/* =========================
   Process data
========================= */
if (empty($errors)) {

    /* ================= EMAIL ================= */

    $subject = "New Booking – Dr Nikita Iyer | $name";

    // HTML Email
    $messageHTML = "
    <html>
    <body style='font-family:Arial,sans-serif; line-height:1.6'>
        <h2>New Booking Received</h2>
        <p><strong>Received On:</strong> $receivedOn</p>
        <table cellpadding='8' cellspacing='0' border='1' width='100%'>
            <tr><td><strong>Name</strong></td><td>$name</td></tr>
            <tr><td><strong>Email</strong></td><td>$email</td></tr>
            <tr><td><strong>Phone</strong></td><td>$phone</td></tr>
            <tr><td><strong>Concern</strong></td><td>$concern</td></tr>
            <tr><td><strong>Date</strong></td><td>$date</td></tr>
            <tr><td><strong>Time</strong></td><td>$time</td></tr>
        </table>
        <p style='margin-top:20px'>
            — <br>
            <strong>Dr Nikita Iyer Website</strong>
        </p>
    </body>
    </html>";

    // Plain text fallback
    $messageText = "
New Booking Received

Received On: $receivedOn
Name: $name
Email: $email
Phone: $phone
Concern: $concern
Date: $date
Time: $time
";

    $boundary = md5(time());

    $headers  = "From: Dr Nikita Iyer <$fromEmail>\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Return-Path: $fromEmail\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
    $body .= $messageText . "\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
    $body .= $messageHTML . "\r\n";
    $body .= "--$boundary--";

    mail($adminEmail1, $subject, $body, $headers);
    mail($adminEmail2, $subject, $body, $headers);
    mail($adminEmail3, $subject, $body, $headers);
    mail($adminEmail4, $subject, $body, $headers);

    /* ================= STORE DATA ================= */

    $row = "
        <tr>
            <td>$receivedOn</td>
            <td>$name</td>
            <td>$email</td>
            <td>$phone</td>
            <td>$concern</td>
            <td>$date</td>
            <td>$time</td>
        </tr>
    ";

    $file = dirname(__DIR__, 2) . '/booking-details.php';

    if (file_exists($file) && is_writable($file)) {
        $content = file_get_contents($file);
        $content = str_replace('<tbody>', '<tbody>' . "\n" . $row, $content);
        file_put_contents($file, $content);
    }

    /* ================= Redirect ================= */
    header('Location: https://www.drnikitaiyer.com/thankyou');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Form Error</title>
</head>
<body>
<?php echo nl2br($errors); ?>
</body>
</html>
