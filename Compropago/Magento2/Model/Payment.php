<?php
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;

use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Magento\Payment\Model\Method\AbstractMethod;


class Payment extends AbstractMethod
{
    const CODE = 'compropago';

    public $_isOffline  = true;
    public $_isGateway  = true;
    public $_canCapture = true;

    public $_supportedCurrencyCodes = ['USD','MXN','GBP','EUR'];

    /**
     * Return payment method code
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * Return ComproPago publickey
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gnail.com>
     */
    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }

    /**
     * Return ComproPago privatekey
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getPrivateKey()
    {
        return $this->getConfigData('private_key');
    }

    /**
     * Return ComproPago mode
     *
     * @return bool
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getLiveMode()
    {
        return ($this->getConfigData('live_mode')=='1')? true : false;
    }

    /**
     * Return if stores logos will be show
     *
     * @return mixed
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getShowLogos()
    {
        return $this->getConfigData('showlogos');
    }

    /**
     * Validate if store currency is supported by ComproPago
     *
     * @param string $currencyCode
     * @return bool
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }

    /**
     * Warnins for config
     *
     * @param Client $client
     * @param bool $enabled
     * @return array
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function hookRetro(Client $client, $enabled = true)
    {
        $error = [
            false,
            '',
            'yes'
        ];

        if ($enabled) {
            if ( !empty($client->publickey) && !empty($client->privatekey) ) {
                try {
                    $compropagoResponse = Validations::evalAuth($client);
                    if ( !Validations::validateGateway($client) ) {
                        $error[1] = 'Invalid Keys, The Public Key and Private Key must be valid before using this module.';
                        $error[0] = true;
                    } else {
                        if ($compropagoResponse->mode_key != $compropagoResponse->livemode) {
                            $error[1] = 'Your Keys and Your ComproPago account are set to different Modes.';
                            $error[0] = true;
                        } else {
                            if ($client->live != $compropagoResponse->livemode) {
                                $error[1] = 'Your Store and Your ComproPago account are set to different Modes.';
                                $error[0] = true;
                            } else {
                                if ($client->live != $compropagoResponse->mode_key) {
                                    $error[1] = 'Your keys are for a different Mode.';
                                    $error[0] = true;
                                } else {
                                    if (!$compropagoResponse->mode_key && !$compropagoResponse->livemode) {
                                        $error[1] = 'Account is running in TEST mode, NO REAL OPERATIONS';
                                        $error[0] = true;
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $error[2] = 'no';
                    $error[1] = $e->getMessage();
                    $error[0] = true;
                }
            } else {
                $error[1] = 'The Public Key and Private Key must be set before using';
                $error[2] = 'no';
                $error[0] = true;
            }
        } else {
            $error[1] = 'The module is not enable';
            $error[2] = 'no';
            $error[0] = true;
        }
        return $error;
    }
}
