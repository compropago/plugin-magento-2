<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model\Ui;

use Compropago\Magento2\Model\Cash;
use CompropagoSdk\Resources\Payments\Cash as sdkCash;

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
        $this->escaper      = $escaper;
        $this->method       = $instance;
        $this->checSession  = $checSession;
        $this->storeManager = $storeManager;
        $this->assetRepo    = $assetRepo;
        $this->config       = $config;
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
        try {
            $client = (new sdkCash)->withKeys(
                $this->config->getPublicKey(),
                $this->config->getPrivateKey()
            );
            $compropagoProviders = $client->getProviders();
        } catch (\Exception $e) {
            $compropagoProviders = [
                ['name' => '7Eleven', 'internal_name' => 'SEVEN_ELEVEN'],
                ['name' => 'Oxxo', 'internal_name' => 'OXXO']
            ];
        }

        # Available providers
        $available = explode(',', $this->method->getConfigData('active_providers'));
        if ( empty($available[0]) ) {
            foreach ($compropagoProviders as $provider) {
                array_push($available, $provider);
            }
        }

        $final = [];
        foreach ($compropagoProviders as $provider)
        {
            foreach ($available as $prov_av) {
                if ($prov_av == $provider['internal_name']) {
                    $final[] = $provider;
                }
            }
        }
        
        return empty($final) ? 0 : $final;
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
