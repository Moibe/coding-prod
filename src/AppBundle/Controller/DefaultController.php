<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template("AppBundle:home:index.html.twig")
     */
    public function indexAction()
    {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Category');
        $result = $repository->findAll();

        return array('cat' => $result);
    }

    /**
     * @Route("/categoria/{slug}", name="categoria")
     * @Template("AppBundle:categoria:index.html.twig")
     */
    public function categoryAction($slug) {

        $repository = $this->getDoctrine()->getRepository('AppBundle:Category');
        $cat = $repository->findOneBy(array('slug' => $slug));
        $id= $this->getDoctrine()->getManager();
        $id= $cat->getId();

        $repoProd = $this->getDoctrine()->getRepository('AppBundle:Product');
        $prod = $repoProd->findBy(array('category' => $id));
        
        return array('pr' => $prod);
    }
}
