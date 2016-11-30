<?php

namespace Compropago\Magento2\Model\Observers;


use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Models\PlaceOrderInfo;

class PlaceOrder implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Compropago\Magento2\Model\Payment
     */
    private $model;
    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $sessionManager;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metada;
    /**
     * @var \Magento\Checkout\Model\Session
     */
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

        if($order->getPayment()->getMethod() == 'compropago'){
            $dataorder = new PlaceOrderInfo(
                $order->getRealOrderId(),
                $order->getRealOrderId(),
                $order->getData('total_due'),
                $order->getCustomerFirstname() . " " . $order->getCustomerLastname(),
                $order->getCustomerEmail(),
                $_COOKIE['provider'],
                null,
                'magento2',
                $this->metada->getVersion()
            );

            try{
                $client = new Client(
                    $this->model->getPublicKey(),
                    $this->model->getPrivateKey(),
                    $this->model->getLiveMode()
                );

                $response = $client->api->placeOrder($dataorder);

                $this->checSession->setCompropagoId($response->getId());
            }catch(\Exception $e){
                $this->messageManager->addError($e->getMessage());
            }
        }
    }
}