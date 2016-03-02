<?php
namespace Compropago\Magento2\Controller\Webhook;

class Index extends \Magento\Framework\App\Action\Action {

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }

    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create('raw');
        /** You may introduce your own constants for this custom REST API */
        $result->setContents('hola mundo');
        return $result;
    }

}