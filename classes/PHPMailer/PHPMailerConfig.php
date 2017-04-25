<?php
$mail->isSMTP();
$mail->CharSet = EMAIL_CHARSET;
$mail->Host = EMAIL_HOST;
$mail->Port = EMAIL_PORT;
$mail->SMTPAuth = EMAIL_AUTH;
$mail->Username = EMAIL_AUTH_USERNAME;
$mail->Password = EMAIL_AUTH_PASSWORD;
$mail->SMTPSecure = EMAIL_SMTP_SECURE;
$mail->SMTPOptions = EMAIL_SMTP_OPTIONS;