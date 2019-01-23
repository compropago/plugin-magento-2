<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */

namespace Compropago\Magento2\Model\Observers;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

use CompropagoSdk\Resources\Webhook as sdkWebhook;


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
        \Compropago\Magento2\Model\Config $config)
    {
        $this->messageManager	= $messageManager;
        $this->storeManager		= $storeManager;
        $this->config			= $config;
    }

    /**
     * Event for the observer
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $webhook = $this->storeManager->getStore()->getBaseUrl() . "cpwebhook/";

        try {
            $objWebhook = (new sdkWebhook)->withKeys(
                $this->config->getPublicKey(),
                $this->config->getPrivateKey()
            );

            # Create webhook on ComproPago Panel
            $objWebhook->create( $webhook );
            $this->messageManager->addSuccessMessage('Webhook ComproPago was updated.');
        } catch(\Exception $e) {
            $errors = [
                'ComproPago: Request Error [409]: ',
            ];
            $message = json_encode(str_replace($errors, '', $e->getMessage()));

            # Ignore Webhook registered
            if ( isset($message['code']) && $message['code']==409 )
            {
                $this->messageManager->addError("ComproPago: {$message}");
            }
        }
    }
}
