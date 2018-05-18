<?php
/**
 * Created by PhpStorm.
 * User: danteay
 * Date: 5/15/18
 * Time: 2:25 PM
 */

namespace Compropago\Magento2\Model;


use Magento\Payment\Model\Method\AbstractMethod;

class Config extends AbstractMethod
{
    const CODE = 'compropago_config';
    protected $_code = self::CODE;

    /**
     * Return the code
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * return public key config
     * @return string
     */
    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }

    /**
     * @return mixed
     */
    public function getPrivateKey()
    {
        return $this->getConfigData('private_key');
    }

    /**
     * @return bool
     */
    public function getLiveMode()
    {
        return $this->getConfigData('live_mode') == 1 ? true : false;
    }
}