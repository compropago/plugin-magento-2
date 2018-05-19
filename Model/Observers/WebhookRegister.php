<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */

namespace Compropago\Magento2\Model\Observers;

use CompropagoSdk\Tools\Validations;
use CompropagoSdk\Client;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;


class WebhookRegister implements ObserverInterface
{
    private $messageManager;
    private $storeManager;
    private $config;

    /**
     * WebhookRegister constructor.
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Compropago\Magento2\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Compropago\Magento2\Model\Config $config
    ) {
        $this->messageManager = $messageManager;
        $this->storeManager = $storeManager;
        $this->config = $config;
    }

    /**
     * Event for the observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $webhook = $this->storeManager->getStore()->getBaseUrl() . "cpwebhook/";

        $client = new Client(
            $this->config->getPublicKey(),
            $this->config->getPrivateKey(),
            $this->config->getLiveMode()
        );

        $errors = [
            'Request error: 409',
            'Error: conflict.urls.create'
        ];

        try {
            Validations::validateGateway($client);
            $client->api->createWebhook($webhook);

            $this->messageManager->addSuccessMessage('Webhook ComproPago was updated.');
        } catch(\Exception $e) {
            if (!in_array($e->getMessage(), $errors)) {
                $this->messageManager->addError("ComproPago: {$e->getMessage()}");
            }
        }
    }
}