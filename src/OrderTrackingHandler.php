<?php
namespace MayaTracking;

class OrderTrackingHandler
{
    private $client;
    private $logger;
    private $apiTokenManager;

    public function __construct(Client $client, logger $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }
    public function generateActiveAntsOrderInfo(string $external_order_number)
    {
        if (empty($external_order_number)) {
            $this->logger->error("External order number not provided");
            return null;
        }
        $endpoint = $this->client::ENDPOINT_ORDER;
        $params = [
            'include' => 'orderItems,deliveryAddress,billingAddress,pickUpPoint',
            'filter' => [
                'externalOrderNumber' => ['in' => $external_order_number]
            ]
        ];
        return $this->client->get($endpoint, $params);
    }
}
