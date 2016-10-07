<?php
/*
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

use Magento\Checkout\Model\ConfigProviderInterface;
//use Compropago\Magento2\Model\Api\Compropago\Client;
//use Compropago\Magento2\Model\Api\Compropago\Service;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;

class CompropagoConfigProvider implements ConfigProviderInterface
{
    /**
     * @var Payment
     */
    protected $method;

    /**
     * @var \Magento\Framework\Escaper
     */
    protected $escaper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checSession;
    

    /**
     * CompropagoConfigProvider constructor.
     * @param \Magento\Payment\Helper\Data $paymentHelper
     * @param \Magento\Framework\Escaper $escaper
     * @param Payment $instance
     */
    public function __construct(
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\Escaper $escaper,
        \Magento\Checkout\Model\Session $checSession,
        \Compropago\Magento2\Model\Payment $instance
    ) {
        $this->escaper = $escaper;
        $this->method = $instance;
        $this->checSession = $checSession;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                Payment::CODE => [
                    //'data' => 'scope placeholder',
                	'compropagoProviders'        => $this->getProviders(),
                	'compropagoLogos'            => $this->getShowLogos()
                ],
            ],
        ] : [];
    }

    /**
     * @return mixed
     */
    protected  function getShowLogos()
    {
    	return $this->method->getShowLogos();
    }

    /**
     * @return mixed
     */
  	protected function getProviders()
    {
        $client = new Client(
            $this->method->getPublicKey(),
            $this->method->getPrivateKey(),
            $this->method->getLiveMode()
        );

    	$compropagoProviders = $client->api->listProviders(true, $this->getGrandTotal());

    	return $compropagoProviders;
    }

    /**
     * Get session GrandTotal
     *
     * @return float
     */
    public function getGrandTotal()
    {
        return (float)$this->checSession->getQuote()->getGrandTotal();
    }
}