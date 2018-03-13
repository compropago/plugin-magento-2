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
 * @author Eduardo Aguilar <dante.aguilar41@gmail.com>
 * @category Compropago
 * @copyright qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */

namespace Compropago\Magento2\Model;

use CompropagoSdk\Tools\Validations;
use CompropagoSdk\Client;
use CompropagoSdk\Factory\Factory;

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


/**
 * Payment Object Handler
 */
class Payment extends AbstractMethod
{
    const CODE = 'compropago';
    const API_CLIENT_NAME = 'magento2';
    const API_CALL_NAME = 'PlaceOrderInfo';
    const PROVIDER_KEY_NAME = 'provider';

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
     * @param Validations $validations
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
     * @param DataObject $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(DataObject $data)
    {
        parent::assignData($data);
        
        if($data->getData(self::PROVIDER_KEY_NAME)){
            $this->getInfoInstance()->setAdditionalInformation(
                self::PROVIDER_KEY_NAME,
                $data->getData(self::PROVIDER_KEY_NAME)
            );
        } else {
            $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
            foreach ($additionalData as $key => $value) {
                if(!is_object($value)){
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
        $this->_apiClient = new Client(
            $this->getPublicKey(),
            $this->getPrivateKey(),
            $this->getLiveMode()
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
        $order = $payment->getOrder();

        try{
            $result = $this->_executePayment(
                $this->_getRequestInfo($order)
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
     * @param $order
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _getRequestInfo($order) 
    {
        $provider = $this->getInfoInstance()->getAdditionalInformation('provider');

        if (!empty($order->getCustomerFirstname()) && 
            !empty($order->getCustomerLastname())) 
        {
            $customerName = $order->getCustomerFirstname() . ' ' . $order->getCustomerLastname();
        } else {
            $customerName = $order->getShippingAddress()->getName();
        }        

        return array(
            'order_id'           => $order->getIncrementId(),
            'order_name'         => $order->getIncrementId(),
            'order_price'        => $order->getGrandTotal(),
            'customer_name'      => $customerName,
            'customer_email'     => $order->getCustomerEmail(),
            'payment_type'       => $provider,
            'currency'           => strtoupper($order->getStoreCurrencyCode()),
            'app_client_name'    => self::API_CLIENT_NAME,
            'app_client_version' => $this->_metadata->getVersion()
        );
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
        if(!isset($result['response'])){
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
        $payment->setTransactionId($response->id)
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getOfflineInfo($response) 
    {
        return array(
            "type"       => $this->_code,
            "provider"   => $this->getInfoInstance()->getAdditionalInformation("provider") ? : null,
            "ID"         => $response->id,
            "reference"  => $response->short_id,
            "expires_at" => date(
                "Y-m-d H:i:s",
                substr("{$response->expires_at}", 0, 10)
            )
        );
    }

    /**
     * Error Handler
     * @param $e
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @return string
     */
    public function getCode()
    {
        return self::CODE;
    }

    /**
     * Return ComproPago publickey
     * @return string
     */
    public function getPublicKey()
    {
        return $this->getConfigData('public_key');
    }

    /**
     * Return ComproPago privatekey
     * @return string
     */
    public function getPrivateKey()
    {
        return $this->getConfigData('private_key');
    }

    /**
     * Return ComproPago mode
     * @return bool
     */
    public function getLiveMode()
    {
        return ($this->getConfigData('live_mode') == '1')? true : false;
    }

    /**
     * Return if stores logos will be show
     * @return mixed
     */
    public function getShowLogos()
    {
        return $this->getConfigData('showlogos');
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
   
    /**
     * Warnins for config
     * @param Client $client
     * @param bool $enabled
     * @return array
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
