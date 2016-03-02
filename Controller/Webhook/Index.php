<?php
namespace Compropago\Magento2\Controller\Webhook;

class Index extends \Magento\Framework\App\Action\Action {

    /**
	 * @override
	 * @see \Magento\Framework\App\Action\Action::execute()
	 * @return \Magento\Framework\Controller\Result\Redirect
	 */
	public function execute() {
		return $this->resultRedirectFactory->create()->setPath('catalog/product/');
	}

}