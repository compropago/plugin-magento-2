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
 * @package Compropago\Magento2\
 * @copyright qbo (http://www.qbo.tech)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 * 
 * © 2017 QBO DIGITAL SOLUTIONS. 
 *
 */
namespace Compropago\Magento2\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Json\DecoderInterface;
use Compropago\Magento2\Model\Webhook;
use Compropago\Magento2\Model\Api\CompropagoSdk\Factory\Factory;
use \Psr\Log\LoggerInterface;

class Index extends Action
{
    const SUCESS_MESSAGE = "Payment successfully processed.";
    const INVALID_REQUEST_MESSAGE = "Invalid Request. Please verify request order info";
    const SERVER_ERROR_MESSAGE = "Ups. An error occurred during server request processing.";
    const SUCCESSFUL_TEST_MESSAGE = "Test succesfully passed.";
    const BAD_REQUEST_MESSAGE = '[Compropago Webhook] Please specify an Order ID or Payment Type.';
    const SUCESS_STATUS  = "success";
    const ERROR_STATUS   = "error";
    const ERROR_CODE_KEY = "error_code";
    const MESSAGE_KEY   = "message";
    const TEST_SHORT_ID  = "000000";
    const STREAM_BUFFER_NAME  = "php://input";
    /**
     * @var DecoderInterface
     */
    public $jsonDecoder;
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory 
     */
    protected $jsonResponse;
    /**
     * @var Compropago\Magento2\Model\Webhook
     */
    protected $webhookProcessor;
    /**
     * @var Psr\Log\LoggerInterface 
     */
    protected $_logger;
    /**
     * @var Magento\Framework\Filesystem\Io\File
     */
    protected $ioFile;
    /**
     * @var [type]
     */
    protected $webhook;

    /**
     * Constructor Method.
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     *
     */
    public function __construct(
        Context $context, 
        JsonFactory $jsonResultFactory,
        File $fileData,
        Webhook $webhook,
        LoggerInterface $logger,
        DecoderInterface $jsonDecoder                
    )
    {
        $this->jsonResponse = $jsonResultFactory->create();
        $this->ioFile = $fileData;
        $this->webhookProcessor = $webhook;
        $this->_logger = $logger;  
        $this->jsonDecoder = $jsonDecoder;        
        
        parent::__construct($context);
    }

    /**
     * Webhook Handler
     * 
     * @return \Magento\Framework\Controller\Result\JsonFactory
     */
    public function execute()
    {
        try {
            $event = $this->jsonDecoder->decode(
                $this->ioFile->read(self::STREAM_BUFFER_NAME)
            );
            if(!$this->_validateRequest($event)) {
                return $this->jsonResponse;
            }
            if($this->_getIsTest($event)) {
                return $this->jsonResponse;
            }
            $result = $this->webhookProcessor->processRequest($this->webhook);

            $this->_processResult($result, $event);
            
        } catch (\Exception $e) {
            /** Set HTTP error codes */
            $this->jsonResponse->setHttpResponseCode(
                \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
            $this->_logger->critical($e->getMessage());
        }
        return $this->jsonResponse; 
    }
    /**
     * Process Webhook result handler
     *
     * @return void
     */
    protected function _processResult($result, $event)
    {
        if (isset($result[self::SUCESS_STATUS]) && $result[self::SUCESS_STATUS])
        {
            $this->jsonResponse->setHttpResponseCode(
                \Magento\Framework\App\Response\Http::STATUS_CODE_200
            );
            $this->jsonResponse->setData([
                'status' => self::SUCESS_STATUS,
                'message' => __(self::SUCESS_MESSAGE),
                'short_id' =>  $event["short_id"],
                'reference' =>  $event["order_info"]["order_id"]
            ]);
            return $this->jsonResponse;
        }
       
        $this->jsonResponse->setHttpResponseCode(
            $result[self::ERROR_CODE_KEY] ? 
            : \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
        );

        $this->jsonResponse->setData([
            'status' => self::ERROR_STATUS,
            'message'   => $result[self::MESSAGE_KEY]
        ]);

        $this->_logger->critical($result[self::MESSAGE_KEY]);
    }
    /**
     * Validate Request Data
     *
     * @return void
     */
    protected function _validateRequest($event) 
    {        
        if( !is_array($event) ||
            !in_array("short_id", $event) ||
            empty($event)
        ) {
            $this->jsonResponse->setHttpResponseCode(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
            );
            $this->jsonResponse->setData([
                'status' => self::ERROR_STATUS,
                'message' => __(self::INVALID_REQUEST_MESSAGE)        
            ]);
            return false;
        }

        if(!isset($event["order_info"]["order_id"]) || !isset($event["type"])) {
            $this->jsonResponse->setHttpResponseCode(
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
            );
            $this->jsonResponse->setData([
                'status' => self::ERROR_STATUS,
                'message' => __(self::BAD_REQUEST_MESSAGE)        
            ]);
            return false;
        }

        $this->webhook = Factory::getInstanceOf('CpOrderInfo', $event);        
        
        return true;
    }

    /**
     * Testing ?
     *
     * @param array $event
     * @return void
     */
    protected function _getIsTest($event){
        // Test Case
        if ($event["short_id"] == self::TEST_SHORT_ID) {
            $this->jsonResponse->setHttpResponseCode(
                \Magento\Framework\App\Response\Http::STATUS_CODE_200
            );
            $this->jsonResponse->setData([
                'status' => self::SUCESS_STATUS,
                'message' => __(self::SUCCESSFUL_TEST_MESSAGE)      
            ]);
            return true;
        }
        return false;
    }

}
