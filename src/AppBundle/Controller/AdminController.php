<?php

namespace AppBundle\Controller;

use JavierEguiluz\Bundle\EasyAdminBundle\Controller\AdminController as BaseAdminController;

class AdminController extends BaseAdminController {

    public function createEntityForm($entity, array $entityProperties, $view) {
        $form = parent::createEntityForm($entity, $entityProperties, $view);
        
        $choices = array('paypal' => 'PayPal','conekta' => 'Conekta');
        
        if ($this->entity['name'] === 'Product') {
            $form->remove('slug');
            $form->remove('archivo');
            $form->remove('payment_type');
            $form->add('payment_type','choice',array('required' => true, 'choices' => $choices));
            $form->add('archivo', 'vlabs_file', array('required' => false));
        }

        if ($this->entity['name'] === 'Category') {
            $form->remove('slug');
            $form->remove('cover');
            $form->remove('products');
            $form->add('cover', 'vlabs_file', array('required' => false));
        }

        return $form; 
    }

}

?>