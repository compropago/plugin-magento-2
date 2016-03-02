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


namespace Compropago\Magento2\Block\Webhook;


//use \Compropago\Sdk\Client;
//use \Compropago\Sdk\Service;


class Webhook extends \Magento\Framework\View\Element\Template
//class Webhook extends \Magento\Payment\Block\Form
//class Webhook implements Magento\Framework\View\Element\BlockInterface
{
	protected $_template = 'Compropago_Magento2::webhook/webhook.phtml';

	/**
	 * @override Magento\Framework\View\Element\BlockInterface
	 */
	/*public function toHtml(){
		echo "Hola mundo";
	}*/


	public function webhook()
	{

	}
}