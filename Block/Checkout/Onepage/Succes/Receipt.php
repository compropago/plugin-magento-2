<?php
/**
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;


class Receipt extends \Magento\Checkout\Block\Onepage\Success
{
    public $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';

    /**
     * Get Payment TXN ID
     * @return string
     */
    public function getVars()
    {
        $info = $this->getOrder()
            ->getPayment()
            ->getMethodInstance()
            ->getInfoInstance();

        $info = $info->getAdditionalInformation("offline_info")
            ? null
            : $info->getAdditionalInformation();
        
        $_txnId = isset($info["ID"]) ? $info["ID"] : '';

        return $_txnId;
    }

    /**
     * Get Order Object
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}
