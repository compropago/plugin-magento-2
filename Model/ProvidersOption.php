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
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */
 
namespace Compropago\Magento2\Model;

use Magento\Framework\Option\ArrayInterface;

use CompropagoSdk\Client;


class ProvidersOption implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = array();
        $client = new Client('', '', false);
        $flag = false;

        foreach ($client->api->listDefaultProviders() as $provider){
            $options[] = array(
                'value' => $provider->internal_name,
                'label' => $provider->name
            );

            if ($provider->internal_name == "OXXO") { $flag = true; }
        }

        if (!$flag) {
            $options[] = [
                'value' => "OXXO",
                'label' => "Oxxo"
            ];
        }

        return $options;
    }
}