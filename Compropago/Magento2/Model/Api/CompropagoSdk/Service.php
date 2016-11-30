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
 * Compropago php-sdk
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */


namespace Compropago\Magento2\Model\Api\CompropagoSdk;

use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use Compropago\Magento2\Model\Api\CompropagoSdk\Models\PlaceOrderInfo;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Rest;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;

/**
 * Class Service Provee de los servicios necesarios para el manejo de la API de ComproPago
 * @package CompropagoSdk
 */
class Service
{
    private $client;
    private $headers;
    
    /**
     * Service constructor.
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
        $this->headers = array(
            'useragent: '.$client->getContained()
        );
    }

    /**
     * @param bool $auth
     * @param int $limit
     * @param bool $fetch
     * @return array
     * @throws \Exception
     */
    public function listProviders($auth = false, $limit = 0, $fetch = false)
    {
        if($auth){
            $uri = $this->client->getUri()."providers";
            $keys = $this->client->getFullAuth();
        }else{
            $uri = $this->client->getUri()."providers/true";
            $keys = "";
        }

        if(is_numeric($limit) && $limit > 0){
            $uri .= "?order_total=$limit";
        }

        if(is_bool($fetch) && $fetch){
            if(is_numeric($limit) && $limit > 0){
                $uri .= "&fetch=true";
            }else{
                $uri .= "?fetch=true";
            }
        }

        $response = Rest::get($uri,$keys,$this->headers);
        $providers = Factory::arrayProviders($response);

        return $providers;
    }

    /**
     * @param $orderId
     * @return \CompropagoSdk\Factory\Abs\CpOrderInfo
     * @throws \Exception
     */
    public function verifyOrder( $orderId )
    {
        Validations::validateGateway($this->client);

        $response = Rest::get($this->client->getUri()."charges/$orderId/",$this->client->getAuth(),$this->headers);
        $obj = Factory::cpOrderInfo($response);

        return $obj;
    }

    /**
     * @param PlaceOrderInfo $neworder
     * @return \CompropagoSdk\Factory\Abs\NewOrderInfo
     * @throws \Exception
     */
    public function placeOrder(PlaceOrderInfo $neworder)
    {
        Validations::validateGateway($this->client);

        $params = "order_id=".$neworder->order_id.
            "&order_name=".$neworder->order_name.
            "&order_price=".$neworder->order_price.
            "&customer_name=".$neworder->customer_name.
            "&customer_email=".$neworder->customer_email.
            "&payment_type=".$neworder->payment_type.
            "&image_url=".$neworder->image_url.
            "&app_client_name=".$neworder->app_client_name.
            "&app_client_version=".$neworder->app_client_version;

        $response = Rest::post($this->client->getUri()."charges/",$this->client->getAuth(),$params,$this->headers);

        $obj = Factory::newOrderInfo($response);

        return $obj;
    }

    /**
     * @param $number
     * @param $orderId
     * @return \CompropagoSdk\Factory\Abs\SmsInfo
     * @throws \Exception
     */
    public function sendSmsInstructions($number,$orderId)
    {
        Validations::validateGateway($this->client);

        $params = "customer_phone=".$number;

        $response= Rest::post($this->client->getUri()."charges/".$orderId."/sms/",$this->client->getAuth(),$params,
            $this->headers);
        $obj = Factory::smsInfo($response);

        return $obj;
    }

    /**
     * @param $url
     * @return Models\Webhook
     * @throws \Exception
     */
    public function createWebhook($url)
    {
        Validations::validateGateway($this->client);

        $params = "url=".$url;

        $response = Rest::post($this->client->getUri()."webhooks/stores/", $this->client->getFullAuth(), $params,
            $this->headers);
        $obj = Factory::webhook($response);

        return $obj;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function listWebhooks()
    {
        Validations::validateGateway($this->client);

        $response = Rest::get($this->client->getUri()."webhooks/stores/",$this->client->getFullAuth(),
            $this->headers);
        $obj = Factory::listWebhooks($response);

        return $obj;
    }

    /**
     * @param $webhookId
     * @param $url
     * @return Models\Webhook
     * @throws \Exception
     */
    public function updateWebhook($webhookId, $url)
    {
        Validations::validateGateway($this->client);

        $params = "url=".$url;

        $response = Rest::put($this->client->getUri()."webhooks/stores/$webhookId/", $this->client->getFullAuth(),
            $params, $this->headers);

        $obj = Factory::webhook($response);

        return $obj;
    }

    /**
     * @param $webhookId
     * @return Models\Webhook
     * @throws \Exception
     */
    public function deleteWebhook($webhookId)
    {
        Validations::validateGateway($this->client);

        $response=Rest::delete($this->client->getUri()."webhooks/stores/$webhookId/", $this->client->getFullAuth(),
            null,$this->headers);

        $obj = Factory::webhook($response);

        return $obj;
    }

    /**
     * Despliegue de retroalimentacion en el panel de administración
     *
     * @param bool   $enabled
     * @return array
     */
    public function hookRetro($enabled = true)
    {
        $error = array(
            false,
            '',
            'yes'
        );
        if($enabled){
            if(!empty($this->client->getPublickey()) && !empty($this->client->getPrivatekey()) ){
                try{
                    $compropagoResponse = Validations::evalAuth($this->client);
                    //eval keys
                    if(!Validations::validateGateway($this->client)){
                        $error[1] = 'Invalid Keys, The Public Key and Private Key must be valid before using this module.';
                        $error[0] = true;
                    }else{
                        if($compropagoResponse->mode_key != $compropagoResponse->livemode){
                            $error[1] = 'Your Keys and Your ComproPago account are set to different Modes.';
                            $error[0] = true;
                        }else{
                            if($this->client->getMode() != $compropagoResponse->livemode){
                                $error[1] = 'Your Store and Your ComproPago account are set to different Modes.';
                                $error[0] = true;
                            }else{
                                if($this->client->getMode() != $compropagoResponse->mode_key){
                                    $error[1] = 'Your keys are for a different Mode.';
                                    $error[0] = true;
                                }else{
                                    if(!$compropagoResponse->mode_key && !$compropagoResponse->livemode){
                                        $error[1] = 'Account is running in TEST mode, NO REAL OPERATIONS';
                                        $error[0] = true;
                                    }
                                }
                            }
                        }
                    }
                }catch (\Exception $e) {
                    $error[2] = 'no';
                    $error[1] = $e->getMessage();
                    $error[0] = true;
                }
            }else{
                $error[1] = 'The Public Key and Private Key must be set before using';
                $error[2] = 'no';
                $error[0] = true;
            }
        }else{
            $error[1] = 'The module is not enable';
            $error[2] = 'no';
            $error[0] = true;
        }
        return $error;
    }
}