<?php

namespace Compropago\Magento2\Model\Observers;

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Compropago\Magento2\Model\Payment;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ResourceConnection;


class PlaceOrder implements ObserverInterface
{
    private $messageManager;
    private $storeManager;
    private $model;
    private $metada;
    private $checSession;
    private $order;
    private $connection;

    /**
     * PlaceOrder constructor.
     *
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $metadata
     * @param Session $checkSession
     * @param OrderInterface $order
     * @param Payment $model
     * @param ResourceConnection $connection
     * 
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function __construct(
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $metadata,
        Session $checkSession,
        OrderInterface $order,
        Payment $model,
        ResourceConnection $connection
    ) {
        $this->messageManager  = $messageManager;
        $this->storeManager    = $storeManager;
        $this->model           = $model;
        $this->metada          = $metadata;
        $this->order           = $order;
        $this->checSession     = $checkSession;
        $this->connection      = $connection;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $orders = $observer->getEvent()->getOrderIds();
        $order = $this->order->load($orders[0]);

        if (!empty($order->getCustomerFirstname()) && !empty($order->getCustomerLastname())) {
            $customer_name = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        } else {
            $customer_name = $order->getShippingAddress()->getName();
        }

        if ($order->getPayment()->getMethod() == 'compropago') {

            $order_info = [
                'order_id' => $order->getIncrementId(),
                'order_name' => $order->getIncrementId(),
                'order_price' => $order->getGrandTotal(),
                'customer_name' => $customer_name,
                'customer_email' => $order->getCustomerEmail(),
                'payment_type' => $_COOKIE['provider'],
                'currency' => strtoupper($order->getStoreCurrencyCode()),
                'app_client_name' => 'magento2',
                'app_client_version' => $this->metada->getVersion()
            ];

            $dataorder = Factory::getInstanceOf('PlaceOrderInfo',$order_info);


            try {
                $client = new Client(
                    $this->model->getPublicKey(),
                    $this->model->getPrivateKey(),
                    $this->model->getLiveMode()
                );

                $response =$client->api->placeOrder($dataorder);

                $this->addTransactionId($order, $response);

                $this->checSession->setCompropagoId($response->id);
            } catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }
    }

    /**
     * Add transaction info for the order
     *
     * @param $order
     * @param $cpOrder
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function addTransactionId($order, $cpOrder)
    {
        $order_payment       = $this->connection->getTableName('sales_order_payment');
        $payment_transaction = $this->connection->getTableName('sales_payment_transaction');
        $connection          = $this->connection->getConnection();

        $payment = $order->getPayment();
        $method = $payment->getMethodInstance();
        $methodTitle = $method->getTitle();

        $addInfo = [
            "method_title" => $methodTitle,
            "offline_info" => [
                "type" => $this->model->getCode(),
                "data" => [
                    "reference" => $cpOrder->short_id,
                    "expires_at" => date(
                        "Y-m-d H:i:s",
                        substr("{$cpOrder->expires_at}", 0, 10)
                    )
                ]
            ]
        ];

        $json = json_encode($addInfo);
        $entity_id = $order->getEntityId();

        $query1 = "UPDATE {$order_payment} SET last_trans_id = '{$cpOrder->id}',
            additional_information = '{$json}' where entity_id = {$entity_id}";

        $query2 = "INSERT INTO {$payment_transaction} (order_id, payment_id, txn_id, txn_type) 
            VALUES ({$entity_id}, {$entity_id}, '{$cpOrder->id}', 'authorization')";

        $connection->query($query1);
        $connection->query($query2);
    }
}
