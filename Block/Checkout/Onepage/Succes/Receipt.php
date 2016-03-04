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
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 * @author Rolando Lucio <rolando@compropago.com> 
 */


namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

//use Compropago\Sdk\Controllers\Views;
use \Compropago\Sdk\Client;
use \Compropago\Sdk\Service;

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


	public function getVars(){

		$factory = \Magento\Framework\App\ObjectManager::getInstance();

		$orderSession = $factory->create('\Magento\Checkout\Model\Session');
		$customerSession = $factory->create('Magento\Customer\Model\Session');
        $compropagoModel = $factory->create('\Compropago\Magento2\Model\Payment');

		$order = $orderSession->getLastRealOrder();
		$customer = $customerSession->getCustomer();


        $dataorder = array(
            'order_id'           => $order->getRealOrderId(),             // string para identificar la orden
            'order_price'        => $order->getData('total_due'),         // float con el monto de la operación
            'order_name'         => $order->getRealOrderId(),             // nombre para la orden
            'customer_name'      => $customer->getName(),                 // nombre del cliente
            'customer_email'     => $customer->getEmail(),                // email del cliente
            'payment_type'       => $_COOKIE['provider'],
        	'app_client_name'    =>	'magento2',
        	'app_client_version' => '2.0.2'
        );

        $compropagoConfig = array(
            'publickey'         => $compropagoModel->getPublicKey(),
            'privatekey'        => $compropagoModel->getPrivateKey(),
            'live'              => $compropagoModel->getLiveMode()
        );


        if(isset($_COOKIE['payment_method']) && $_COOKIE['payment_method'] == 'compropago'){


            try{
                $compropagoClient = new Client($compropagoConfig);
                $compropagoService = new Service($compropagoClient);

                $response = $compropagoService->placeOrder($dataorder);

                $_COOKIE['provider'] = null;
                $_COOKIE['payment_method'] = null;

                unset($_COOKIE['provider']);
                unset($_COOKIE['payment_method']);

                return base64_encode(json_encode($response));
            }catch(\Exception $e){
                return $e->getMessage();
            }
        }else{  
            return "Entra null";
        }




	}
}