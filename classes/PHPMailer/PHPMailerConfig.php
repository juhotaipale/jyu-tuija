<?php
//$mail->isSMTP();

//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 4;
$mail->Debugoutput = 'html';

$mail->Host = EMAIL_HOST;
$mail->Port = EMAIL_PORT;
$mail->SMTPAuth = EMAIL_AUTH;
$mail->Username = EMAIL_AUTH_USERNAME;
$mail->Password = EMAIL_AUTH_PASSWORD;
$mail->SMTPSecure = EMAIL_SMTP_SECURE;
$mail->SMTPOptions = EMAIL_SMTP_OPTIONS;