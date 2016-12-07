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

namespace Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Abs;

abstract class CpOrderInfo
{
    /**
     * @return string
     */
    protected abstract function getId();

    /**
     * @return string
     */
    protected abstract function getType();

    /**
     * @return string
     */
    protected abstract function getCreated();

    /**
     * @return bool
     */
    protected abstract function getPaid();

    /**
     * @return string
     */
    protected abstract function getAmount();

    /**
     * @return string
     */
    protected abstract function getCurrency();

    /**
     * @return bool
     */
    protected abstract function getRefunded();

    /**
     * @return string
     */
    protected abstract function getFee();

    /**
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Abs\FeeDetails
     */
    protected abstract function getFeeDetails();

    /**
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Abs\OrderInfo
     */
    protected abstract function getOrderInfo();

    /**
     * @return \Compropago\Magento2\Model\Api\CompropagoSdk\Models\Customer
     */
    protected abstract function getCustomer();

    /**
     * @return string
     */
    protected abstract function getCaptured();

    /**
     * @return string
     */
    protected abstract function getFailureMessage();

    /**
     * @return string
     */
    protected abstract function getFailureCode();

    /**
     * @return double
     */
    protected abstract function getAmountRefunded();

    /**
     * @return string
     */
    protected abstract function getDescription();

    /**
     * @return string
     */
    protected abstract function getDispute();
}
