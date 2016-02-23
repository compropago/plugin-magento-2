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

use Compropago\Sdk\Controllers\Views;

//could extend our general block
class Receipt extends \Magento\Payment\Block\Form
{
	protected $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';
	
	public  function showReceipt()
	{
		 
		$compropagoData=   new \ArrayObject(array('id'=>'ch_d721a5de-e51c-4fdd-97a4-d09231a4f844'), \ArrayObject::STD_PROP_LIST | \ArrayObject::ARRAY_AS_PROPS);
		
		Views::loadView('iframe',$compropagoData);
		//echo 'Soy el method';
	}
}