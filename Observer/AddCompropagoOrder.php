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
use Magento\Framework\Event\Observer as EventObserver;

/**
 * PayPal module observer
 */
class AddCompropagoOrder implements ObserverInterface
{
	
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $checkoutSession;

	/**
	 * Constructor
	 *
	 * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 */
	public function __construct(
			\Magento\Checkout\Model\Session $checkoutSession
			) {
				$this->checkoutSession = $checkoutSession;
	}

	/**
	 * @param EventObserver $observer
	 * @return void
	 */
	public function execute(EventObserver $observer)
	{
		/** @var \Magento\Sales\Model\Order\Payment $orderPayment */
		$orderPayment = $observer->getEvent()->getPayment();
		$order = $orderPayment->getOrder();
		//$this->checkoutSession->unsLastBillingAgreementReferenceId();
		
		$agreementCreated = false;
	
	}
}
