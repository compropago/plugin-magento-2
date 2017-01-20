<?php

namespace Compropago\Magento2\Model\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;

class PlaceOrder implements ObserverInterface
{
    private $messageManager;
    private $storeManager;
    private $model;
    private $sessionManager;
    private $metada;
    private $checSession;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\Generic $sessionManager,
        \Magento\Framework\App\ProductMetadataInterface $metada,
        \Magento\Checkout\Model\Session $checSession,
        \Compropago\Magento2\Model\Payment $model
    )
    {
        $this->messageManager  = $messageManager;
        $this->storeManager    = $storeManager;
        $this->model           = $model;
        $this->sessionManager  = $sessionManager;
        $this->metada          = $metada;
        $this->checSession     = $checSession;
    }

    /**
     * @param Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $this->checSession->getLastRealOrder();

        if ($order->getPayment()->getMethod() == 'compropago') {

              $order_info = [
                'order_id' => $order->getRealOrderId(),
                'order_name' => $order->getRealOrderId(),
                'order_price' => $order->getData('total_due'),
                'customer_name' => $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
                'customer_email' => $order->getCustomerEmail(),
                'payment_type' => $_COOKIE['provider'],
                'currency' => $this->storeManager->getStore()->getCurrentCurrencyCode(),
                'image_url' => null,
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
