<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */

namespace Compropago\Payments\Model\Observers;

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
     * @param \Compropago\Payments\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Compropago\Payments\Model\Config $config
    )
    {
        $this->messageManager = $messageManager;
        $this->config = $config;
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
            $this->config->getPublicKey(),
            $this->config->getPrivateKey(),
            $this->config->getLiveMode()
        );

        try {
            Validations::validateGateway($client);
            $client->api->createWebhook($webhook);

            $this->messageManager->addSuccessMessage('Webhook ComproPago was updated.');
        } catch(\Exception $e) {
            if ($e->getMessage() != 'Error: conflict.urls.create') {
                $this->messageManager->addError("ComproPago: {$e->getMessage()}");
            }
        }
    }
}