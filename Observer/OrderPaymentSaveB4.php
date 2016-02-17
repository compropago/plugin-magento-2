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

namespace Compropago\Magento2\Observer;

use Magento\Framework\Event\ObserverInterface;

use Compropago\Magento2\Model\Payment;

class OrderPaymentSaveB4 implements ObserverInterface
{
    /**
     * Sets current instructions for bank transfer account
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Payment $payment */
        $payment = $observer->getEvent()->getPayment();
        $instructionMethods = [
            Banktransfer::PAYMENT_METHOD_BANKTRANSFER_CODE,
            Cashondelivery::PAYMENT_METHOD_CASHONDELIVERY_CODE
        ];
        if (in_array($payment->getMethod(), $instructionMethods)) {
            $payment->setAdditionalInformation(
                'instructions',
                $payment->getMethodInstance()->getInstructions()
            );
        } elseif ($payment->getMethod() === Checkmo::PAYMENT_METHOD_CHECKMO_CODE) {
            $payment->setAdditionalInformation(
                'payable_to',
                $payment->getMethodInstance()->getPayableTo()
            );
            $payment->setAdditionalInformation(
                'mailing_address',
                $payment->getMethodInstance()->getMailingAddress()
            );
        }
    }
}