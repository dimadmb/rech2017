<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin/cruise")
 */

class AdminCruiseController extends Controller
{
    /**
     * @Route("/discount", name="admin_cruise_discount")
	 * @Template()
     */
    public function discountAction(Request $request)
    {
        // отображаем тут список круизов
		
		$cruises = $this->get('cruise_search')->searchCruise($request->query->all());
		return ['cruises'=>$cruises];
    }	
}
