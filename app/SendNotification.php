<?php


class SendNotification
{
    private $firebaseAuth = null;


    public function __construct($firebaseAuth)
    {

        $this->firebaseAuth = $firebaseAuth;
    }

    public function sendMessage($token, $title, $body, $image)
    {

        try {

            if (empty($this->firebaseAuth)) {
                throw new Error('Authenticated Error');
            }

            $tokenResponse = $this->firebaseAuth->generateAccessToken();

            if (empty($tokenResponse) || empty($tokenResponse['access_token'])) {
                throw new Error('Token generation error');
            }

            $apiurl = "https://fcm.googleapis.com/v1/projects/{$this->firebaseAuth->authConfig['project_id']}/messages:send";   //replace "your-project-id" with...your project ID

            $headers = [
                'Authorization: Bearer ' . $tokenResponse['access_token'],
                'Content-Type: application/json'
            ];

            $notification = array(
                "message" => array(
                    "token" => $token,
                    "notification" => array(
                        "title" => $title,
                        "body" => $body
                    ),
                    "data"=>[
                        "image"=>$image
                    ]
                )
            );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notification));

            $result = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (!$result) {
                //Failed
                die('Curl failed: ' . curl_error($ch));
            }

            curl_close($ch);

            return [
                'status' => $httpcode == 200 ? 'success' : 'failed',
                'data' => json_decode($result, true)
            ];
        } catch (Exception $ex) {
        }

        return null;
    }
}
