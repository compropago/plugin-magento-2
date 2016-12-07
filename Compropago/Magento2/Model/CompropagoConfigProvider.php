<?php
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;

class CompropagoConfigProvider implements ConfigProviderInterface
{
    private $method;
    private $escaper;
    private $checSession;

    public function __construct(
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Model\Session $checSession,
        \Compropago\Magento2\Model\Payment $instance
    ) {
        $this->escaper = $escaper;
        $this->method = $instance;
        $this->checSession = $checSession;
    }

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

    protected function getShowLogos()
    {
        return $this->method->getShowLogos();
    }

    protected function getProviders()
    {
        $client = new Client(
            $this->method->getPublicKey(),
            $this->method->getPrivateKey(),
            $this->method->getLiveMode()
        );

        $available = $this->method->getConfigData('active_providers');
        $available = explode(',', $available);

        $compropagoProviders = $client->api->listProviders(true, $this->getGrandTotal());

        $final = [];
        foreach ($compropagoProviders as $provider) {
            foreach ($available as $prov_av) {
                if ($prov_av == $provider->internal_name) {
                    $final[] = $provider;
                }
            }
        }

        return $final;
    }

    public function getGrandTotal()
    {
        return (float)$this->checSession->getQuote()->getGrandTotal();
    }
}
