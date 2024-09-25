# Maya Tracking Integration for WooCommerce

## Overview

This plugin integrates Maya Active Ants tracking information with WooCommerce orders. It allows for automatic retrieval and storage of tracking URLs, which can then be displayed in order emails and the customer's account page.

## Features

- Automatic retrieval of tracking URLs from Maya Active Ants API
- Storage of tracking URLs in WooCommerce order meta data
- Display of tracking URLs in order confirmation emails
- Admin page for manual lookup of tracking URLs
- Integration with WooCommerce order details page

## Installation

1. Upload the plugin files to the `/wp-content/plugins/maya-tracking` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the Settings->Maya Tracking screen to configure the plugin.

## Configuration

1. Go to the WordPress admin panel.
2. Navigate to Settings -> Maya Tracking.
3. Enter your Maya Active Ants API credentials:
   - API URL
   - Username
   - Password
4. Save the settings.

## Usage

### Automatic Tracking URL Retrieval

The plugin automatically attempts to retrieve the tracking URL when a new order is created in WooCommerce.

### Manual Tracking URL Lookup

1. Go to the WordPress admin panel.
2. Navigate to Maya Tracking -> Lookup Tracking.
3. Enter the order ID and click "Get Tracking URL".

### Displaying Tracking URL in Emails

The tracking URL is automatically added to the order confirmation email template. To customize this, you can override the `customer-invoice.php` email template in your theme.

### Accessing Tracking URL Programmatically

You can use the following function to get the tracking URL for an order:

```php
$tracking_url = get_maya_tracking_url($order_id);
```

## Files and Structure

- `maya-tracking.php`: Main plugin file
- `src/MayaTracking.php`: Core functionality
- `src/ApiTokenManager.php`: Handles API authentication
- `src/Client.php`: API client for making requests
- `src/OrderTrackingHandler.php`: Handles order tracking operations
- `src/ShipmentInfoRetriever.php`: Retrieves shipment information
- `src/DisplayHandlers.php`: Handles display of tracking information
- `src/Logger.php`: Logging functionality

## Hooks and Filters

- `woocommerce_new_order`: Triggered when a new order is created
- `woocommerce_admin_order_data_after_shipping_address`: Used to display tracking URL in admin order details
- `woocommerce_order_details_after_order_table`: Used to display tracking URL on customer order details page

## Troubleshooting

- If tracking URLs are not being retrieved, check your API credentials in the plugin settings.
- Ensure that the WooCommerce order status is correct before expecting a tracking URL to be available.
- Check the WordPress error log for any API-related errors.

## Support

For support, please contact [Your Support Email or URL].

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

---

**Note**: This integration assumes that tracking URLs are available shortly after order creation. There may be cases where tracking information is delayed or not available. Always handle these scenarios gracefully in your code.
