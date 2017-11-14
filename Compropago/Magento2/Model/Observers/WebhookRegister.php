<?php

namespace Compropago\Magento2\Model\Observers;

use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;


class WebhookRegister implements ObserverInterface
{
    private $messageManager;
    private $model;
    private $storeManager;

    /**
     * WebhookRegister constructor.
     *
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Compropago\Magento2\Model\Payment $model
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Compropago\Magento2\Model\Payment $model
    )
    {
        $this->messageManager = $messageManager;
        $this->model = $model;
        $this->storeManager = $storeManager;
    }

    /**
     * Event for the observer
     *
     * @param Observer $observer
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function execute(Observer $observer)
    {
        $webhook = $this->storeManager->getStore()->getBaseUrl() . "cpwebhook/";

        $client = new Client(
            $this->model->getPublicKey(),
            $this->model->getPrivateKey(),
            $this->model->getLiveMode()
        );

        try {
            Validations::validateGateway($client);
            $client->api->createWebhook($webhook);
        } catch(\Exception $e) {
            if ($e->getMessage() != 'Error: conflict.urls.create') {
                $this->messageManager->addError("ComproPago: {$e->getMessage()}");
            } 
        }

        $response = $this->model->hookRetro(
            $client,
            $this->model->getConfigData('active') == 1
        );

        if ($response[0]) {
            $this->messageManager->addNotice("ComproPago: {$response[1]}");
        }
    }
}
