<?php
namespace MayaTracking;

class ShipmentInfoRetriever
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    public function getActiveAntsShipmentInfo($order_id)
    {

        $endpoint = Client::ENDPOINT_SHIPMENT;
        $params = [
            'include' => 'shipmentItems',
            'filter' => [
                'orderId' => ['in' => $order_id]
            ],
            'page' => [
                'size' => 100,
                'cursor' => 0
            ]
        ];

        do {
            $result = $this->client->get($endpoint, $params);

            if (isset($result['data'])) {
                foreach ($result['data'] as $shipment) {
                    if ($shipment['relationships']['order']['data']['id'] == $order_id) {
                        return $shipment;
                    }
                }
            }

            $params['page']['cursor'] = 0;
            if (isset($result['links']['next'])) {
                $next_url = $result['links']['next']['href'];
                parse_str(parse_url($next_url, PHP_URL_QUERY), $query_params);
                $params['page']['cursor'] = $query_params['page']['cursor'] ?? 0;
            }

        } while ($params['page']['cursor'] !== 0);

        return null;
    }
}