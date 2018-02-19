<?php

namespace ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
//use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
//use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

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
		
		$years = [];
		foreach(range(date("Y"), 2017) as $year)
		{
			$years[$year] = $year;
		}
		
		$months = [
					'январь' => 1,
					'февраль' => 2,
					'март' => 3,
					'апрель' => 4,
					'май' => 5,
					'июнь' => 6,
					'июль' => 7,
					'август' => 8,
					'сентябрь' => 9,
					'октябрь' => 10,
					'ноябрь' => 11,
					'декабрь' => 12,
					];
					
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
				->add('date_month',ChoiceType::class,['choices'=> $months, 'label'=>'Месяц'])
				->add('date_year',ChoiceType::class, ['choices'=> $years,'label'=>'Год'])											
															
													
															
				->getForm()
			;
		
		$form->handleRequest($request);		
		
		return ['form'=>$form->createView()];
		return new Response("OK");
	}
	
	/**
	 * @Template()
	 * @Route("/agency_report_sales", name="manager_agency_report_sales")
	 */
	public function reportSales(Request $request)
	{
		
		$date_year = $request->query->get('date_year');
		$date_month = $request->query->get('date_month');
		$res = $this->get('report_agent')->reportSales($date_year,$date_month);
		return $res;
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
	
	/**
	 * @Route("/agency_act", name="manager_agency_act")
	 */
	public function act(Request $request)
	{
		
		$agency_id = $request->query->get('agency_id');
		$date_year = $request->query->get('date_year');
		$date_month = $request->query->get('date_month');


		$response = $this->get('report_agent')->act($agency_id,$date_year,$date_month);
		

		
		return $response;
	}
	
}
