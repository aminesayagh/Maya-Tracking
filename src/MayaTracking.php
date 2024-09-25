<?php
namespace MayaTracking;

class MayaTracking
{
    private static $instance = null;
    private $apiTokenManager;
    private $client;
    private $orderTrackingHandler;
    private $shipmentInfoRetriever;
    private $logger = null;

    private function __construct()
    {
        $this->apiTokenManager = ApiTokenManager::getInstance();
        $this->client = new Client($this->apiTokenManager);
        $this->shipmentInfoRetriever = new ShipmentInfoRetriever($this->client, Logger::getInstance());
        $this->orderTrackingHandler = new OrderTrackingHandler($this->client, Logger::getInstance());
        $this->logger = new Logger();
    }

    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new MayaTracking();
        }
        return self::$instance;
    }
    public function generateMayaTrackingUrl(string $external_order_number)
    {
        try {
            $order_info = $this->orderTrackingHandler->generateActiveAntsOrderInfo($external_order_number);
            if (!isset($order_info['data'][0]['id'])) {
                $this->logger->error("Order not found for external order number: " . $external_order_number);
                return null;
            }

            $order_id = $order_info['data'][0]['id'];
            echo $order_id;

            $shipment_info = $this->shipmentInfoRetriever->getActiveAntsShipmentInfo($order_id);
            if (!$shipment_info) {
                $this->logger->error("Shipment info not found for order ID: " . $order_id, [
                    'order_id' => $order_id
                ]);
                return null;
            }

            if (!isset($shipment_info['attributes']['tracking']['url'])) {
                $this->logger->error("Tracking URL not found in shipment info for order ID: " . $order_id);
                return null;
            }

            $tracking_url = $shipment_info['attributes']['tracking']['url'];
            $this->logger->info("Tracking URL generated", ['order_id' => $order_id, 'tracking_url' => $tracking_url]);
            return $tracking_url;
        } catch (\Exception $e) {
            $this->logger->error("Exception in generateMayaTrackingUrl", [
                'external_order_number' => $external_order_number,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }
    public function getOrGenerateMayaTrackingUrl(string $order_id)
    {
        $tracking_url = get_post_meta($order_id, MAYA_TRACKING_URL_META_KEY, true);
        if (!$tracking_url) {
            $tracking_url = $this->generateMayaTrackingUrl($order_id);
            if ($tracking_url) {
                update_post_meta($order_id, MAYA_TRACKING_URL_META_KEY, $tracking_url);
            }
        }
        return $tracking_url;
    }
    public function initHooks()
    {
        add_action('woocommerce_new_order', [$this, 'handleNewOrder'], 10, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', [DisplayHandlers::class, 'displayTrackingUrlInAdminOrderDetails'], 10, 1);
        add_action('woocommerce_order_details_after_order_table', [DisplayHandlers::class, 'displayTrackingUrlOnCustomerOrderDetails'], 10, 1);
    }
    public function handleNewOrder($order_id)
    {
        $tracking_url = $this->getOrGenerateMayaTrackingUrl($order_id);
        if ($tracking_url) {
            $this->logger->info("Tracking URL processed for order #" . $order_id . ": " . $tracking_url);
        } else {
            $this->logger->warning("Unable to process tracking URL for order #" . $order_id);
        }
    }
}