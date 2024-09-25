<?php
namespace MayaTracking;

class ShipmentInfoRetriever
{
    private $apiTokenManager;

    public function __construct(ApiTokenManager $apiTokenManager)
    {
        $this->apiTokenManager = $apiTokenManager;
    }

    public function getActiveAntsShipmentInfo($order_id)
    {
        $curl = curl_init();
        $next_cursor = 0;
        $matching_shipments = [];
        $result = [];

        do {
            $api_url = $this->apiTokenManager->getApiUrl();
            $access_token = $this->apiTokenManager->getAccessToken();
            $url = $api_url . "/v3/shipments?include=shipmentItems&filter[orderId][in]=" . $order_id . "&page[size]=100&page[cursor]=" . $next_cursor;

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => array(
                    'Authorization: Bearer ' . $access_token,
                    'Accept: application/vnd.api+json'
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);

            if ($err) {
                return "cURL Error #:" . $err;
            }

            // $result = json_decode($response, true);
            $currentResult = json_decode($response, true);

            if (isset($currentResult['data'])) {
                foreach ($currentResult['data'] as $shipment) {
                    if ($shipment['relationships']['order']['id'] == $order_id) {
                        $matching_shipments = $shipment;
                        return $matching_shipments;
                    }
                }
            }

            $result[] = $currentResult;

            // Check if there's a next page
            $next_cursor = 0;
            if (isset($result['links']['next'])) {
                $next_url = $result['links']['next']['href'];
                parse_str(parse_url($next_url, PHP_URL_QUERY), $query_params);
                $next_cursor = $query_params['page']['cursor'] ?? 0;
            }

        } while ($next_cursor !== 0 && empty($matching_shipments));

        curl_close($curl);
        return null;
    }

    public function getActiveAntsOrderInfo($external_order_number)
    {
        try {
            $api_url = $this->apiTokenManager->getApiUrl();
            $access_token = $this->apiTokenManager->getAccessToken();
            $order_info = get_active_ants_order_info($access_token, $api_url, $external_order_number);
            if (!isset($order_info['data'][0]['id'])) {
                error_log("Order not found for external order number: " . $external_order_number);
                return null;
            }
            $order_id = $order_info['data'][0]['id'];

            $shipment_info = get_active_ants_shipment_info_by_loop($access_token, $api_url, $order_id);
            if (!$shipment_info) {
                error_log("Shipment not found for order ID: " . $order_id);
                return null;
            }

            if (!isset($shipment_info['attributes']['tracking']['url'])) {
                error_log("Tracking URL not found for order ID: " . $order_id);
                return null;
            }

            return $shipment_info['attributes']['tracking']['url'];
        } catch (\Exception $e) {
            error_log("Exception in maya_shipping_info_page: " . $e->getMessage());
            return null;
        }
    }

    public function getMayaShippingInfo($external_order_number)
    {

        try {
            $api_url = $this->apiTokenManager->getApiUrl();
            $access_token = $this->apiTokenManager->getAccessToken();
            $order_info = get_active_ants_order_info($access_token, $api_url, $external_order_number);
            if (!isset($order_info['data'][0]['id'])) {
                error_log("Order not found for external order number: " . $external_order_number);
                return null;
            }
            $order_id = $order_info['data'][0]['id'];

            $shipment_info = get_active_ants_shipment_info_by_loop($access_token, $api_url, $order_id);
            if (!$shipment_info) {
                error_log("Shipment not found for order ID: " . $order_id);
                return null;
            }

            if (!isset($shipment_info['attributes']['tracking']['url'])) {
                error_log("Tracking URL not found for order ID: " . $order_id);
                return null;
            }

            return $shipment_info['attributes']['tracking']['url'];
        } catch (\Exception $e) {
            error_log("Exception in maya_shipping_info_page: " . $e->getMessage());
            return null;
        }
    }
}