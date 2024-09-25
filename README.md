# Maya Tracking Integration

This plugin integrates Maya tracking information with WooCommerce orders.

## Usage

To get or generate a tracking URL for an order in your custom code:

```php
$mayaTracking = MayaTracking\MayaTracking::getInstance();
$tracking_url = $mayaTracking->getOrGenerateTrackingUrl($order_id);
```

This can be used in your email templates or any other custom functionality.