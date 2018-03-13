<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;

use CompropagoSdk\Client;

use Magento\Checkout\Model\Session;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;


class CompropagoConfigProvider implements ConfigProviderInterface
{
    private $method;
    private $escaper;
    private $checSession;
    private $storeManager;

    /**
     * CompropagoConfigProvider constructor.
     * @param Escaper $escaper
     * @param Session $checSession
     * @param Payment $instance
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Escaper $escaper,
        Session $checSession,
        Payment $instance,
        StoreManagerInterface $storeManager
    ) {
        $this->escaper = $escaper;
        $this->method = $instance;
        $this->checSession = $checSession;
        $this->storeManager = $storeManager;
    }

    /**
     * Config for front framework
     * @return array
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
     * @return mixed
     */
    protected function getShowLogos()
    {
        return $this->method->getShowLogos();
    }

    /**
     * Return List of providers
     * @return array|int
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
     * @return float
     */
    public function getGrandTotal()
    {
        return (float)$this->checSession->getQuote()->getGrandTotal();
    }
}
