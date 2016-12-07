<?php
/**
 * Copyright 2016 Compropago.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * @author Rolando Lucio <rolando@compropago.com>
 */

namespace Compropago\Magento2\Model;

/**
 * Class Payment
 *
 * @method \Magento\Quote\Api\Data\PaymentMethodExtensionInterface getExtensionAttributes()
 */
class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'compropago';
    
    public $_isOffline  = true;
    public $_isGateway  = true;
    public $_canCapture = true;
    
    public $_supportedCurrencyCodes = ['USD','MXN'];

    public function getCode()
    {
        return self::CODE;
    }

    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }

    public function getPrivateKey()
    {
        return $this->getConfigData('private_key');
    }

    public function getLiveMode()
    {
        return ($this->getConfigData('live_mode')=='1')? true : false;
    }
   
    public function getShowLogos()
    {
        return $this->getConfigData('showlogos');
    }

    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
}
