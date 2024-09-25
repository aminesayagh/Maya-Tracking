<?php
namespace MayaTracking;

class DisplayHandlers {
    public static function displayTrackingUrlInAdminOrderDetails($order) {
        $tracking_url = $order->get_meta(MAYA_TRACKING_URL_META_KEY);
        if ($tracking_url) {
            echo '<p><strong>Maya Tracking URL:</strong> <a href="' . esc_url($tracking_url) . '" target="_blank">' . esc_html($tracking_url) . '</a></p>';
        }
    }

    public static function displayTrackingUrlOnCustomerOrderDetails($order) {
        $tracking_url = $order->get_meta(MAYA_TRACKING_URL_META_KEY);
        if ($tracking_url) {
            echo '<p><strong>Tracking URL:</strong> <a href="' . esc_url($tracking_url) . '" target="_blank">Track your package</a></p>';
        }
    }
}
