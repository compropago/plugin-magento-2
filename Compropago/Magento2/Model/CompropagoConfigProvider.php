<?php
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;

error_reporting(E_ALL);
ini_set('display_errors', '1');

use Magento\Checkout\Model\ConfigProviderInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;

class CompropagoConfigProvider implements ConfigProviderInterface
{
    private $method;
    private $escaper;
    private $checSession;
    private $storeManager;

    /**
     * CompropagoConfigProvider constructor.
     *
     * @param \Magento\Framework\Escaper $escaper
     * @param \Magento\Checkout\Model\Session $checSession
     * @param Payment $instance
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Model\Session $checSession,
        \Compropago\Magento2\Model\Payment $instance,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->escaper = $escaper;
        $this->method = $instance;
        $this->checSession = $checSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Config for front framework
     *
     * @return array
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                Payment::CODE => [
                    'compropagoProviders' => $this->getProviders(),
                    'compropagoLogos'     => $this->getShowLogos()
                ],
            ],
        ] : [];
    }

    /**
     * Verify if store logos will be show
     *
     * @return mixed
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    protected function getShowLogos()
    {
        return $this->method->getShowLogos();
    }

    /**
     * Return List of providers
     *
     * @return array|int
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    protected function getProviders()
    {
        $client = new Client(
            $this->method->getPublicKey(),
            $this->method->getPrivateKey(),
            $this->method->getLiveMode()
        );

        $available = $this->method->getConfigData('active_providers');
        $available = explode(',', $available);

        $compropagoProviders = $client->api->listProviders(
            $this->getGrandTotal(),
            $this->storeManager->getStore()->getCurrentCurrencyCode()
        );

        $final = [];
        foreach ($compropagoProviders as $provider) {
            foreach ($available as $prov_av) {
                if ($prov_av == $provider->internal_name) {
                    $final[] = $provider;
                }
            }
        }

        if (empty($final)) {
            return 0;
        } else {
            return $final;
        }
    }

    /**
     * Obtain grand total for the current order
     *
     * @return float
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getGrandTotal()
    {
        return (float)$this->checSession->getQuote()->getGrandTotal();
    }
}
