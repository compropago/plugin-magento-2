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
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @contributor Rus0 <andonid88@gmail.com>
 *
 * @category Compropago
 * @package Compropago\Magento2\
 * @copyright qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */

namespace Compropago\Magento2\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Framework\Escaper;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DB\Transaction;
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;

use CompropagoSdk\Resources\Payments\Cash as sdkCash;
use CompropagoSdk\Resources\Payments\Spei as sdkSpei;


class Webhook
{
	const CHARGE_TYPE_PENDING  = "charge.pending";
	const CHARGE_TYPE_EXPIRED  = "charge.expired";
	const CHARGE_TYPE_SUCCESS  = "charge.success";

	const XML_PATH_VERIFIED_MESSAGE = 'verified_payment_message';

	/**
	 * @var \Compropago\Magento2\Model\Cash
	 */
	protected $_cashModel;

	/**
	 * @var \Compropago\Magento2\Model\Spei
	 */
	protected $_speiModel;

	/**
	 * @var \Compropago\Magento2\Model\Config
	 */
	protected $_config;

	/**
	 * @var array
	 */
	protected $result = [];

	/**
	 * @var sdkCash|sdkSpei
	 */
	protected $client;

	/**
	 * @var Order
	 */
	protected $_orderRepository;

	/**
	 * @var
	 */
	protected $_order;

	/**
	 * @var \Magento\Sales\Model\Order\Email\Sender\OrderCommentSender 
	 */
	protected $_orderCommandSender;

	/**
	 * @var Escaper
	 */
	protected $_escaper;

	/**
	 *  @var InvoiceService $invoiceService
	 */
	protected $_invoiceService;

	/**
	 * Core store config
	 *
	 * @var ScopeConfigInterface
	 */
	protected $_scopeConfig;

	/**
	 * @var Transaction $_transaction
	 */
	protected $_transaction;

	/**
	 * @var InvoiceSender $_invoiceSender
	 */
	protected $_invoiceSender;

	/**
	 * Webhook constructor.
	 * @param Cash $model
	 * @param Spei $spei
	 * @param Config $config
	 * @param Order $order
	 * @param OrderCommentSender $orderCommentSender
	 * @param Escaper $_escaper
	 * @param InvoiceService $_escaper
	 * @param ScopeConfigInterface $scopeConfig
	 * @param Transaction $transaction
	 * @param InvoiceSender $invoiceSender
	 * @param array $data
	 */
	public function __construct(
		Cash $model,
		Spei $spei,
		Config $config,
		Order $order,
		OrderCommentSender $orderCommentSender, 
		Escaper $_escaper,
		InvoiceService $invoiceService,
		ScopeConfigInterface $scopeConfig,
		Transaction $transaction,
		InvoiceSender $invoiceSender,
		array $data = [])
	{
		$this->_orderCommandSender = $orderCommentSender;        
		$this->_orderRepository = $order;        
		$this->_cashModel = $model;
		$this->_speiModel = $spei;
		$this->_escaper = $_escaper;
		$this->_config = $config;
		$this->_invoiceService = $invoiceService;
		$this->_scopeConfig = $scopeConfig;
		$this->_transaction = $transaction;
		$this->_invoiceSender = $invoiceSender;
	}

	/**
	 * @param $charge
	 * @return array
	 */
	public function processRequest($charge)
	{
		try
		{
			$this->_initClient();
			$order = $this->_initOrder($charge);
			$provider = $order->getPayment()->getAdditionalInformation('provider');

			if ($provider == 'SPEI')
			{
				$this->client = (new sdkSpei)->withKeys(
					$this->_config->getPublicKey(),
					$this->_config->getPrivateKey()
				);

				$type = $this->_speiValidate($charge['id']);
			}
			else
			{
				$this->client = (new sdkCash)->withKeys(
					$this->_config->getPublicKey(),
					$this->_config->getPrivateKey()
				);

				$type = $this->_validate($charge);
			}

			$this->_changeStatus($order, $type);
			$this->result = [
				'success' => true
			];
		}
		catch (\Exception $e)
		{
			$this->result = [
				'success' => false,
				'error_code' => $e->getCode(),
				'message' => $e->getMessage()
			];
		}

		return $this->result;
	}

	/**
	 * @param string $cpId
	 * @return string
	 * @throws \Exception
	 */
	protected function _speiValidate($cpId)
	{
		$response = $this->client->verifyOrder($cpId);

		if ($response['code']!= 200) {
			$message = "Can't verify order";
			throw new \Exception($message, $response['code']);
		}
		$status = $response['data']['status'];

		switch ($status) {
			case 'ACCEPTED':
				return self::CHARGE_TYPE_SUCCESS;
			case 'EXPIRED':
				return self::CHARGE_TYPE_EXPIRED;
			case 'PENDING':
				return self::CHARGE_TYPE_PENDING;
			default:
				$message = "Invalid status $status of the order";
				throw new \Exception($message, 500);
		}
	}

	/**
	 * @param $order
	 * @param $type string
	 */
	protected function _changeStatus(&$order, $type)
	{
		switch ($type)
		{
			case self::CHARGE_TYPE_PENDING:
				//Do nothing
				break;
			
			case self::CHARGE_TYPE_SUCCESS:
				if ($order->getStatus() != Order::STATE_PROCESSING) {
					$order->setState(Order::STATE_PROCESSING)
						->setStatus(Order::STATE_PROCESSING);
					$this->_processOrderComments($order, true);
				}
				break;
			
			case self::CHARGE_TYPE_EXPIRED:
				$order->setState(Order::STATE_CANCELED)
					->setStatus(Order::STATE_CANCELED)
					->save();
				break;
		}
	}

	/**
	 * Validate Request Info
	 * @param $charge
	 * @return string
	 * @throws \Exception
	 */
	protected function _validate($charge)
	{
		//$response = $this->client->verifyOrder($charge->id);
		$response = $this->client->verifyOrder($charge['id']);

		if ($response['type'] == 'error') {
			throw new \Exception(
				sprintf(__('[ComproPago Webhook] Invalid payment %s'), $charge['id']),
				\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
			);
		}

		return $response['type'];
	}

	/**
	 * Initialize API Client
	 * @throws \Exception
	 */
	protected function _initClient()
	{
		$publickey  = $this->_config->getPublicKey();
		$privatekey = $this->_config->getPrivateKey();
		$live       = $this->_config->getLiveMode();

		if (empty($publickey) || empty($privatekey)) {
			throw new \Exception(
				__("[ComproPago Webhook] Module is not configured."),
				\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
			);
		}
	}

	/**
	 * Load Order by txn ID
	 * @throws \Exception
	 */
	protected function _initOrder($charge)
	{
		//$orderId = ($charge->order_info->order_id);
		$orderId = ($charge['order_info']['order_id']);
		/** @var \Magento\Sales\Model\Order $order */
		$order = $this->_orderRepository->loadByIncrementId($orderId);        
		
		if(!$order->getId()) {
			throw new \Exception(
				sprintf(__('[ComproPago Webhook] Order not found for transaction ID: %s'), $orderId),
				 \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
			);
		}

		//Avoid fraud by matching request ID with order original transaction ID
		$referenceId = $order->getPayment()->getAdditionalInformation("ID")
			? null
            : $order->getPayment()->getAdditionalInformation("id");
        
		if($referenceId != $charge['id']) {
			throw new \Exception(
				__("[ComproPago Webhook] Reference ID does not match transaction ID"),
				\Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
			);
		}

		return $order;
	}

	/**
	 * Process Order History comments and notify customer via email
	 * @param \Magento\Sales\Model\Order $order
	 */
	protected function _processOrderComments($order)
	{
		$paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();

		$comment = $this->_escaper->escapeHtml(
			$this->_cashModel->getConfigData(self::XML_PATH_VERIFIED_MESSAGE)
		);

		// Validate if the order needs invoice
		if (
			$this->_scopeConfig->getValue(
				"payment/compropago_config/invoice_items",
				\Magento\Store\Model\ScopeInterface::SCOPE_STORE,
				$order->getStoreId()
				) && $order->canInvoice() )
		{
			try
			{
				$invoice = $this->_invoiceService->prepareInvoice($order);
				$invoice->register();
				$invoice->save();

				$transactionSave = $this->_transaction->addObject(
					$invoice
				)->addObject(
					$invoice->getOrder()
				);
				$transactionSave->save();
				$this->_invoiceSender->send($invoice);

				$historyItem = $order->addStatusHistoryComment(
					$comment,
					Order::STATE_PROCESSING);
				$historyItem->setIsCustomerNotified(true);
				$historyItem->save();
				$order->save();
			} catch (\Exception $e) {

			}
		}
		else
		{
			$historyItem = $order->addStatusHistoryComment(
				$comment,
				Order::STATE_PROCESSING
			);
			$historyItem->setIsCustomerNotified(true);
			$historyItem->save();
			$order->save();
		}
		
		try {
			$this->_orderCommandSender->send($order, true, $comment);
		} catch (\Exception $e) {

		}       
	}
}
