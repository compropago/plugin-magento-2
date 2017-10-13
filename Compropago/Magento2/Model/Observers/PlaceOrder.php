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


class PlaceOrder implements ObserverInterface
{
    private $messageManager;
    private $storeManager;
    private $model;
    private $metada;
    private $checSession;
    private $order;

    /**
     * PlaceOrder constructor.
     *
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param ProductMetadataInterface $metada
     * @param Session $checSession
     * @param OrderInterface $order
     * @param Payment $model
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function __construct(
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        ProductMetadataInterface $metada,
        Session $checSession,
        OrderInterface $order,
        Payment $model
    )
    {
        $this->messageManager  = $messageManager;
        $this->storeManager    = $storeManager;
        $this->model           = $model;
        $this->metada          = $metada;
        $this->order           = $order;
        $this->checSession     = $checSession;
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
                'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
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

                $this->checSession->setCompropagoId($response->id);
            } catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }
    }
}
