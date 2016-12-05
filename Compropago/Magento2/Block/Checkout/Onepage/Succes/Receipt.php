<?php
/**
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

use Magento\Framework\View\Element\Template;

class Receipt extends Template
{
    public $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';
    private $checSession;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $checSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->checSession = $checSession;
    }

    public function getVars()
    {
        $compropago_id = $this->checSession->getCompropagoId();
        $this->checSession->setCompropagoId(null);

        return $compropago_id;
    }
}
