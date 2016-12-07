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

namespace Compropago\Magento2\Model\Api\CompropagoSdk\Factory;

use Compropago\Magento2\Model\Api\CompropagoSdk\Models\EvalAuthInfo;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Json\Serialize;
use Compropago\Magento2\Model\Api\CompropagoSdk\Models\Webhook;
use Compropago\Magento2\Model\Api\CompropagoSdk\Models\Provider;

class Factory
{
    private static function verifyVersion($source)
    {
        $obj = json_decode($source);
        return isset($obj->api_version) ? $obj->api_version : null;
    }

    public static function evalAuthInfo($source)
    {
        $res = new EvalAuthInfo();
        $obj = json_decode($source);

        $res->type = $obj->type;
        $res->livemode = $obj->livemode;
        $res->mode_key = $obj->mode_key;
        $res->message = $obj->message;
        $res->code = $obj->code;

        return $res;
    }

    public static function arrayProviders($source)
    {
        $jsonObj= json_decode($source);

        if (isset($jsonObj->type) && $jsonObj->type == "error") {
            throw new \Exception($jsonObj->message, $jsonObj->code);
        }

        $res = array();

        foreach ($jsonObj as $val) {
            $provider = new Provider();

            $provider->name = $val->name;
            $provider->store_image = $val->store_image;
            $provider->is_active = $val->is_active;
            $provider->image_small = $val->image_small;
            $provider->image_medium = $val->image_medium;
            $provider->image_large = $val->image_large;
            $provider->internal_name = $val->internal_name;
            $provider->rank = $val->rank;
            $provider->transaction_limit = isset($val->transaction_limit) ? $val->transaction_limit : null;

            $res[] = $provider;
        }

        return $res;
    }

    public static function cpOrderInfo($source)
    {
        switch ( self::verifyVersion($source) ) {
            case '1.1':
                return Serialize::cpOrderInfo11($source);
            default:
                return Serialize::cpOrderInfo10($source);
        }
    }

    public static function newOrderInfo($source)
    {
        switch ( self::verifyVersion($source) ) {
            case '1.1':
                return Serialize::newOrderInfo11($source);
            default:
                return Serialize::newOrderInfo10($source);
        }
    }

    public static function smsInfo($source)
    {
        if ( array_key_exists('payment', json_decode($source)) ) {
            return Serialize::smsInfo10($source);
        } else {
            return Serialize::smsInfo11($source);
        }
    }

    public static function webhook($source)
    {
        $json = json_decode($source);
        
        if (isset($json->type) && $json->type == 'error') {
            throw new \Exception($json->message, $json->code);
        }
        
        $object = new Webhook();
        
        $object->id = $json->id;
        $object->url = isset($json->url) ? $json->url : null ;
        $object->mode = isset($json->mode) ? $json->mode : null ;
        $object->status = isset($json->status) ? $json->status : null ;
        
        return $object;
    }

    public static function listWebhooks($source)
    {
        $final = array();

        foreach (json_decode($source,true) as $value) {
            $final[] = self::webhook(json_encode($value));
        }

        return $final;
    }
}
