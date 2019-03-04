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
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @category Compropago
 * @copyright qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *
 * © 2017 QBO DIGITAL SOLUTIONS.
 *
 */

namespace Compropago\Magento2\Model;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\DataObject;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Payment\Helper\Data;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Payment\Model\Method\Logger;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface;

use CompropagoSdk\Resources\Payments\Spei as sdkSpei;


/**
 * Payment Object Handler
 */
class Spei extends AbstractMethod
{
	const CODE				= 'compropago_spei';
	const PROVIDER_KEY_NAME	= 'provider';

	const ERROR_CODE_STORE_NOT_FOUND = 5002;

	/**
	 * @var string
	 */
	protected $_infoBlockType  = 'Compropago\Magento2\Block\Payment\Info';

	/**
	 * Mode
	 *
	 * @var boolean
	 */
	public $_isOffline  = false;

	/**
	 * Gateway
	 *
	 * @var boolean
	 */
	public $_isGateway  = true;

	/**
	 * Can Capture Transaction
	 *
	 * @var boolean
	 */
	public $_canCapture = true;

	/**
	 * Payment Method Code
	 *
	 * @var [type]
	 */
	protected $_code = self::CODE;

	/**
	 *
	 * @var [type]
	 */
	protected $_metadata;

	/**
	 * API Client for cash gateway
	 * @var [sdkCash]
	 */
	protected $_apiClient;

	/**
	 * General configuration of ComproPago
	 * @var \Compropago\Magento2\Model\Config
	 */
	protected $config;

	/**
	 * @var array
	 */
	public $_supportedCurrencyCodes = ['EUR', 'GBP', 'MXN', 'USD'];

	/**
	 * Payment constructor.
	 * @param Context $context
	 * @param Registry $registry
	 * @param ExtensionAttributesFactory $extensionFactory
	 * @param AttributeValueFactory $customAttributeFactory
	 * @param Data $paymentData
	 * @param ScopeConfigInterface $scopeConfig
	 * @param Logger $logger
	 * @param BuilderInterface $transactionBuilder
	 * @param ProductMetadataInterface $metadata
	 * @param \Compropago\Magento2\Model\Config $config
	 * @param array $data
	 */
	public function __construct(
		Context $context,
		Registry $registry,
		ExtensionAttributesFactory $extensionFactory,
		AttributeValueFactory $customAttributeFactory,
		Data $paymentData,
		ScopeConfigInterface $scopeConfig,
		Logger $logger,
		BuilderInterface $transactionBuilder,
		ProductMetadataInterface $metadata,
		\Compropago\Magento2\Model\Config $config,
		array $data = array()
	) {
		$this->_metadata = $metadata;
		$this->transactionBuilder = $transactionBuilder;
		$this->config = $config;

		parent::__construct(
			$context,
			$registry,
			$extensionFactory,
			$customAttributeFactory,
			$paymentData,
			$scopeConfig,
			$logger,
			null,
			null,
			$data
		);
	}

	/**
	 * Assign corresponding data to payment info object
	 * @param DataObject $data
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function assignData(DataObject $data)
	{
		parent::assignData($data);

		if ($data->getData(self::PROVIDER_KEY_NAME)) {
			$this->getInfoInstance()->setAdditionalInformation(
				self::PROVIDER_KEY_NAME,
				$data->getData(self::PROVIDER_KEY_NAME)
			);
		} else {
			$additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
			foreach ($additionalData as $key => $value) {
				if (!is_object($value)) {
					$this->getInfoInstance()->setAdditionalInformation($key, $value);
				}
			}
        }
        
		return $this;
	}

	/**
	 * Initialize Compropago API Client
	 * @return void
	 */
	protected function _initialize()
	{
		$this->_apiClient = (new sdkSpei)->withKeys(
			$this->config->getPublicKey(),
			$this->config->getPrivateKey()
		);
	}

	/**
	 * Payment Authorization Processing
	 * @param InfoInterface $payment
	 * @param float $amount
	 * @return $this
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Validator\Exception
	 */
	public function authorize(InfoInterface $payment, $amount)
	{
		$this->_initialize();

		try{
			$result = $this->_executePayment(
				$this->_getRequestInfo( $payment->getOrder() )
			);

			if (isset($result['success'])) {
				$this->_addTransactionInfo(
					$payment,
					$result
				);
			}

		} catch(\Exception $e) {
			$this->_processErrors($e);
		}

		return $this;
	}

	/**
	 * Process Payment Data to Compropago API
	 * @param $_orderInfo
	 * @return array
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Validator\Exception
	 */
	protected function _executePayment($_orderInfo)
	{
		$result = [];

		try {
			$response = $this->_apiClient->createOrder($_orderInfo);
			
			if (isset($response['data']['id'])) {
				$result = [
					'success'	=> true,
					'response'	=> $response['data']
				];
			} else {
                throw new \Magento\Framework\Validator\Exception(__('Old API version, Send mail to soporte@compropago.com'));
			}
		} catch(\Exception $e) {
			$this->_processErrors($e);
		}

		return $result;
	}

	/**
	 * Build Order Request Info
	 * @param $order
	 * @return array
	 */
	protected function _getRequestInfo($order)
	{
		if (!empty($order->getCustomerFirstname()) && !empty($order->getCustomerLastname())) {
			$customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
		} else {
			$customerName = $order->getShippingAddress()->getName();
		}

		$customerTelephone = $order->getShippingAddress()->getTelephone();
		if (!$customerTelephone) $customerTelephone = "";

		return [
			"product"		=> [
				"id"		=> "{$order->getIncrementId()}",
				"price"		=> floatval($order->getGrandTotal()),
				"name"		=> "{$order->getIncrementId()}",
				"url"		=> "",
				"currency"	=> strtoupper($order->getStoreCurrencyCode())
			],
			"customer"		=> [
				"name"		=> $customerName,
				"email"		=> $order->getCustomerEmail(),
				"phone"		=> $customerTelephone,
			],
			"payment"		=>  [
				"type"		=> "SPEI"
			]
		];
	}

	/**
	 * Add transaction info to payment object
	 * @param $payment
	 * @param $result
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Validator\Exception
	 */
	public function _addTransactionInfo(&$payment, $result)
	{
		if (!isset($result['response'])) {
			throw new \Magento\Framework\Validator\Exception(__('An error occurred.'));
		}

		$response = $result['response'];
		$offlineInfo = $this->getOfflineInfo($response);

		$this->getInfoInstance()->setAdditionalInformation([
			"offline_info" => $offlineInfo
		]);

		foreach($offlineInfo as $key => $value) {
			$this->getInfoInstance()->setAdditionalInformation(
				$key, $value
			);
		}

		/**
		 * Set TXN ID
		 */
		$payment->setTransactionId($response['id'])
			->setIsTransactionClosed(0)
			->setSkipOrderProcessing(true);

		/**
		 * Add Transaction Details
		 */
		$payment->setTransactionAdditionalInfo(Transaction::RAW_DETAILS, $offlineInfo);
	}

	/**
	 * Get Payment Info After Charge
	 * @param $response
	 * @return array
	 */
	protected function getOfflineInfo($response)
	{
		return [
			"type"			=> $this->_code,
			"provider"		=> 'SPEI',
			"ID"			=> $response['id'],
			"reference"		=> $response['shortId'],
			"expires_at"	=> date("Y-m-d H:i:s", substr("{$response['expiresAt']}", 0, 10))
		];
	}

	/**
	 * Error Handler
	 * @param $e
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Validator\Exception
	 */
	protected function _processErrors($e)
	{
		$message = $e->getMessage();
		$this->_logger->error(__('[ComproPago]: ' . $message));

		if($e->getCode() === self::ERROR_CODE_STORE_NOT_FOUND) {
			throw new \Magento\Framework\Exception\LocalizedException(__($message));
		}

		throw new \Magento\Framework\Validator\Exception(__($message));
		//throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
	}


	/**
	 * Return payment method code
	 * @return string
	 */
	public function getCode()
	{
		return self::CODE;
	}

	/**
	 * Validate if store currency is supported by ComproPago
	 * @param string $currencyCode
	 * @return bool
	 */
	public function canUseForCurrency($currencyCode)
	{
		if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
			return false;
		}
		return true;
	}
}
