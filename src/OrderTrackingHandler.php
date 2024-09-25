<?php
namespace MayaTracking;

class OrderTrackingHandler
{
    private $apiTokenManager;

    public function __construct(ApiTokenManager $apiTokenManager)
    {
        $this->apiTokenManager = $apiTokenManager;
    }
    private function generateMayaTrackingUrl($external_order_number) {
        try {
            $access_token = $this->apiTokenManager->getAccessToken();
            $api_url = $this->apiTokenManager->getApiUrl();
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
    public function getOrGenerateTrackingUrl($order_id)
    {
        $order = wc_get_order($order_id);

        if (!$order) {
            error_log("Order not found for ID: " . $order_id);
            return null;
        }

        // Try to get the existing tracking URL
        $tracking_url = $order->get_meta(MAYA_TRACKING_URL_META_KEY);

        // If the tracking URL exists, return it
        if ($tracking_url) {
            return $tracking_url;
        }

        // If not, generate a new one
        $external_order_number = $order->get_order_number();

        // Get the API token
        $token_manager = ApiTokenManager::getInstance();
        $access_token = $token_manager->getAccessToken();

        if (!$access_token) {
            error_log("Failed to obtain access token for order #" . $order_id);
            return null;
        }

        $api_url = 'https://shopapi.activeants.nl';

        // Get the tracking URL
        $tracking_url = maya_shipping_info_page($access_token, $api_url, $external_order_number);

        if ($tracking_url) {
            // Save the newly generated tracking URL
            $order->update_meta_data(MAYA_TRACKING_URL_META_KEY, $tracking_url);
            $order->save();
            error_log("New tracking URL generated and saved for order #" . $order_id . ": " . $tracking_url);
        } else {
            error_log("Failed to generate tracking URL for order #" . $order_id);
        }

        return $tracking_url;
    }

    public function handleNewOrderTracking($order_id)
    {
        $tracking_url = $this->getOrGenerateTrackingUrl($order_id);

        if ($tracking_url) {
            error_log("Tracking URL processed for order #" . $order_id . ": " . $tracking_url);
        } else {
            error_log("Unable to process tracking URL for order #" . $order_id);
        }
    }
}
