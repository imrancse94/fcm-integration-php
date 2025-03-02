<?php

class FirebaseAuth
{

    private $CURRENT_TIME = null; 

    const SCOPE = 'https://www.googleapis.com/auth/firebase.messaging';


    private $AUTH_CONFIG_STRING = '';

    private $header = null;

    // Parse service account details
    public $authConfig = null;

    // Read private key from service account details
    private $secret = null;

    private $end = null;
    private $start = null;

    private $payload = [];

    public function __construct($json)
    {
        $this->CURRENT_TIME = time(); // Get seconds since 1 January 1970
        $this->setHeader();
        $this->setAuthConfig($json);
        $this->setSecret();
        $this->setExpiry();
        $this->setPayload();
    }


    private function setSecret(){
        $this->secret = openssl_get_privatekey($this->authConfig['private_key']);
    } 

    private function setAuthConfig($string){
        $this->authConfig = json_decode($string, true);
    }

    private function setHeader(){
        $this->header = json_encode([
            'typ' => 'JWT',
            'alg' => 'RS256'
        ]);
    }

    private function setPayload()
    {
        // Create payload
        $this->payload = json_encode([
            "iss" => $this->authConfig['client_email'],
            "scope" => self::SCOPE,
            "aud" => $this->authConfig['token_uri'],
            "exp" => $this->end,
            "iat" => $this->start
        ]);
    }

    private function setExpiry()
    {
        // Allow 1 minute time deviation between client en server (not sure if this is necessary)
        $this->start = $this->CURRENT_TIME - 60;
        $this->end = $this->start + 3600;
    }


    private function base64UrlEncode($text)
    {
        return str_replace(
            ['+', '/', '='],
            ['-', '_', ''],
            base64_encode($text)
        );
    }


    public function generateAccessToken()
    {
        try {

            $signature = ""; // we are taking is as empty

            // Encode Header
            $base64UrlHeader = $this->base64UrlEncode($this->header);

            // Encode Payload
            $base64UrlPayload = $this->base64UrlEncode($this->payload);

            // Create Signature Hash
            $result = openssl_sign($base64UrlHeader . "." . $base64UrlPayload, $signature, $this->secret, OPENSSL_ALGO_SHA256);

            // Encode Signature to Base64Url String
            $base64UrlSignature = $this->base64UrlEncode($signature);

            // Create JWT
            $jwt = $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;

            //-----Request token, with an http post request------
            $options = array('http' => array(
                'method'  => 'POST',
                'content' => 'grant_type=urn:ietf:params:oauth:grant-type:jwt-bearer&assertion=' . $jwt,
                'header'  => "Content-Type: application/x-www-form-urlencoded"
            ));

            $context  = stream_context_create($options);
            
            $responseText = file_get_contents($this->authConfig['token_uri'], false, $context);

            if(empty($responseText)){
                throw new Error('Something went error');
            }

            return json_decode($responseText, true);

        } catch (Exception $ex) {
            
        }

        return null;
    }
}
