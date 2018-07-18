<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model\Ui;

use Compropago\Magento2\Model\Cash;
use CompropagoSdk\Client;

use Magento\Checkout\Model\Session;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\Asset\Repository as AssetsRepository;


class ConfigProvider implements ConfigProviderInterface
{
    private $method;
    private $escaper;
    private $checSession;
    private $storeManager;
    private $assetRepo;
    /**
     * @var \Compropago\Magento2\Model\Config
     */
    private $config;

    /**
     * CompropagoConfigProvider constructor.
     * @param Escaper $escaper
     * @param Session $checSession
     * @param Cash $instance
     * @param \Compropago\Magento2\Model\Config $config
     * @param StoreManagerInterface $storeManager
     * @param AssetsRepository $assetRepo
     */
    public function __construct(
        Escaper $escaper,
        Session $checSession,
        Cash $instance,
        \Compropago\Magento2\Model\Config $config,
        StoreManagerInterface $storeManager,
        AssetsRepository $assetRepo
    ) {
        $this->escaper = $escaper;
        $this->method = $instance;
        $this->checSession = $checSession;
        $this->storeManager = $storeManager;
        $this->assetRepo = $assetRepo;
        $this->config = $config;
    }

    /**
     * Config for front framework
     * @return array
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                Cash::CODE => [
                    'providers' => $this->getProviders()
                ],
            ],
        ] : [];
    }

    /**
     * Return List of providers
     * @return array|int
     */
    protected function getProviders()
    {
        $client = new Client(
            $this->config->getPublicKey(),
            $this->config->getPrivateKey(),
            $this->config->getLiveMode()
        );

        $available = $this->method->getConfigData('active_providers');
        $available = explode(',', $available);

        try {
            $compropagoProviders = $client->api->listProviders(
                $this->getGrandTotal(),
                $this->storeManager->getStore()->getCurrentCurrencyCode()
            );
        } catch (\Exception $e) {
            $compropagoProviders = [
                (Object)['name' => '7Eleven', 'internal_name' => 'SEVEN_ELEVEN'],
                (Object)['name' => 'Oxxo', 'internal_name' => 'OXXO']
            ];
        }

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
