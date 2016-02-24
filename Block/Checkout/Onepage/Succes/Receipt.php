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


namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

//use Compropago\Sdk\Controllers\Views;


//class Receipt extends \Magento\Checkout\Block\Onepage\Success
//class Receipt extends \Magento\Payment\Block\Info
//class Receipt extends \Magento\Framework\View\Element\Template
class Receipt extends \Magento\Payment\Block\Form
{
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;

	/**
	 * @var \Magento\Paypal\Model\Billing\AgreementFactory
	 */
	//protected $_agreementFactory;
	
	protected $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';

	/**
	 * @param \Magento\Framework\View\Element\Template\Context $context
	 * @param \Magento\Checkout\Model\Session $checkoutSession
	 * @param \Magento\Customer\Model\Session $customerSession
	 * @param \Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory
	 * @param array $data
	 */
	/*public function __construct(
			//\Magento\Framework\View\Element\Template\Context $context,
			\Magento\Checkout\Model\Session $checkoutSession,
			\Magento\Customer\Model\Session $customerSession
			//\Magento\Paypal\Model\Billing\AgreementFactory $agreementFactory,
			//array $data = []
			) {
				parent::__construct();
				$this->_checkoutSession = $checkoutSession;
				$this->_customerSession = $customerSession;
				//$this->_agreementFactory = $agreementFactory;
				//parent::__construct( $data);
	}*/

	public function getVars(){

	}
	
	public  function showReceipt()
	{
	
	}
}