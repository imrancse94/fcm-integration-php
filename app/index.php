<?php

require 'FirebaseAuth.php';
require 'SendNotification.php';


$JSON = '{
    "type": "service_account",
    "project_id": "",
    "private_key_id": "",
    "private_key": "",
    "client_email": "",
    "client_id": "",
    "auth_uri": "https://accounts.google.com/o/oauth2/auth",
    "token_uri": "https://oauth2.googleapis.com/token",
    "auth_provider_x509_cert_url": "https://www.googleapis.com/oauth2/v1/certs",
    "client_x509_cert_url": "",
    "universe_domain": "googleapis.com"
}';

// auth for access token 
$firebaseAuth = new FirebaseAuth($JSON);

$sendNotification = new SendNotification($firebaseAuth);

$token = ""; // device token
$title = "This is title";
$body = "This is body";

$response = $sendNotification->sendMessage($token, $title, $body);

echo "<pre>";
print_r($response);
exit;