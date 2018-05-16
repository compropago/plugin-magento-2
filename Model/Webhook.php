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

namespace Compropago\Payments\Model;

use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;
use Magento\Framework\Escaper;

use CompropagoSdk\Client;
use CompropagoSdk\Tools\Validations;

class Webhook
{
    const CHARGE_TYPE_PENDING  = "charge.pending";
    const CHARGE_TYPE_EXPIRED  = "charge.expired";
    const CHARGE_TYPE_SUCCESS  = "charge.success";

    const XML_PATH_VERIFIED_MESSAGE = 'verified_payment_message';

    /**
     * @var \Compropago\Magento2\Model\Payment
     */
    protected $_paymentModel;

    /**
     * @var array
     */
    protected $result = array();

    /**
     * @var array
     */
    protected $client = array();

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
     * @var Config
     */
    private $config;

    /**
     * Webhook constructor.
     * @param Cash $model
     * @param Config $config
     * @param Order $order
     * @param OrderCommentSender $orderCommentSender
     * @param Escaper $_escaper
     * @param array $data
     */
    public function __construct(
        Cash $model,
        Config $config,
        Order $order,
        OrderCommentSender $orderCommentSender, 
        Escaper $_escaper,
        array $data = []
    )
    {
        $this->_orderCommandSender = $orderCommentSender;        
        $this->_orderRepository = $order;        
        $this->_paymentModel = $model;
        $this->_escaper = $_escaper;
        $this->config = $config;
    }

    /**
     * @param $charge
     * @return array
     */
    public function processRequest($charge)
    {        
        try{
            $this->_initClient();
            $this->_validate($charge);
            $order = $this->_initOrder($charge);
            $type = $charge->type;

            switch ($type) {
                case self::CHARGE_TYPE_PENDING:
                    //Do nothing
                    break;
                case self::CHARGE_TYPE_SUCCESS:
                    if($order->getStatus() != Order::STATE_PROCESSING) 
                    {
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
            $this->result = [
                'success' => true
            ];
        } catch (\Exception $e){
            $this->result = array(
                'success' => false,
                'error_code' => $e->getCode(),
                'message' => $e->getMessage()
            );
        }
        return $this->result;
    }

    /**
     * Validate Request Info
     * @param $charge
     * @throws \Exception
     */
    protected function _validate($charge)
    {
        $response = $this->client->api->verifyOrder($charge->id);
        
        if ($response->type == 'error') {
            throw new \Exception(
                sprintf(__('[Compropago Webhook] Invalid payment %s'), $charge->id),
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
            );
        }
    }

    /**
     * Initialize API Client
     * @throws \Exception
     */
    protected function _initClient()
    {
        $publickey     = $this->config->getPublicKey();
        $privatekey    = $this->config->getPrivateKey();
        $live          = $this->config->getLiveMode();

        if (empty($publickey) || empty($privatekey)) {
            throw new \Exception(
                __("[Compropago Webhook] Module is not configured."),
                \Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR
            );
        }

        $this->client = new Client($publickey, $privatekey, $live);
        Validations::validateGateway($this->client);
    }

    /**
     * Load Order by txn ID
     * @throws \Exception
     */
    protected function _initOrder($charge)
    {
        $orderId = $charge->order_info->order_id;
        $order = $this->_orderRepository->loadByIncrementId($orderId);        
        
        if(!$order->getId()){
            throw new \Exception(
                sprintf(__('[Compropago Webhook] Order not found for transaction ID: %s'), $orderId),
                 \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
            );
        }
        //Avoid fraud by matching request ID with order original transaction ID
        $referenceId = $order->getPayment()->getAdditionalInformation("ID") ?
            : $order->getPayment()->getAdditionalInformation("id");

        if($referenceId != $charge->id) {
            throw new \Exception(
                __("[Compropago Webhook] Reference ID does not match transaction ID"),
                \Magento\Framework\Webapi\Exception::HTTP_BAD_REQUEST
            );
        }

        return $order;
    }

    /**
     * Process Order History comments and notify customer via email
     * @param $order
     */
    protected function _processOrderComments($order)
    {
        $paymentMethod = $order->getPayment()->getMethodInstance()->getTitle();

        $comment = $this->_escaper->escapeHtml(
            $this->_paymentModel->getConfigData(self::XML_PATH_VERIFIED_MESSAGE)
        );

        $historyItem = $order->addStatusHistoryComment($comment, Order::STATE_PROCESSING);
        $historyItem->setIsCustomerNotified(true);

        $historyItem->save();
        $order->save();
        
        try{
            $this->_orderCommandSender->send($order, true, $comment);
        } catch (\Exception $e) {
        }       
    }
}
