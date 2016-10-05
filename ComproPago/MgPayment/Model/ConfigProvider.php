<?php
/**
 * Copyright 2015 Compropago.
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
 * Compropago $Library
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */


namespace ComproPago\MgPayment\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data;
use Magento\Framework\Escaper;

class ConfigProvider implements ConfigProviderInterface
{
    protected $escaper;
    protected $instance;

    public function __construct(
        Data $helper,
        Escaper $escaper
    )
    {
        $this->escaper = $escaper;
        $this->instance = $helper->getMethodInstance(Standard::CODE);
    }


    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return $this->instance->isAvailable() ? [
            'payment' => [
                Standard::CODE => [
                    'providers' => $this->getProviders(),
                    'showlogos' => $this->getShowLogos()
                ]
            ]
        ] : [];
    }


    protected function getProviders()
    {
        die($this->instance->getConfigData('active_providers'));
        return $this->instance->getConfigData('active_providers');
    }


    protected function getShowLogos()
    {
        die($this->instance->getConfigData('showlogo'));
        return $this->instance->getConfigData('showlogo');
    }
}