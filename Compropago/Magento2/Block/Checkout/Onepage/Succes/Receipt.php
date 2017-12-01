<?php
/**
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

use Magento\Framework\View\Element\Template;

class Receipt extends \Magento\Checkout\Block\Onepage\Success
{
    public $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';

    /**
     * Get Payment TXN ID
     *
     * @return void
     */
    public function getVars()
    {
        $_txnId = "";

        $info = $this->getOrder()
            ->getPayment()
            ->getMethodInstance()
            ->getInfoInstance()
            ->getAdditionalInformation("offline_info");
            
        if(isset($info["ID"])){
            $_txnId = $info["ID"];
        } 
        return $_txnId;
    }
    /**
     * Get Order Object
     * 
     * @return type
     */
    public function getOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }
}
