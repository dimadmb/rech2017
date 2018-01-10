<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/cruises_off")
     */
    public function indexAction()
    {
        return $this->render('CruiseBundle:Default:index.html.twig');
    }
}
