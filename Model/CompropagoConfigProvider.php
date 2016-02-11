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

namespace Compropago\Payments\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
//use Magento\Framework\App\ProductMetadataInterface;


class CompropagoConfigProvider implements ConfigProviderInterface
{
    /**
     * @var string[]
     */
    protected $methodCode = Payment::PAYMENT_METHOD_COMPROPAGO_CODE;

    /**
     * @var Checkmo
     */
    protected $method;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper $paymentHelper
     * @param Escaper $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfig()
    {
        return $this->method->isAvailable() ? [
            'payment' => [
                'compropago' => [
                    'data' => 'scope placeholder'             
                	//'compropagoProvider' => $this->getCompropagoConfig()
                ],
            ],
        ] : [];
    }

  
    protected function getCompropagoConfig()
    {
    	return $this->method->getCompropagoConfig();  
    }

    /**
     * @return string
     */
    protected function getPublicKey()
    {
        return 'placeholder';
    }
    protected function getPrivateKey()
    {
    	 return 'placeholder';
    }
}
