<?php
/**
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */


namespace Compropago\Magento2\Block\Checkout\Onepage\Succes;

//use Compropago\Magento2\Model\Api\Compropago\Client;
//use Compropago\Magento2\Model\Api\Compropago\Service;

use Compropago\Magento2\Model\Api\CompropagoSdk\Client;

use Compropago\Magento2\Model\Api\CompropagoSdk\Models\PlaceOrderInfo;
use Magento\Framework\View\Element\Template;


class Receipt extends Template
{
	/**
	 * @var \Magento\Checkout\Model\Session
	 */
	protected $_checkoutSession;

	/**
	 * @var \Magento\Customer\Model\Session
	 */
	protected $_customerSession;


    /**
     * Template for render
     *
     * @var string
     */
	protected $_template = 'Compropago_Magento2::checkout/onepage/success/receipt.phtml';



    /**
     * @var \Magento\Framework\Session\SessionManager
     */
    private $session;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $custSession;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checSession;
    /**
     * @var \Compropago\Magento2\Model\Payment
     */
    private $instance;
    /**
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    private $metada;


    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Session\SessionManager $session,
        \Magento\Customer\Model\Session $custSession,
        \Magento\Checkout\Model\Session $checSession,
        \Compropago\Magento2\Model\Payment $instance,
        \Magento\Framework\App\ProductMetadataInterface $metada,
        array $data = []
    )
    {
        parent::__construct($context, $data);
        $this->session = $session;
        $this->custSession = $custSession;
        $this->checSession = $checSession;
        $this->instance = $instance;
        $this->metada = $metada;
    }


    /**
     * create an order in ComproPago
     *
     * @return array
     */
    private function createOrder()
    {
        $order = $this->checSession->getLastRealOrder();
        $customer = $this->custSession->getCustomer();
        
        $dataorder = new PlaceOrderInfo(
            $order->getRealOrderId(),
            $order->getRealOrderId(),
            $order->getData('total_due'),
            $customer->getName(),
            $customer->getEmail(),
            $_COOKIE['provider'],
            null,
            'magento2',
            $this->metada->getVersion()
        );


        if(isset($_COOKIE['payment_method']) && $_COOKIE['payment_method'] == 'compropago'){
            try{
                $client = new Client(
                    $this->instance->getPublicKey(),
                    $this->instance->getPrivateKey(),
                    $this->instance->getLiveMode()
                );

                $response = $client->api->placeOrder($dataorder);

                $_COOKIE['provider'] = null;
                $_COOKIE['payment_method'] = null;

                unset($_COOKIE['provider']);
                unset($_COOKIE['payment_method']);

                return [
                    "type"  => "success",
                    "value" => base64_encode($response->getId())
                ];
            }catch(\Exception $e){
                return [
                    "type"  => "error",
                    "value" => $e->getMessage()
                ];
            }
        }else{
            return [
                "type"  => "error",
                "value" => "Metodo no reconocido"
            ];
        }
    }


    /**
     * Function in block
     *
     * @return array
     */
    public function getVars(){
        return $this->createOrder();
	}
}