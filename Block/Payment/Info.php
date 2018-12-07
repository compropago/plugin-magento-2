<?php
/**
 * MMDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDMMM
 * MDDDDDDDDDDDDDNNDDDDDDDDDDDDDDDDD=.DDDDDDDDDDDDDDDDDDDDDDDMM
 * MDDDDDDDDDDDD===8NDDDDDDDDDDDDDDD=.NDDDDDDDDDDDDDDDDDDDDDDMM
 * DDDDDDDDDN===+N====NDDDDDDDDDDDDD=.DDDDDDDDDDDDDDDDDDDDDDDDM
 * DDDDDDD$DN=8DDDDDD=~~~DDDDDDDDDND=.NDDDDDNDNDDDDDDDDDDDDDDDM
 * DDDDDDD+===NDDDDDDDDN~~N........8$........D ........DDDDDDDM
 * DDDDDDD+=D+===NDDDDDN~~N.?DDDDDDDDDDDDDD:.D .DDDDD .DDDDDDDN
 * DDDDDDD++DDDN===DDDDD~~N.?DDDDDDDDDDDDDD:.D .DDDDD .DDDDDDDD
 * DDDDDDD++DDDDD==DDDDN~~N.?DDDDDDDDDDDDDD:.D .DDDDD .DDDDDDDN
 * DDDDDDD++DDDDD==DDDDD~~N.... ...8$........D ........DDDDDDDM
 * DDDDDDD$===8DD==DD~~~~DDDDDDDDN.IDDDDDDDDDDDNDDDDDDNDDDDDDDM
 * NDDDDDDDDD===D====~NDDDDDD?DNNN.IDNODDDDDDDDN?DNNDDDDDDDDDDM
 * MDDDDDDDDDDDDD==8DDDDDDDDDDDDDN.IDDDNDDDDDDDDNDDNDDDDDDDDDMM
 * MDDDDDDDDDDDDDDDDDDDDDDDDDDDDDN.IDDDDDDDDDDDDDDDDDDDDDDDDDMM
 * MMDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDDMMM
 *
 * @author José Castañeda <jose@qbo.tech>
 * @category Compropago
 * @package Compropago\Magento2
 * @copyright   qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */

namespace Compropago\Magento2\Block\Payment;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Model\Config;


class Info extends \Magento\Payment\Block\Info
{
	protected $_disallowedFiledNames = [
		'offline_info',
		'type'
	];

	/**
	 * Constructor
	 *
	 * @param Context $context
	 * @param Config $paymentConfig
	 * @param array $data
	 */
	public function __construct(Context $context, Config $paymentConfig, array $data = [])
	{
		parent::__construct($context, $data);
		$this->paymentConfig = $paymentConfig;
	}

	/**
	 * @param null $transport
	 * @return $this|\Magento\Framework\DataObject
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	protected function _prepareSpecificInformation($transport = null) 
	{
		$transport = parent::_prepareSpecificInformation($transport);
		$data = [];
		$info = $this->getInfo();

		if (
			$this->_appState->getAreaCode() === FrontNameResolver::AREA_CODE
			&& $info->getAdditionalInformation() )
		{
			foreach ($info->getAdditionalInformation() as $field => $value)
			{
				$beautifiedFieldName = str_replace("_", " ", ucwords(trim(preg_replace('/(?<=\\w)(?=[A-Z])/', " $1", $field))));
				if($field == "ID")
				{
					$data["ID"] = $value;
				}
				else if (!in_array($field, $this->_disallowedFiledNames))
				{
					$data[__($beautifiedFieldName)->getText()] = $value;
				}
			}
		}

		return $transport->setData(array_merge($data, $transport->getData()));
	}
}
