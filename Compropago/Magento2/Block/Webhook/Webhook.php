<?php

namespace Compropago\Magento2\Block\Webhook;

use Magento\Sales\Model\Order;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\View\Element\Template;
use Compropago\Magento2\Model\Payment;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;

class Webhook extends Template
{
    private $model;
    private $orderManager;
    private $resource;

    /**
     * Webhook constructor.
     *
     * @param Template\Context $context
     * @param Payment $model
     * @param Order $orderManager
     * @param ResourceConnection $resourceConnection
     * @param array $data
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function __construct(
        Template\Context $context,
        Payment $model,
        Order $orderManager,
        ResourceConnection $resourceConnection,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->model        = $model;
        $this->orderManager = $orderManager;
        $this->resource     = $resourceConnection;
    }

    public function procesWebhook($json = null)
    {
        if (empty($json)) {
            return json_encode([
                'status' => 'error',
                'message' => 'invalid request',
                'short_id' => null,
                'reference' => null
            ]);
        }

        if (!$resp_webhook = Factory::getInstanceOf('CpOrderInfo', $json)) {
            return json_encode([
                'status' => 'error',
                'message' => 'invalid request',
                'short_id' => null,
                'reference' => null
            ]);
        }

        $publickey     = $this->model->getPublicKey();
        $privatekey    = $this->model->getPrivateKey();
        $live          = $this->model->getLiveMode();

        if ( empty($publickey) || empty($privatekey) ) {
            return json_encode([
                'status' => 'error',
                'message' => 'ivalid compropago keys',
                'short_id' => null,
                'reference' => null
            ]);
        }

        try{
            $client = new Client($publickey, $privatekey, $live);
            Validations::validateGateway($client);
        }catch (\Exception $e) {
            return $e->getMessage();
        }

        if ( $resp_webhook->short_id == '000000' ) {
            return json_encode([
                'status' => 'success',
                'message' => 'OK - test',
                'short_id' => $resp_webhook->short_id,
                'reference' => $resp_webhook->order_info->order_id
            ]);
        }

        try{
            $response = $client->api->verifyOrder( $resp_webhook->id );

            if ($response->type == 'error') {
                return json_encode([
                    'status' => 'error',
                    'message' => 'verify order failed',
                    'short_id' => $resp_webhook->short_id,
                    'reference' => $resp_webhook->order_info->order_id
                ]);
            }

            $table_name      = $this->resource->getTableName('sales_order');
            $table_grid_name = $this->resource->getTableName('sales_order_grid');
            $connection      = $this->resource->getConnection();

            $this->orderManager->loadByIncrementId( $response->order_info->order_id );

            $entity_id = $this->orderManager->getEntityId();

            switch ( $response->type ) {
                case 'charge.pending':
                    return json_encode([
                        'status' => 'success',
                        'message' => 'OK - charge.pending',
                        'short_id' => $response->short_id,
                        'reference' => $response->order_info->order_id
                    ]);
                    break;
                case 'charge.success':
                    $this->orderManager->setState('processing');
                    $status = 'processing';
                    break;
                case 'charge.expired':
                    $this->orderManager->setState('canceled');
                    $status = 'canceled';
                    break;
                default:
                    return json_encode([
                        'status' => 'error',
                        'message' => 'invalid request type - ' . $response->type,
                        'short_id' => $response->short_id,
                        'reference' => $response->order_info->order_id
                    ]);
            }

            $query = "UPDATE $table_name SET state = '$status', status = '$status' WHERE entity_id = $entity_id";
            $connection->query($query);

            $query = "UPDATE $table_grid_name SET status = '$status' WHERE entity_id = $entity_id";
            $connection->query($query);

            return json_encode([
                'status' => 'success',
                'message' => 'OK - ' . $response->type,
                'short_id' => $response->short_id,
                'reference' => $response->order_info->order_id
            ]);
        }catch (\Exception $e){
            return $e->getMessage();
        }
    }
}
