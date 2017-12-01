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
 * @author Rolando Lucio <rolando@compropago.com>
 * @category Compropago
 * @copyright qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */
namespace Compropago\Magento2\Model;

use Compropago\Magento2\Model\Api\CompropagoSdk\Tools\Validations;
use Compropago\Magento2\Model\Api\CompropagoSdk\Client;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Sales\Model\Order\Payment\Transaction;


/**
 * Payment Object Handler
 */
class Payment extends AbstractMethod
{
    const CODE = 'compropago';
    const API_CLIENT_NAME = 'magento2';
    const API_CALL_NAME = 'PlaceOrderInfo';

    const ERROR_CODE_STORE_NOT_FOUND = 5002;
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
     *
     * @var [type]
     */
    protected $_validations;
    /**
     * Api Client
     *
     * @var [type]
     */
    protected $_apiClient;

    /**
     * @var array
     */
    public $_supportedCurrencyCodes = ['USD','MXN','GBP','EUR'];

    /**
     * Constructor Method.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param Data $paymentData
     * @param ScopeConfigInterface $scopeConfig
     * @param Logger $logger
     * @param ModuleListInterface $moduleList
     * @param TimezoneInterface $localeDate
     * @param CountryFactory $countryFactory
     * @param DataHelper $_data
     * @param CardManager $cardManager
     * @param Subscription $subscription
     * @param ProductMetadataInterface $metadata
     * @param Client $client
     * @param Validations $validations
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Sales\Model\Order\Payment\Transaction\BuilderInterface $transactionBuilder,        
        ProductMetadataInterface $metadata,
        Validations $validations,
        array $data = array()
    ) {
        $this->_metadata = $metadata;
        $this->_validations = $validations;
        $this->transactionBuilder = $transactionBuilder;

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
    *
    * @param \Magento\Framework\DataObject|mixed $data
    * @return $this
    * @throws LocalizedException
    */
    public function assignData(\Magento\Framework\DataObject $data) 
    {
        parent::assignData($data);
        
        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);

        foreach ($additionalData as $key => $value) {
            if(!is_object($value)){
                $this->getInfoInstance()->setAdditionalInformation($key, $value);
            }
        }

        return $this;
    }
    /**
     * Initialize Compropago API Client
     *
     * @return void
     */
    protected function _initialize() 
    {
        $this->_apiClient = new Client(
            $this->getPublicKey(),
            $this->getPrivateKey(),
            $this->getLiveMode()
        );
    }
    /**
     * Payment Authorization Processing
     *
     * @param \Magento\Payment\Model\InfoInterface $payment
     * @param [type] $amount
     * @return void
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->_initialize();
        $order = $payment->getOrder();

        try{
            $result = $this->_executePayment(
                $this->_getRequestInfo($order)
            );

            if(isset($result['success'])) {
                $this->_addTransactionInfo(
                    $payment, 
                    $result
                );
                $response = $result['response'];
                $order->setExtOrderId($response->id)->save();
            } 
             
        } catch(\Exception $e) {
            $this->_processErrors($e);
        }
        return $this;
    }
    /**
     * Process Payment Data to Compropago API
     *
     * @return void
     */
    protected function _executePayment($_orderInfo)
    {
        $result = array();
 
        try {
            $_orderRequest = Factory::getInstanceOf(
                self::API_CALL_NAME, 
                $_orderInfo
            );
            $response = $this->_apiClient->api->placeOrder($_orderRequest);

            if(property_exists($response, "id")) {
                $result = array(
                    'success' => true,
                    'response' => $response
                );
            }

        } catch(\Exception $e){
            $this->_processErrors($e);
        }

        return $result;
    }
    /**
     * Build Order Request Info
     *
     * @param [type] $order
     * @return void
     */
    protected function _getRequestInfo($order) 
    {
        $provider = $this->getInfoInstance()->getAdditionalInformation('provider');

        return array(
            'order_id'           => $order->getIncrementId(),
            'order_name'         => $order->getIncrementId(),
            'order_price'        => $order->getGrandTotal(),
            'customer_name'      => $order->getCustomerName(),
            'customer_email'     => $order->getCustomerEmail(),
            'payment_type'       => $provider,
            'currency'           => strtoupper($order->getStoreCurrencyCode()),
            'app_client_name'    => self::API_CLIENT_NAME,
            'app_client_version' => $this->_metadata->getVersion()
        );
    }

    /**
     * Add transaction info to payment object
     *
     * @param $order
     * @param $response
     *
     */
    public function _addTransactionInfo(&$payment, $result)
    {
        $response = $result['response'];

        $info = $this->getInfoInstance()
            ->getAdditionalInformation();

        $offlineInfo = $this->getOfflineInfo($info, $response);

        $this->getInfoInstance()->setAdditionalInformation([
            "offline_info" => $offlineInfo
        ]);
        /**
         * Set TXN ID
         */
        $payment->setTransactionId(
            $response->id
        )->setIsTransactionClosed(
            0
        )->setSkipOrderProcessing(
            true
        );
        /**
         * Add Transaction Details
         */
        $payment->setTransactionAdditionalInfo(
            \Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS, 
            $offlineInfo
        );
    }
    /**
     * Get Payment Info After Charge
     *
     * @param [array] $info
     * @param [object] $response
     * @return void
     */
    protected function getOfflineInfo($info, $response) 
    {
        return array(
            "Type"       => $this->_code,
            "Provider"   => isset($info["provider"]) ? $info["provider"] : null,
            "ID"         => $response->id,
            "Reference"  => $response->short_id,
            "expires_at" => date(
                "Y-m-d H:i:s",
                substr("{$response->expires_at}", 0, 10)
            )
        );
    }

    /**
     * Error Handler
     * 
     * @param type $e
     * @throws \Magento\Framework\Validator\Exception
     */
    protected function _processErrors($e)
    {
        $this->_logger->error(__('[Compropago]: ' . $e->getMessage()));
        
        if($e->getCode() === self::ERROR_CODE_STORE_NOT_FOUND){
            throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
        } 
        throw new \Magento\Framework\Validator\Exception(__('Payment capturing error.'));
    }

    /**
     * Return payment method code
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * Return ComproPago publickey
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gnail.com>
     */
    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }

    /**
     * Return ComproPago privatekey
     *
     * @return string
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getPrivateKey()
    {
        return $this->getConfigData('private_key');
    }

    /**
     * Return ComproPago mode
     *
     * @return bool
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getLiveMode()
    {
        return ($this->getConfigData('live_mode') == '1')? true : false;
    }

    /**
     * Return if stores logos will be show
     *
     * @return mixed
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function getShowLogos()
    {
        return $this->getConfigData('showlogos');
    }

    /**
     * Validate if store currency is supported by ComproPago
     *
     * @param string $currencyCode
     * @return bool
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function canUseForCurrency($currencyCode)
    {
        if (!in_array($currencyCode, $this->_supportedCurrencyCodes)) {
            return false;
        }
        return true;
    }
   
    /**
     * Warnins for config
     *
     * @param Client $client
     * @param bool $enabled
     * @return array
     *
     * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
     */
    public function hookRetro(Client $client, $enabled = true)
    {
        $error = [
            false,
            '',
            'yes'
        ];

        if ($enabled) {
            if ( !empty($client->publickey) && !empty($client->privatekey) ) {
                try {
                    $compropagoResponse = Validations::evalAuth($client);
                    if ( !Validations::validateGateway($client) ) {
                        $error[1] = 'Invalid Keys, The Public Key and Private Key must be valid before using this module.';
                        $error[0] = true;
                    } else {
                        if ($compropagoResponse->mode_key != $compropagoResponse->livemode) {
                            $error[1] = 'Your Keys and Your ComproPago account are set to different Modes.';
                            $error[0] = true;
                        } else {
                            if ($client->live != $compropagoResponse->livemode) {
                                $error[1] = 'Your Store and Your ComproPago account are set to different Modes.';
                                $error[0] = true;
                            } else {
                                if ($client->live != $compropagoResponse->mode_key) {
                                    $error[1] = 'Your keys are for a different Mode.';
                                    $error[0] = true;
                                } else {
                                    if (!$compropagoResponse->mode_key && !$compropagoResponse->livemode) {
                                        $error[1] = 'Account is running in TEST mode, NO REAL OPERATIONS';
                                        $error[0] = true;
                                    }
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $error[2] = 'no';
                    $error[1] = $e->getMessage();
                    $error[0] = true;
                }
            } else {
                $error[1] = 'The Public Key and Private Key must be set before using';
                $error[2] = 'no';
                $error[0] = true;
            }
        } else {
            $error[1] = 'The module is not enable';
            $error[2] = 'no';
            $error[0] = true;
        }
        return $error;
    }
}
