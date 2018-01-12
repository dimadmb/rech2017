<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;




class CruiseController extends Controller
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
     * @Route("/cruise", name="cruise")
     */
    public function indexAction(Request $request)
    {
		$cruises = $this->searchCruise();
		return ["months"=>$this->month($cruises)];
    }

    /**
	 * @Template("CruiseBundle:Cruise:index.html.twig")	
     * @Route("/search", name="search")
     */
    public function searchAction(Request $request)
    {
		$cruises = $this->searchCruise($request->query->all());
		//return ["cruises"=>$cruises];
		return ["months"=>$this->month($cruises)];
    }
	
	
    /**
	 * @Template("CruiseBundle:Cruise:cruises.html.twig")	
     */
    public function searchInAction(Request $request, $parameters = [])
    {
		$cruises = $this->searchCruise($parameters);
		//return ["cruises"=>$cruises];
		return ["months"=>$this->month($cruises)];
    }	
	

	public function shipAction($ship)
	{
		$cruises = $this->searchCruise(["ship"=>$ship]);
		//return ["cruises"=>$cruises];
        return $this->render('CruiseBundle:Cruise:cruises.html.twig', ["months"=>$this->month($cruises)]);				
	}
	
	
	/// группировка по месяцам
	public function month($cruises)
	{
		$month = "";
		$months = [];
		foreach($cruises as $cruise)
		{
			if(date("Y-m",$cruise->getStartDate()->getTimestamp()) != $month)
			{
				$month = date("Y-m",$cruise->getStartDate()->getTimestamp()); 
			}
			$months[$month][] = $cruise;
		}
		
		return $months;
	}

    /**
	 * @Template()	
     * @Route("/cruise/{id}", name="cruisedetail",     
	 *     requirements={
     *         "id": "\d+"
     *     })
     */
	public function cruiseDetailAction($id)
	{
				
		$cruiseRepository = $this->getDoctrine()->getRepository("CruiseBundle:Cruise");
		
		$em = $this->getDoctrine()->getManager();
		
		$cruise = $em->createQueryBuilder()
			->select('c,td')
			->from("CruiseBundle:Cruise",'c')
			->leftJoin('c.typeDiscount','td')
			->where('c.id ='.$id)
			->getQuery()
			->getOneOrNullResult()
		;
		

		//$cruise = $cruiseProgram = $cruiseRepository->getProgramCruise($id);
		if($cruise == null)
		{
			throw $this->createNotFoundException("Страница не найдена.");
		}			
		$cruiseShipPrice = $cruiseRepository->getPrices($id);
		
		//dump($cruiseShipPrice);
		
		$session = new Session();
		$basket = $session->get('basket');	
		if(null === $basket)
		{
			$session->set('basket',[]);	
		}
		


		$tariff_arr = array();
		$cabins = array();
		
		if($cruiseShipPrice != null)
		{
			
			$roomDiscounts = $this->getDoctrine()->getRepository("CruiseBundle:RoomDiscount")->findByCruise($cruise);
			
			$discount = $cruise->getTypeDiscount();
			$active_rooms = [];
			if(null !== $discount)
			{
				foreach($roomDiscounts as  $roomDiscount)
				{
					$active_rooms[] = $roomDiscount->getRoom()->getId();
				}				
			}
			//$available_rooms = $this->get('cruise')->getAvailibleRooms($cruise);
			$available_rooms = $this->get('cruise')->getRoomsIdArray($cruise->getId());

			//dump($available_rooms);
			
			$cabinsAll = $cruiseShipPrice->getShip()->getCabin();
			
			foreach($cabinsAll as $cabinsItem)
			{
				
				
				$discountInCabin = false;
				$rooms_in_cabin = array();
				foreach($cabinsItem->getRooms() as $room)
				{
					
					if(in_array($room->getId(),$active_rooms))
					{
						//$room->discount = true;
						$discountInCabin = true;
					}
					else
					{
						//$room->discount = false;
					}
					
					if(in_array($room->getId(),$available_rooms) /*|| true*/)
					{
						$rooms_in_cabin[] = $room;
					}
					/*
					elseif(in_array($room->getId(),$active_rooms))
					{
						$rooms_in_cabin[] = $room;
					}
					*/
					
				}

				foreach($cabinsItem->getPrices() as $prices)
				{

					$tariff_arr[$prices->getTariff()->getname()]=1;
					
					$price[$prices->getPlace()->getRpName()]['prices'][$prices->getTariff()->getname()][$prices->getMeals()->getName()] = $prices;
					$price[$prices->getPlace()->getRpName()]['place'] = $prices->getPlace()->getRpId();
					//$price[$prices->getRpId()->getRpName()]['rooms'] = $rooms_in_cabin;//список кают
					// сюда добавить свободные каюты
					//$rooms => 
					
				}
				$cabins[$cabinsItem->getDeck()->getName()][] = array(
					'cabinName' =>$cabinsItem->getType()->getComment(),
					'cabin' => $cabinsItem,
					'rpPrices' => $price,
					'rooms' => $rooms_in_cabin,
					'discountInCabin' => $discountInCabin
					// тут можно посчитать количество rowspan
					)
					;
				unset($price);	
			}	
		}
		else
		{
			return ['cruise' => $cruise, 'cabins' => null,'tariff_arr'=>null ];
		}		

		
		return [ 	
					'cruise' => $cruise, 
					'cabins' => $cabins,
					'tariff_arr'=>$tariff_arr ,
					'discount'=>$discount,
					'request' => Request::createFromGlobals(),
					'rooms' => $available_rooms,
					];
	}



	public function searchCruise($parameters = array())
	{
		return $this->get('cruise_search')->searchCruise($parameters);
	}

	
	
	// вместо этого контроллера есть параметр в поиске
	/**
	 * @Template()
     * @Route("/cruise/categoryroutes/{category}.html", name="categoryroutes")	 
	*/
	public function categoryroutesAction($category) 
	{
		$em = $this->getDoctrine()->getManager();

		$category = $em->createQueryBuilder()
						->select('cc,c')
						->from('CruiseBundle:Category','cc')
						->leftJoin('cc.cruises','c')
						->where('c.endDate >= 	CURRENT_DATE()')
						->andWhere("cc.code = '$category'")
						->getQuery()
						->getOneOrNullResult()
					;
		
		if(null == $category )
		{
			throw $this->createNotFoundException("Страница не найдена.");
		}
		
		$cruises_months= $this->month($category->getCruises());
		
		return array('months' => $cruises_months, 'category' => $category  );
	}


	
}


