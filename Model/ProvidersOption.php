<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */

namespace Compropago\Magento2\Model;

use Magento\Framework\Option\ArrayInterface;
use CompropagoSdk\Client;


class ProvidersOption implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        $options = array();
        $client = new Client('', '', false);

        try {
            $allProviders = $client->api->listDefaultProviders();
        } catch (\Exception $e) {
            $allProviders = [
                (Object)['name' => '7Eleven', 'internal_name' => 'SEVEN_ELEVEN'],
                (Object)['name' => 'Oxxo', 'internal_name' => 'OXXO']
            ];
        }

        foreach ($allProviders as $provider){
            $options[] = array(
                'value' => $provider->internal_name,
                'label' => $provider->name
            );
        }

        return $options;
    }
}
