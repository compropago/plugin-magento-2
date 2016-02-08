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

class Payment extends \Magento\Payment\Model\Method\AbstractMethod
{
    const CODE = 'compropago_payments';

    protected $_code = self::CODE;
    protected $_isOffline = true;
    protected $_isGateway                   = true;
    protected $_canCapture                  = true;
    protected $_canCapturePartial           = true;
    protected $_canRefund                   = true;
    protected $_canRefundInvoicePartial     = true;

    protected $_stripeApi = false;

    protected $_countryFactory;

    protected $_minAmount = null;
    protected $_maxAmount = null;
    protected $_supportedCurrencyCodes = array('USD','MXN');

    protected $_debugReplacePrivateDataKeys = ['number', 'exp_month', 'exp_year', 'cvc'];

    
    /**
     * Payment capturing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param float $amount
     * @return $this
     * @throws \Magento\Framework\Validator\Exception
     */
    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        //throw new \Magento\Framework\Validator\Exception(__('Inside Stripe, throwing donuts :]'));

        /** @var \Magento\Sales\Model\Order $order */
        $order = $payment->getOrder();

        /** @var \Magento\Sales\Model\Order\Address $billing */
        $billing = $order->getBillingAddress();

        try {
            $requestData = [
                'amount'        => $amount * 100,
                'currency'      => strtolower($order->getBaseCurrencyCode()),
                'description'   => sprintf('#%s, %s', $order->getIncrementId(), $order->getCustomerEmail()),
                'card'          => [
                    'number'            => $payment->getCcNumber(),
                    'exp_month'         => sprintf('%02d',$payment->getCcExpMonth()),
                    'exp_year'          => $payment->getCcExpYear(),
                    'cvc'               => $payment->getCcCid(),
                    'name'              => $billing->getName(),
                    'address_line1'     => $billing->getStreetLine(1),
                    'address_line2'     => $billing->getStreetLine(2),
                    'address_city'      => $billing->getCity(),
                    'address_zip'       => $billing->getPostcode(),
                    'address_state'     => $billing->getRegion(),
                    'address_country'   => $billing->getCountryId(),
                    // To get full localized country name, use this instead:
                    // 'address_country'   => $this->_countryFactory->create()->loadByCode($billing->getCountryId())->getName(),
                ]
            ];

            $charge = \Stripe\Charge::create($requestData);
            $payment
                ->setTransactionId($charge->id)
                ->setIsTransactionClosed(0);

        } catch (\Exception $e) {
            $this->debugData(['request' => $requestData, 'exception' => $e->getMessage()]);
            $this->_logger->error(__('Payment capturing error.'));
            throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
        }

        return $this;
    }

 

    /**
     * Determine method availability based on quote amount and config data
     *
     * @param \Magento\Quote\Api\Data\CartInterface|null $quote
     * @return bool
     */
    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if ($quote && (
            $quote->getBaseGrandTotal() < $this->_minAmount
            || ($this->_maxAmount && $quote->getBaseGrandTotal() > $this->_maxAmount))
        ) {
            return false;
        }

        if (!$this->getConfigData('public_key')) {
            return false;
        }

        return parent::isAvailable($quote);
    }

    /**
     * Availability for currency
     *
     * @param string $currencyCode
     * @return bool
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
    
    private function setCompropagoConfig()
    {
    
    	$this->compropagoConfig = array(
    			'publickey'=>$this->getConfigData('public_key'),
    			'privatekey'=>$this->getConfigData('private_key'),
    			'live'=>($this->getConfigData('live_mode')=='1')? true:false,
    			//'contained'=>'plugin; cpmg2 '.self::VERSION.';woocommerce '.$woocommerce->version.'; wordpress '.$wp_version.';'
    	);
    }
    
    public function getCompropagoConfig()
    {
    	$this->setCompropagoConfig();
    	return $this->compropagoConfig;
    }
    
    public function getProviders()
    {
    	return "Compropago Providers goes here";
    }
}