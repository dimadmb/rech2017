<?php

namespace LoadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LoadGamaController extends Controller
{
	/**
	 * @Template()
	 * @Route("/loadgama_ship/{ship_id}/{update}", name="loadgama_ship" )
     */			
	public function loadShipAction($ship_id = null, $update = false)
	{
		$load = $this->get('load.loadgama');
		$update = $update === false ? false : true;
		$res = $load->load($ship_id, $update);
		return  $res;
		//return $load->load($ship_id);
	}		
}
