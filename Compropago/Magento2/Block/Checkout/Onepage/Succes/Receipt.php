<?php
/**
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */


namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

use Magento\Framework\View\Element\Template;


class Receipt extends Template
{
    /**
     * Template for render
     *
     * @var string
     */
    protected $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checSession;


    /**
     * Receipt constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Checkout\Model\Session $checSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Checkout\Model\Session $checSession,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->checSession = $checSession;
    }



    /**
     * Function in block
     *
     * @return array
     */
    public function getVars()
    {
        $compropago_id = $this->checSession->getCompropagoId();
        $this->checSession->setCompropagoId(null);

        return $compropago_id;
    }
}