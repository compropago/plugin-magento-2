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
 * Compropago ${LIBRARI}
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

namespace Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Abs;

abstract class NewOrderInfo
{
    /**
     * @return string
     */
    protected abstract function getId();

    /**
     * @return string
     */
    protected abstract function getShortId();

    /**
     * @return string
     */
    protected abstract function getStatus();

    /**
     * @return string
     */
    protected abstract function getCreated();

    /**
     * @return string
     */
    protected abstract function getExpirationDate();

    /**
     * @return OrderInfo
     */
    protected abstract function getOrderInfo();

    /**
     * @return FeeDetails
     */
    protected abstract function getFeeDetails();

    /**
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Models\Instructions
     */
    protected abstract function getInstructions();
}
