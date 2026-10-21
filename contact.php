<?php
/* ============================================================
   BlueBeck contact form handler (cPanel / PHP mail)
   ------------------------------------------------------------
   Receives the contact form POST and emails it to you.
   >>> SET THIS to the mailbox you actually check: <<<
   It should be a REAL email account that exists in cPanel
   (e.g. created under Email Accounts for bluebeck.org).
   ============================================================ */
$RECIPIENT = "hello@bluebeck.org";

// Only accept POST submissions; anything else goes back to the form.
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: contact.html");
    exit;
}

// Spam honeypot: real people leave the hidden "company" field blank.
// Bots tend to fill every field, so if it's filled we silently drop it.
if (!empty($_POST["company"])) {
    header("Location: contact.html?sent=1");
    exit;
}

// Strip line breaks from header-bound fields to prevent header injection.
function bb_clean($v) {
    return trim(str_replace(array("\r", "\n"), " ", (string) $v));
}

$first   = bb_clean($_POST["first_name"] ?? "");
$last    = bb_clean($_POST["last_name"]  ?? "");
$email   = bb_clean($_POST["email"]      ?? "");
$subject = bb_clean($_POST["subject"]    ?? "");
$message = trim($_POST["message"] ?? "");

// Basic validation: need a name, a valid email, and a message.
if ($first === "" || $message === "" || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: contact.html?sent=0");
    exit;
}

$name        = trim($first . " " . $last);
$mailSubject = "BlueBeck contact" . ($subject !== "" ? " — " . $subject : "");

$body  = "New message from the BlueBeck contact form\n";
$body .= "------------------------------------------\n";
$body .= "Name:  " . $name . "\n";
$body .= "Email: " . $email . "\n";
$body .= "Topic: " . ($subject !== "" ? $subject : "(none selected)") . "\n";
$body .= "------------------------------------------\n\n";
$body .= $message . "\n";

// From must be an address on your own domain or the host may reject it.
// Reply-To is the visitor, so you can just hit "Reply" to answer them.
$headers  = "From: BlueBeck Website <" . $RECIPIENT . ">\r\n";
$headers .= "Reply-To: " . $name . " <" . $email . ">\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

$ok = @mail($RECIPIENT, $mailSubject, $body, $headers);

header("Location: contact.html?sent=" . ($ok ? "1" : "0"));
exit;
