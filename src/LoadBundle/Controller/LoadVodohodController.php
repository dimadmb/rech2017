<?php

namespace LoadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class LoadVodohodController extends Controller
{



	public function curl_get_file_contents($URL)
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $URL);
			$contents = curl_exec($c);
			curl_close($c);

			if ($contents) return $contents;
				else return FALSE;
		}






    /**
	 * @Template()	
     * @Route("/loadvodohod", name="loadvodohod")
     */
    public function indexAction()
	{
		$url_motorships = "http://cruises.vodohod.com/agency/json-motorships.htm?pauth=jnrehASKDLJcdakljdx";
		
		$motorships_json = $this->curl_get_file_contents($url_motorships);
		
		$motorships = json_decode($motorships_json,true);
		
		$ships = [];
		
		foreach($motorships as $motorship_id=>$motorship)
		{
			$ships[] = array('id' => $motorship_id, 'name' => $motorship['name']);
		}
		
		return array('ships' => $ships, "dump"=>$motorships);
	}

	/**
	 * @Template()
	 * @Route("/loadvodohod_ship/{ship_id}", name="loadvodohod_ship" )
     */			
	public function loadShipAction($ship_id = null)
	{
		$load = $this->get('load.loadvodohod');
		$res = $load->load($ship_id, false);
		return  $res;
		//return $load->load($ship_id);
	}	

	
}
