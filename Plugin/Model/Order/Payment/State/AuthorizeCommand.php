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
 * @author QBO Team <info@qbo.tech>
 * @copyright   qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */

namespace Compropago\Magento2\Plugin\Model\Order\Payment\State;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order\Payment\State\CommandInterface as BaseCommandInterface;
use Magento\Store\Model\ScopeInterface;

use Compropago\Magento2\Model\Cash;
use Compropago\Magento2\Model\Spei;
use Compropago\Magento2\Model\Config\Source\Order\Status;


class AuthorizeCommand
{
	const STATUS_PENDING = "pending";

	/**
	 * @var ScopeConfigInterface
	 */
	protected $_scopeConfig;

	/**
	 * @var array
	 */
	protected $_allowedMethods = array(
		Cash::CODE,
		Spei::CODE,
	);

	/**
	 * AuthorizeCommand constructor.
	 * @param ScopeConfigInterface $scopeConfig
	 */
	public function __construct(ScopeConfigInterface $scopeConfig)
	{
		$this->_scopeConfig = $scopeConfig;
	}

	/**
	 * Set pending order status on order place
	 * see https://github.com/magento/magento2/issues/5860
	 *
	 * @todo Refactor this when another option becomes available
	 *
	 * @param BaseCommandInterface $subject
	 * @param \Closure $proceed
	 * @param OrderPaymentInterface $payment
	 * @param $amount
	 * @param OrderInterface $order
	 * @return mixed
	 */
	public function aroundExecute(
		BaseCommandInterface $subject,
		\Closure $proceed,
		OrderPaymentInterface $payment,
		$amount,
		OrderInterface $order)
	{
		$result = $proceed($payment, $amount, $order);
		if (in_array($payment->getMethod(), $this->_allowedMethods))
		{
			$orderStatus = $this->_scopeConfig->getValue(
				"payment/{$payment->getMethod()}/order_status",
				ScopeInterface::SCOPE_STORE
			);

			if ($orderStatus) {
				$order->setStatus($orderStatus);
				$order->setState( ($orderStatus == self::STATUS_PENDING)
					? (Status::STATE_PENDING)
					: ($order->getConfig()->getStateDefaultStatus($orderStatus))
				);
			}
		}

		return $result;
	}
}
