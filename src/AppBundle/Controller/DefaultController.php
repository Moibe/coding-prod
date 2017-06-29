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
        $result = $repository->findAll();
        return array('prod' => $result);
    }

    /**
     * @Route("/payment-test", name="payment")
     */
    public function paymentAction() {

    setApiKey("key_93HD4i8jEdq4yA66xtdLXQ");
    $valid_order =
    array(
        'line_items'=> array(
            array(
                'name'        => 'Box of Cohiba S1s',
                'description' => 'Imported From Mex.',
                'unit_price'  => 20000,
                'quantity'    => 1,
                'sku'         => 'cohb_s1',
                'category'    => 'food',
                'tags'        => array('food', 'mexican food')
                )
           ),
          'currency'    => 'mxn',
          'metadata'    => array('test' => 'extra info'),
          'charges'     => array(
              array(
                  'payment_source' => array(
                      'type'       => 'oxxo_cash',
                      'expires_at' => strtotime(date("Y-m-d H:i:s")) + "36000"
                   ),
                   'amount' => 20000
                )
            ),
            'currency'      => 'mxn',
            'customer_info' => array(
                'name'  => 'John Constantine',
                'phone' => '+5213353319758',
                'email' => 'hola@hola.com'
            )
        );

try {
  $order = \Conekta\Order::create($valid_order);
} catch (\Conekta\ProcessingError $e){ 
  echo $e->getMessage();
} catch (\Conekta\ParameterValidationError $e){
  echo $e->getMessage();
} finally (\Conekta\Handler $e){
  echo $e->getMessage();
}

    }


}
