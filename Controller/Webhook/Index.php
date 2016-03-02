<?php


namespace Compropago\Magento2\Controller\Webhook;

use \Compropago\Sdk\Controllers\Views;

class Index extends \Magento\Framework\App\Action\Action {

    /** @var \Magento\Framework\Controller\Result\JsonFactory */
    protected $resultFactory;

    /**
     * Index Controller constructor
     * @param \Magento\Framework\App\Action\Context       $context
     * @param \Magento\Framework\Controller\ResultFactory $resultFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $resultFactory
    ) {
        parent::__construct($context);
        $this->resultFactory = $resultFactory;
    }


    /**
     * Set webhook output
     * @override
     * @return \Magento\Framewrok\Controller\Result\Raw
     */
    public function execute()
    {
    	$message = "";
    	$factory = \Magento\Framework\App\ObjectManager::getInstance();
    	$compropagoModel = $factory->create('\Compropago\Magento2\Model\Payment');

    	$request = @file_get_contents('php://input');

    	try{
			if(!$jsonObj = json_decode($request)){
				throw new \Exception('Tipo de Request no Valido');
			}

			if ( empty( $compropagoModel->getPublicKey() ) || empty( $compropagoModel->getPrivateKey() ) ){
				throw new \Exception("Se requieren las llaves de compropago");
			}

			$compropagoConfig= array(
				'publickey'		=> $compropagoModel->getPublicKey(),
				'privatekey'	=> $compropagoModel->getPrivateKey(),
				'live'			=> $compropagoModel->getLiveMode(),
				'contained'		=> 'plugin; cpm2 1.9.7-dev ; magento '.'2.0.2'.'; webhook;'
			);

			$compropagoClient = new Compropago\Sdk\Client($compropagoConfig);
			$compropagoService = new Compropago\Sdk\Service($compropagoClient);


			// Valid Keys?
			if(!$compropagoResponse = $compropagoService->evalAuth()){
				throw new \Exception("ComproPago Error: Llaves no validas");
			}

			// Store Mode Vs ComproPago Mode, Keys vs Mode & combinations
			if(! Compropago\Sdk\Utils\Store::validateGateway($compropagoClient)){
				throw new \Exception("ComproPago Error: La tienda no se encuentra en un modo de ejecución valido");
			}

			//api normalization
			if($jsonObj->api_version=='1.0'){
				$jsonObj->id=$jsonObj->data->object->id;
				$jsonObj->short_id=$jsonObj->data->object->short_id;
			}


			//webhook Test?
			if($jsonObj->id=="ch_00000-000-0000-000000" || $jsonObj->short_id =="000000"){
				throw new \Exception("Probando el WebHook?, <b>Ruta correcta.</b>");
			}

			/**
			 * Hasta esta parte queda completo
			 */

			$response = $compropagoService->verifyOrder($jsonObj->id);


    	}catch(\Exception $e){
    		$message = $e->getMessage();
    	}


        /** @var \Magento\Framework\Controller\Result\Raw $result */
        $result = $this->resultFactory->create('raw');
        $result->setContents($message);

        return $result;
    }

}