<?php
/**
 * @author Eduardo Aguilar <dante.aguiar41@gmail.com>
 */

namespace Compropago\Magento2\Model\Observers;

use Compropago\Magento2\Model\Payment;

use CompropagoSdk\Tools\Validations;
use CompropagoSdk\Client;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Store\Model\StoreManagerInterface;


class WebhookRegister implements ObserverInterface
{
    private $messageManager;
    private $model;
    private $storeManager;

    /**
     * WebhookRegister constructor.
     * @param ManagerInterface $messageManager
     * @param StoreManagerInterface $storeManager
     * @param Payment $model
     */
    public function __construct(
        ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        Payment $model
    )
    {
        $this->messageManager = $messageManager;
        $this->model = $model;
        $this->storeManager = $storeManager;
    }

    /**
     * Event for the observer
     * @param Observer $observer
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
