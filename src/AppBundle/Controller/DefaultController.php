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
        $redirect = $this->generateUrl('homepage');

        if (!$product->getFeatured()) {
            $redirect = "https://geopositioningservices.com";
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
            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $item = $repository->findOneBy(array('id' => $id));
            $session = $this->get('session');
            $session->set('item', $id);
            $rel = $repository->findOthers($item);
            return array('item' => $item, 'rel' => $rel);
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
     * @Route("/todos-los-productos", name="all")
     * @Template("AppBundle:all:index.html.twig")
     */
    public function allAction() {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
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
            return array('item' => $item);
        }
    }

    /**
     * @Route("/payment-test", name="payment")
     * @Method({"POST"})
     */
    public function paymentAction(Request $request) {

        $itemPrice = $request->get('item-price') * 100;

        $apiEnvKey = getenv('CONEKTA_API');
        if (!$apiEnvKey) {
            // CAMBIAR POR LA LLAVE PRIVADA DE PRODUCCIÓN
            $apiEnvKey = 'key_93HD4i8jEdq4yA66xtdLXQ';
        }
        \Conekta\Conekta::setApiKey($apiEnvKey);

        $validOrder = array(
                    'line_items' => array(
                        array(
                            'name' => $request->get('item-name'),
                            'description' => $request->get('item-name') . ' by Coding Depot',
                            'unit_price' => $itemPrice,
                            'quantity' => 1,
                        )
                    ),
                    'currency' => 'mxn',
        );

        $charges = array(
            'charges' => array(
                array(
                    'payment_method' => array(
                        'type' => 'card',
                        'token_id' => $request->get('conektaTokenId')
                    ),
                    // MODIFICAR POR EL TOTAL DEL CARRITO DE COMPRAS
                    'amount' => $itemPrice
                )
            ),
            'currency' => 'mxn',
            'customer_info' => array(
                'name' => $request->get('card-name'),
                'phone' => $request->get('user-phone'),
                'email' => $request->get('user-mail')
            )
        );

        $order = \Conekta\Order::create(array_merge($validOrder, $charges));

        // COMPRA EXITOSA
        if ($order->payment_status == "paid") {

            $session = $this->get('session');
            $id = $session->get('item');

            $repository = $this->getDoctrine()->getRepository('AppBundle:Product');
            $result = $repository->findOneBy(array('id' => $id));

            return $this->render('AppBundle:success:index.html.twig', array('item' => $result));
        }
    }

//FIN PAYMENT METHOD
}
