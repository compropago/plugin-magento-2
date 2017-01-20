<?php

namespace Compropago\Magento2\Model\Api\CompropagoSdk;

use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\PlaceOrderInfo;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Request;
use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;


class Service
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param bool $auth
     * @param int $limit
     * @param string $currency
     * @return array
     */
    public function listProviders($auth = false, $limit = 0, $currency='MXN')
    {
        if ($auth) {
            $url = $this->client->deployUri.'providers/';
            $keys = ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()];
        } else {
            $url = $this->client->deployUri.'providers/true/';
            $keys = [];
        }
        if ($limit > 0) {
            $url .= '?order_total='.$limit;
        }
        if ($limit > 0 && !empty($currency) && $currency != 'MXN') {
            $url .= '&currency='.$currency;
        }
        $response = Request::get($url, $keys);
        return Factory::getInstanceOf('ListProviders', $response);
    }

    /**
     * @param $orderId
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\CpOrderInfo
     */
    public function verifyOrder( $orderId )
    {
        $response = Request::get(
            $this->client->deployUri.'charges/'.$orderId.'/',
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('CpOrderInfo', $response);
    }

    /**
     * @param PlaceOrderInfo $neworder
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\NewOrderInfo
     */
    public function placeOrder(PlaceOrderInfo $neworder)
    {
        $params = [
            'order_id' => $neworder->order_id,
            'order_name' => $neworder->order_name,
            'order_price' => $neworder->order_price,
            'customer_name' => $neworder->customer_name,
            'customer_email' => $neworder->customer_email,
            'payment_type' => $neworder->payment_type,
            'currency' => $neworder->currency,
            'image_url' => $neworder->image_url,
            'app_client_name' => $neworder->app_client_name,
            'app_client_version' => $neworder->app_client_version
        ];

        $response = Request::post(
            $this->client->deployUri.'charges/',
            $params,
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('NewOrderInfo', $response);
    }

    /**
     * @param $number
     * @param $orderId
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\SmsInfo
     */
    public function sendSmsInstructions($number,$orderId)
    {
        $params = ['customer_phone' => $number];

        $response = Request::post(
            $this->client->deployUri.'charges/'.$orderId.'/sms/',
            $params,
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('SmsInfo', $response);
    }

    /**
     * @param $url
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\Webhook
     */
    public function createWebhook($url)
    {
        $params = ['url' => $url];

        $response = Request::post(
            $this->client->deployUri.'webhooks/stores/',
            $params,
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('Webhook', $response);
    }

    /**
     * @return array
     */
    public function listWebhooks()
    {
        $response = Request::get(
            $this->client->deployUri.'webhooks/stores/',
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('ListWebhooks', $response);
    }

    /**
     * @param $webhookId
     * @param $url
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\Webhook
     */
    public function updateWebhook($webhookId, $url)
    {
        $params = ['url' => $url];

        $response = Request::put(
            $this->client->deployUri.'webhooks/stores/'.$webhookId.'/',
            $params,
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('Webhook', $response);
    }

    /**
     * @param $webhookId
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models\Webhook
     */
    public function deleteWebhook($webhookId)
    {
        $response = Request::delete(
            $this->client->deployUri.'webhooks/stores/'.$webhookId.'/',
            null,
            ['user' => $this->client->getUser(), 'pass' => $this->client->getPass()]
        );

        return Factory::getInstanceOf('Webhook', $response);
    }

    /**
     * @param bool $enabled
     * @return array
     */
    public function hookRetro($enabled = true)
    {
        $error = array(
            false,
            '',
            'yes'
        );
        if ($enabled) {
            if ( !empty($this->client->publickey) && !empty($this->client->privatekey) ) {
                try {
                    $compropagoResponse = Validations::evalAuth($this->client);
                    //eval keys
                    if ( !Validations::validateGateway($this->client) ) {
                        $error[1] = 'Invalid Keys, The Public Key and Private Key must be valid before using this module.';
                        $error[0] = true;
                    } else {
                        if ($compropagoResponse->mode_key != $compropagoResponse->livemode) {
                            $error[1] = 'Your Keys and Your ComproPago account are set to different Modes.';
                            $error[0] = true;
                        } else {
                            if ($this->client->live != $compropagoResponse->livemode) {
                                $error[1] = 'Your Store and Your ComproPago account are set to different Modes.';
                                $error[0] = true;
                            } else {
                                if ($this->client->live != $compropagoResponse->mode_key) {
                                    $error[1] = 'Your keys are for a different Mode.';
                                    $error[0] = true;
                                } else {
                                    if (!$compropagoResponse->mode_key && !$compropagoResponse->livemode) {
                                        $error[1] = 'Account is running in TEST mode, NO REAL OPERATIONS';
                                        $error[0] = true;
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $error[2] = 'no';
                    $error[1] = $e->getMessage();
                    $error[0] = true;
                }
            } else {
                $error[1] = 'The Public Key and Private Key must be set before using';
                $error[2] = 'no';
                $error[0] = true;
            }
        } else {
            $error[1] = 'The module is not enable';
            $error[2] = 'no';
            $error[0] = true;
        }
        return $error;
    }

}
