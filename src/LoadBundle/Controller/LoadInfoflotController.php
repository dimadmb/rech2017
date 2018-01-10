<?php

namespace LoadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LoadInfoflotController extends Controller
{
	/**
	 * @Template()
	 * @Route("/loadinfoflot_ship/{ship_id}", name="loadinfoflot_ship" )
     */			
	public function loadShipAction($ship_id = null)
	{
		$load = $this->get('load.loadinfoflot');
		$res = $load->load($ship_id, false);
		return  $res;
		//return $load->load($ship_id);
	}	
	
	/**
	 * @Template()
	 * @Route("/clear", name="clear" )
     */			
	public function clearAction()
	{
		$em = $this->getDoctrine()->getManager();
		$shipRepos = $this->getDoctrine()->getRepository('CruiseBundle:Ship');		
		$ships = $shipRepos->findAll();
		foreach($ships as $ship)
		{
			$em->remove($ship);
		}
		$em->flush();
		return  ["dump"=>$ships];

	}	
	
}