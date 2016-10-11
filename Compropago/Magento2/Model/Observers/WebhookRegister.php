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


namespace Compropago\Magento2\Model\Observers;


use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;


class WebhookRegister implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var \Compropago\Magento2\Model\Payment
     */
    private $model;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Compropago\Magento2\Model\Payment $model
    )
    {
        $this->messageManager = $messageManager;
        $this->model = $model;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $webhook = $this->storeManager->getStore()->getBaseUrl() . "cpwebhook/";

        try{
            $client = new Client(
                $this->model->getPublicKey(),
                $this->model->getPrivateKey(),
                $this->model->getLiveMode()
            );

            Validations::validateGateway($client);

            $client->api->createWebhook($webhook);

            $response = $client->api->hookRetro(
                $this->model->getConfigData('active') == 1
            );

            if($response[0]){
                $this->messageManager->addNotice("ComproPago: {$response[1]}");
            }
        }catch(\Exception $e){
            $this->messageManager->addError("ComproPago: {$e->getMessage()}");
        }
    }
}