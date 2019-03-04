<?php
/**
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 */

namespace Compropago\Magento2\Model;

use Magento\Framework\Option\ArrayInterface;
use CompropagoSdk\Resources\Payments\Cash as sdkCash;


class ProvidersOption implements ArrayInterface
{
    /**
     * Return array of options as value-label pairs
     * 
     * @return array Format: array(array('value' => '<value>', 'label' => '<label>'), ...)
     */
    public function toOptionArray()
    {
        try {
            $allProviders = (new sdkCash)->getDefaultProviders();
        } catch (\Exception $e) {
            $allProviders = [
                ['name' => '7Eleven', 'internal_name' => 'SEVEN_ELEVEN'],
                ['name' => 'Oxxo', 'internal_name' => 'OXXO']
            ];
        }

        $options = [];
        foreach ($allProviders as $provider) {
            $options[] = [
                'value' => $provider['internal_name'],
                'label' => $provider['name']
            ];
        }

        return $options;
    }
}
