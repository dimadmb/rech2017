<?php

namespace ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

use CruiseBundle\Entity\Agency;


/**
 * @Route("/manager")
 */

class ReportAgencyController extends Controller
{
	
	/**
	 * @Route("/agency_report_index", name="manager_agency_report_index")
	 * @Template()		 
	 */
	public function index(Request $request)
	{
		$form = $this->createFormBuilder()
				
				->add('agency',EntityType::class,[
								//'required'=> true,
								'class' => Agency::class,
								'query_builder' => function (EntityRepository $er) {
									return $er->createQueryBuilder('a')
										->where('a.active = 1')
										->orderBy('a.name', 'ASC');
										},
										'label'=>"Агентство"
				])
				->add('date',DateType::class,['required'=>true,'years'=> range(date("Y"), 2017), 'days' => range(1,1)])											
													
															
				->getForm()
			;
		
		$form->handleRequest($request);		
		
		return ['form'=>$form->createView()];
		return new Response("OK");
	}
	
	/**
	 * @Route("/agency_report", name="manager_agency_report")
	 */
	public function report(Request $request)
	{
		
		$agency_id = $request->query->get('agency_id');
		$date_year = $request->query->get('date_year');
		$date_month = $request->query->get('date_month');


		$response = $this->get('report_agent')->report($agency_id,$date_year,$date_month);
		

		
		return $response;
	}
	
}
