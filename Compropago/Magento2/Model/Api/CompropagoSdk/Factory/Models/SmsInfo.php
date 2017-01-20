<?php

namespace Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Models;

class SmsInfo
{
    public $type;
    public $object;
    public $data;

    public function __construct()
    {
        $this->data = new SmsData();
    }
}
