<?php
namespace MayaTracking;

class Client
{
    private $apiTokenManager;
    CONST ENDPOINT_ORDER = "/v3/orders";
    CONST ENDPOINT_SHIPMENT = "/v3/shipments";

    public function __construct(ApiTokenManager $apiTokenManager)
    {
        $this->apiTokenManager = $apiTokenManager;
    }

    public function get($endpoint, $params = [], $includeAuth = true)
    {
        return $this->request('GET', $endpoint, $params, $includeAuth);
    }

    public function post($endpoint, $data = [], $includeAuth = true)
    {
        return $this->request('POST', $endpoint, $data, $includeAuth);
    }

    private function request($method, $endpoint, $data = [], $includeAuth = true)
    {
        $curl = curl_init();
        $url = $this->apiTokenManager->getApiUrl() . $endpoint;

        if ($method === 'GET' && !empty($data)) {
            $url .= '?' . http_build_query($data);
        }

        $headers = ['Accept: application/vnd.api+json'];
        if ($includeAuth) {
            $headers[] = 'Authorization: Bearer ' . $this->apiTokenManager->getAccessToken();
        }

        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => $headers,
        ];

        if ($method === 'POST' && !empty($data)) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
            $headers[] = 'Content-Type: application/json';
        }

        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            throw new \Exception("cURL Error #:" . $err);
        }

        return $this->decodeJsonResponse($response);
    }
    private function decodeJsonResponse($response)
    {
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("JSON decoding error: " . json_last_error_msg());
        }
        return $decoded;
    }
}