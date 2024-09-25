<?php
namespace MayaTracking;

class OrderTrackingHandler
{
    private $client;
    private $apiTokenManager;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    public function generateActiveAntsOrderInfo($external_order_number)
    {
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
