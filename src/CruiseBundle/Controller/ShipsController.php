<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class ShipsController extends Controller
{
    /**
	 * @Template()	
     * @Route("/ships", name="ships")
     */
    public function indexAction()
    {
		$ships = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findBy(array(),array('name' => 'ASC' ));
		return ["ships"=>$ships];
    }
	

}
