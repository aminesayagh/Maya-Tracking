<?php
namespace MayaTracking;

class MayaTracking {
    private static $instance = null;
    private $apiTokenManager;
    private $orderTrackingHandler;
    private $shipmentInfoRetriever;

    private function __construct() {
        $this->apiTokenManager = ApiTokenManager::getInstance();
        $this->orderTrackingHandler = new OrderTrackingHandler($this->apiTokenManager);
        $this->shipmentInfoRetriever = new ShipmentInfoRetriever($this->apiTokenManager);
    }

    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new MayaTracking();
        }
        return self::$instance;
    }

    public function getOrGenerateTrackingUrl($order_id) {
        return $this->orderTrackingHandler->getOrGenerateTrackingUrl($order_id);
    }

    public function initHooks() {
        add_action('woocommerce_new_order', [$this->orderTrackingHandler, 'handleNewOrderTracking'], 10, 1);
        add_action('woocommerce_admin_order_data_after_shipping_address', [DisplayHandlers::class, 'displayTrackingUrlInAdminOrderDetails'], 10, 1);
        add_action('woocommerce_order_details_after_order_table', [DisplayHandlers::class, 'displayTrackingUrlOnCustomerOrderDetails'], 10, 1);
    }
}