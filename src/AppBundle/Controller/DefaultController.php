<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Artesanus\ConektaBundle\ConektaInterface;

class DefaultController extends Controller {

    /**
     * @Route("/", name="homepage")
     * @Template("AppBundle:home:index.html.twig")
     */
    public function indexAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Category');
        $result = $repository->findAll();
        $repositoryp = $this->getDoctrine()->getRepository('AppBundle:Product');
        $pr = $repositoryp->findBy(array('featured' => 1, 'locale' => $request->getLocale()));
        return array('cat' => $result, 'prod' => $pr);
    }

    /**
     * @Route("/return/{product}", name="return_url")
     */
    public function returnAction(Request $request, $product) {

        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        $item = $repository->findOneBy(array('id' => $product));

        $redirect = $this->generateUrl('homepage');

        if (!$item->getFeatured()) {
            $redirect = "https://www.geopositioningservices.com";
        }

        return $this->redirect($redirect);
    }

    /**
     * @Route("/checkout", name="producto")
     * @Method({"POST"})
     * @Template("AppBundle:producto:index.html.twig")
     */
    public function productAction(Request $request) {
        if ($request->isMethod('post')) {
            $id = $request->get('idd');
            $gclid = $request->get('gclid');
            $param1 = $request->get('param1');
            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $item = $repository->findOneBy(array('id' => $id));
            $session = $this->get('session');
            $session->set('item', $id);
            $rel = $repository->findOthers($item);
           
            return array('item' => $item, 'rel' => $rel, 'gclid' => $gclid, 'param1' => $param1);
        
        }

        
    }

    /**
     * @Route("/success", name="ok")
     * @Template("AppBundle:success:index.html.twig")
     */
    public function successAction() {

        $session = $this->get('session');
        $id = $session->get('item');

        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        $result = $repository->findOneBy(array('id' => $id));


        return array('item' => $result);
    }

    /**
     * @Route("/como-funciona", name="how")
     * @Template("AppBundle:how:index.html.twig")
     */
    public function howAction() {

        return array();
    }

    /**
     * @Route("/preguntas-frecuentes", name="faqs")
     * @Template("AppBundle:faqs:index.html.twig")
     */
    public function faqsAction() {

        return array();
    }

    /**
     * @Route("/terminos-y-condiciones", name="terms")
     * @Template("AppBundle:terms:index.html.twig")
     */
    public function termsAction() {

        return array();
    }

    /**
     * @Route("/oxxo", name="ficha_oxxo")
     * @Template("AppBundle:oxxo:index.html.twig")
     */
    public function oxxoAction() {

        return array();
    }

    /**
     * @Route("/jsoner", name="jsoner")
     * @Template("AppBundle:jsoner:index.html.twig")
     */
    public function jsonerAction() {

        return array();
    }

    /**
     * @Route("/todos-los-productos", name="all")
     * @Template("AppBundle:all:index.html.twig")
     */
    public function allAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
        //$result = $repository->findBy(array('featured' => true,'locale' => $request->getLocale())); se usa cuando por idioma quieres ciertos productos.
        $result = $repository->findBy(array('featured' => true));
        return array('prod' => $result);
    }

    /**
     * @Route("/payment", name="resumen")
     * @Method({"POST"})
     * @Template("AppBundle:payment:index.html.twig")
     */
    public function resumenAction(Request $request) {
        if ($request->isMethod('post')) {
            $id = $request->get('itemId');
            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $item = $repository->findOneBy(array('id' => $id));

            $tipoPago = $request -> get('tipoPago');
            $gclid = $request -> get('gclid');
            
            return array('item' => $item, 'tipoPago' => $tipoPago, 'gclid' => $gclid);
        }
    }

    /**
     * @Route("/payment-test", name="payment")
     * @Method({"POST"})
     */
    public function paymentAction(Request $request) {

        $itemPrice = $request->get('item-price') * 100;
        $itemCurrency = $request->get('item-currency');
        $tipoPago = $request->get('tipoPago'); 
        $gclid = $request->get('gclid'); 

        //$gclid = strval($gclid); 

        $apiEnvKey = getenv('CONEKTA_API');
        if (!$apiEnvKey) {
            
            $apiEnvKey = 'key_pfSCsyaS4KDDsbxxZyNSiw'; //producción.
            //$apiEnvKey = 'key_pSupGhGiXRcLbfhWBrR6iA'; //pruebas.
        }
        \Conekta\Conekta::setApiKey($apiEnvKey);

        //Venta única del producto.
        $validOrder = array(
            'line_items' => array(
                array(
                    'name' => 'Coding Service',
                    'description' => 'Coding Service',
                    'unit_price' => $itemPrice,
                    'quantity' => 1,
                )
            ),
            'currency' => $itemCurrency,
        );

        //$campoMoibe = $request->get('card-number');

if ($tipoPago == "tarjeta"){

       //Se arma el array de charges para pago con tarjeta. 
        $charges = array(
            'charges' => array(
                array(
                    'payment_method' => array(
                        'type' => 'card', //card para tarjeta y oxxo_cash para oxxo.
                        'token_id' => $request->get('conektaTokenId') //comenta ésta línea para que no marque error cuando oxxo.
                    ),
                    // MODIFICAR POR EL TOTAL DEL CARRITO DE COMPRAS
                    'amount' => $itemPrice
                )
            ),
            'currency' => $itemCurrency,
            'customer_info' => array(
                'name' => $request->get('card-name'),
                'phone' => $request->get('user-phone'),
                'email' => $request->get('user-mail')
            )
        );

    } else {


               //Se arma el array de charges para pago con OXXO. 
               $charges = array(
                'charges' => array(
                    array(
                        'payment_method' => array(
                            'type' => 'oxxo_cash' //card para tarjeta y oxxo_cash para oxxo.
                            //'token_id' => $request->get('conektaTokenId') //comenta ésta línea para que no marque error cuando oxxo.
                        ),
                        // MODIFICAR POR EL TOTAL DEL CARRITO DE COMPRAS
                        'amount' => $itemPrice
                    )
                ),
                'currency' => $itemCurrency,
                'customer_info' => array(
                    'name' => "Nombre Cliente",
                    'phone' => $request->get('user-phone'),
                    'email' => $request->get('user-mail')
                ),
                'metadata' => array(
                    'gclid' => $gclid
                )
            );

        }
    

        //$order = \Conekta\Order::create(array_merge($validOrder, $charges));
        
        
 
        try { $order = \Conekta\Order::create(array_merge($validOrder, $charges)); 
        
        // COMPRA EXITOSA
        if ($order->payment_status == "paid") {

            $session = $this->get('session');
            $id = $session->get('item');

            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $result = $repository->findOneBy(array('id' => $id));

//Se hará un curl también para mandar el webhook de pago realizado. 

// Initialize curl
$curl = curl_init();
$data = array('orden' => $order);
$jsonEncodedData = json_encode($data);
$opts = array(
CURLOPT_URL             => 'https://hooks.zapier.com/hooks/catch/5245774/obuwdo3/',
CURLOPT_RETURNTRANSFER  => true,
CURLOPT_CUSTOMREQUEST   => 'POST',
CURLOPT_POST            => 1,
CURLOPT_POSTFIELDS      => $jsonEncodedData,
CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: ' . strlen($jsonEncodedData))
    );    

// Set curl options
curl_setopt_array($curl, $opts);

// Get the results
$resultado = curl_exec($curl);

// Close resource
curl_close($curl);

//Hasta aquí. 

        return $this->render('AppBundle:success:index.html.twig', array('item' => $result));

        } elseif ($order->payment_status == "pending_payment"){

            $ID_orden = $order->id;
            $metodo_pago = $order->charges[0]->payment_method->service_name;
            $Referencia =  $order->charges[0]->payment_method->reference;
            $Costo = $order->amount/100;

//Inicia envío de webhook a Zapier. 

// Initialize curl
$curl = curl_init();
$data = array('orden' => $order);
$jsonEncodedData = json_encode($data);
$opts = array(
CURLOPT_URL             => 'https://hooks.zapier.com/hooks/catch/5245774/ooopjpo/',
CURLOPT_RETURNTRANSFER  => true,
CURLOPT_CUSTOMREQUEST   => 'POST',
CURLOPT_POST            => 1,
CURLOPT_POSTFIELDS      => $jsonEncodedData,
CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: ' . strlen($jsonEncodedData))
    );    

// Set curl options
curl_setopt_array($curl, $opts);

// Get the results
$resultado = curl_exec($curl);

// Close resource
curl_close($curl);


            //return new \Symfony\Component\HttpFoundation\JsonResponse(array('Orden' => $ID_orden, 'Metodo' => $metodo_pago, 'Referencia' => $Referencia, 'Costo' => $Costo));

            return $this->render('AppBundle:oxxo:index.html.twig', array('Referencia' => $Referencia, 'Costo' => $Costo));
            
            
        }
        
        
        }
        catch (\Conekta\ProcessingError $error){

            //Obtención de los datos de error: 

             $mensaje_error = $error->getMessage();
             $codigo_error = $error->getCode();

             //Conekta object props
             $conektaError = $error->getConektaMessage();
             
             $error_type = $conektaError->type;
             $error_details = $conektaError->details;

            //Object iteration
            $i = 0;

            foreach ($conektaError->details as $key) {
            $debug_messages[$i] = $key->debug_message;
            $i++; 
            }
                         
          
            //Inicia envío de webhook a Zapier para cachar errores. 

             // Initialize curl
             $curl = curl_init();
             $data = array('mensaje_error' => $mensaje_error, 'codigo_error' => $codigo_error, 'error_type' => $error_type, 'error_details' => $error_details, 'arreglo_debug' => $debug_messages);
             $jsonEncodedData = json_encode($data);
             $opts = array(
             CURLOPT_URL             => 'https://hooks.zapier.com/hooks/catch/5245774/oolirmb/',
             CURLOPT_RETURNTRANSFER  => true,
             CURLOPT_CUSTOMREQUEST   => 'POST',
             CURLOPT_POST            => 1,
             CURLOPT_POSTFIELDS      => $jsonEncodedData,
             CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: ' . strlen($jsonEncodedData))
               );    
 
     // Set curl options
     curl_setopt_array($curl, $opts);
 
     // Get the results
     $resultados = curl_exec($curl);
 
     // Close resource
     curl_close($curl);

            $session = $this->get('session');
            $id = $session->get('item');

            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $result = $repository->findOneBy(array('id' => $id));
            
            $array_error = $error;

            //Aquí se revisará la variable de 'codigo error' que será la que determine si se va hacia A o hacia B.
            //Estos errores remiten al usuario hacia la página de producto. 
            if($codigo_error == "conekta.errors.processing.bank.insufficient_funds" 
            || "conekta.errors.processing.bank.invalid_card_security_code" 
            || "conekta.errors.processing.bank.card_not_supported" 
            || "conekta.errors.processing.bank.declined" 
            || "conekta.errors.processing.bank.invalid_card" 
            || "conekta.errors.processing.bank.invalid_transaction" 
            || "conekta.errors.processing.charge.card_payment.suspicious_behaviour"){

                return $this->render('AppBundle:producto:index.html.twig', array('item' => $result, 'error' => $data, 'tipoPago' => $tipoPago, 'gclid' => $gclid)); //falta tipo de pago y gclid 
//todos los demás errores hacen que repita la página de payment. 
            }else{
 
            return $this->render('AppBundle:payment:index.html.twig', array('item' => $result, 'error' => $data, 'tipoPago' => $tipoPago, 'gclid' => $gclid)); //falta tipo de pago y gclid 
        }

        } catch (\Conekta\ParameterValidationError $error){

             //Obtención de los datos de error: 

             $mensaje_error = $error->getMessage();
             $codigo_error = $error->getCode();
             
             //Conekta object props
             $conektaError = $error->getConektaMessage();
             
             $error_type = $conektaError->type;
             $error_details = $conektaError->details;

             //Object iteration
            
             $i = 0; 

            foreach ($conektaError->details as $key) {
                $debug_messages[$i] = $key->debug_message;
                $i++; 
                }
                         
             
             //Inicia envío de webhook a Zapier para cachar errores. 

             // Initialize curl
             $curl = curl_init();
             $data = array('mensaje_error' => $mensaje_error, 'codigo_error' => $codigo_error, 'error_type' => $error_type, 'error_details' => $error_details, 'arreglo_debug' => $debug_messages);
             $jsonEncodedData = json_encode($data);
             $opts = array(
             CURLOPT_URL             => 'https://hooks.zapier.com/hooks/catch/5245774/oolirmb/',
             CURLOPT_RETURNTRANSFER  => true,
             CURLOPT_CUSTOMREQUEST   => 'POST',
             CURLOPT_POST            => 1,
             CURLOPT_POSTFIELDS      => $jsonEncodedData,
             CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: ' . strlen($jsonEncodedData))
               );    
 
     // Set curl options
     curl_setopt_array($curl, $opts);
 
     // Get the results
     $resultados = curl_exec($curl);
 
     // Close resource
     curl_close($curl);

            $session = $this->get('session');
            $id = $session->get('item');

            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $result = $repository->findOneBy(array('id' => $id));

            $array_error = $error;

           //Aquí se revisará la variable de 'codigo error' que será la que determine si se va hacia A o hacia B.
            //Estos errores remiten al usuario hacia la página de producto. 
            if($codigo_error == "conekta.errors.processing.bank.insufficient_funds" 
            || "conekta.errors.processing.bank.invalid_card_security_code" 
            || "conekta.errors.processing.bank.card_not_supported" 
            || "conekta.errors.processing.bank.declined" 
            || "conekta.errors.processing.bank.invalid_card" 
            || "conekta.errors.processing.bank.invalid_transaction" 
            || "conekta.errors.processing.charge.card_payment.suspicious_behaviour"){

                return $this->render('AppBundle:producto:index.html.twig', array('item' => $result, 'error' => $data, 'tipoPago' => $tipoPago, 'gclid' => $gclid)); //falta tipo de pago y gclid 

            }else{
 
            return $this->render('AppBundle:payment:index.html.twig', array('item' => $result, 'error' => $data, 'tipoPago' => $tipoPago, 'gclid' => $gclid)); //falta tipo de pago y gclid 
        }


          } catch (\Conekta\Handler $error){

             //Obtención de los datos de error: 

             $mensaje_error = $error->getMessage();
             $codigo_error = $error->getCode();
             
             //Conekta object props
             $conektaError = $error->getConektaMessage();
             
             $error_type = $conektaError->type;
             $error_details = $conektaError->details;

             //Object iteration
            
            $i = 0;

            foreach ($conektaError->details as $key) {
                $debug_messages[$i] = $key->debug_message;
                $i++; 
                }
                     
             //Inicia envío de webhook a Zapier para cachar errores. 

             // Initialize curl
             $curl = curl_init();
             $data = array('mensaje_error' => $mensaje_error, 'codigo_error' => $codigo_error, 'error_type' => $error_type, 'error_details' => $error_details, 'arreglo_debug' => $debug_messages);
             $jsonEncodedData = json_encode($data);
             $opts = array(
             CURLOPT_URL             => 'https://hooks.zapier.com/hooks/catch/5245774/oolirmb/',
             CURLOPT_RETURNTRANSFER  => true,
             CURLOPT_CUSTOMREQUEST   => 'POST',
             CURLOPT_POST            => 1,
             CURLOPT_POSTFIELDS      => $jsonEncodedData,
             CURLOPT_HTTPHEADER  => array('Content-Type: application/json','Content-Length: ' . strlen($jsonEncodedData))
               );    
 
     // Set curl options
     curl_setopt_array($curl, $opts);
 
     // Get the results
     $resultados = curl_exec($curl);
 
     // Close resource
     curl_close($curl);

            $session = $this->get('session');
            $id = $session->get('item');

            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $result = $repository->findOneBy(array('id' => $id));

            $array_error = $error;



            return $this->render('AppBundle:payment:index.html.twig', array('item' => $result, 'error' => $data, 'tipoPago' => $tipoPago, 'gclid' => $gclid));



          }

    }

//FIN PAYMENT METHOD
}
