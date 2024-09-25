<?php
namespace MayaTracking;

class ApiTokenManager {
    
    private static $instance = null;
    private $access_token = null;
    private $token_expiry = null;
    private $api_url;
    private $username;
    private $password;

    private function __construct() {
        $this->api_url = defined('MAYA_API_URL') ? MAYA_API_URL : '';
        $this->username = defined('MAYA_API_USERNAME') ? MAYA_API_USERNAME : '';
        $this->password = defined('MAYA_API_PASSWORD') ? MAYA_API_PASSWORD : '';
        
        if (empty($this->api_url) || empty($this->username) || empty($this->password)) {
            error_log('Maya API credentials are not properly configured in wp-config.php');
        }
    }
    
    public function getApiUrl() {
        return $this->api_url;
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new ApiTokenManager();
        }
        return self::$instance;
    }

    public function getAccessToken() {
        if ($this->access_token === null || $this->isTokenExpired()) {
            $this->access_token = null;
            $this->token_expiry = null;
            $this->generateNewToken();
        }
        return $this->access_token;
    }

    private function isTokenExpired() {
        return $this->token_expiry === null || time() > $this->token_expiry;
    }

    private function generateNewToken() {
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $this->api_url . '/token',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => http_build_query(array(
                'grant_type' => 'password',
                'username' => $this->username,
                'password' => $this->password
            )),
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/x-www-form-urlencoded'
            ),
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            error_log("cURL Error #:" . $err);
            return false;
        } else {
            $result = json_decode($response, true);
            if (isset($result['access_token'])) {
                $this->access_token = $result['access_token'];
                $this->token_expiry = time() + $result['expires_in'];
                return true;
            } else {
                error_log("Authentication failed: " . ($result['error_description'] ?? 'Unknown error'));
                return false;
            }
        }
    }
}
