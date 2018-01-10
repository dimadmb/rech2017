<?php

namespace LoadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LoadMosturflotController extends Controller
{
	/**
	 * @Template()
	 * @Route("/loadmosturflot_ship/{ship_id}", name="loadmosturflot_ship" )
     */			
	public function loadShipAction($ship_id = null)
	{
		$load = $this->get('load.loadmosturflot');
		$res = $load->load($ship_id, false);
		return  $res;
		//return $load->load($ship_id);
	}	
	
}