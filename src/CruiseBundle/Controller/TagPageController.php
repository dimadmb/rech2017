<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class TagPageController extends Controller
{
	
	public $placesAssoc = [
		"moscow" => ["inf" => "Москва", "gen" => "Москвы", "acc"=>"Москву"],
		"nnovgorod" =>  ["inf" => "Нижний Новгород", "gen" => "Нижнего Новгорода", "acc"=>"Нижний Новгород"],
		"volgograd" => ["inf" => "Волгоград", "gen" => "Волгограда", "acc"=>"Волгоград"],
		"saratov" => ["inf" => "Саратов", "gen" => "Саратова", "acc"=>"Саратов"],
		//"yaroslavl" => ["inf" => "Ярославль", "gen" => "Ярославля"],
		"sankt-peterburg" => ["inf" => "Санкт-Петербург", "gen" => "Санкт-Петербурга", "acc"=>"Санкт-Петербург"],
		"kazan" => ["inf" => "Казань", "gen" => "Казани", "acc"=>"Казань"],
		"samara" => ["inf" => "Самара", "gen" => "Самары", "acc"=>"Самару"],
		"astrakhan" => ["inf" => "Астрахань", "gen" => "Астрахани", "acc"=>"Астрахань"],
	];
	
	public $riverAssoc = [
		"kruizy-po-donu" =>  "Дону",
		"kruizy-po-volge" => "Волге",
		"kruizy-po-kame" =>  "Каме",
	];	
	
	public $monthAssoc = [
#		"2018-04" =>  "Апреле 2018",
		"2018-05" =>  "Мае 2018",
		"2018-06" =>  "Июне 2018",
		"2018-07" =>  "Июле 2018",
		"2018-08" =>  "Августе 2018",
		"2018-09" =>  "Сентябре 2018",
		"2018-10" =>  "Октябре 2018",
		"2018-11" =>  "Ноябре 2018",
	];
	
	public function createSubMenuAction()
	{
		
		$request = Request::createFromGlobals();
		$router = $this->get("router");
		$route = $router->match($request->getPathInfo());
		
		//dump($route["_route"]);
		//dump($route);
		
		$links = "";
		
		if($route['_route'] == 'page')
		{
			$urls = explode("/",$route['url']);
	
	
			$ships = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findAll();
			
			// отправление из города
			if((count($urls) == 3) && ($urls[0] == 'cruise') && ($urls[1] == 'from') )
			{					
				$links = $this->renderView("CruiseBundle:TagPage:linksFrom.html.twig",[
						'from'=> $urls[2],
						'placesAssoc' => $this->placesAssoc,
						'riverAssoc' => $this->riverAssoc,
						'monthAssoc' => $this->monthAssoc,
						'ships' => $ships,
						]);
			}		
			
			// прибытие в город
			if((count($urls) == 3) && ($urls[0] == 'cruise') && ($urls[1] == 'to') )
			{					
				$links = $this->renderView("CruiseBundle:TagPage:linksTo.html.twig",[
						'to'=> $urls[2],
						'placesAssoc' => $this->placesAssoc,
						'riverAssoc' => $this->riverAssoc,
						'monthAssoc' => $this->monthAssoc,
						'ships' => $ships,
						]);
			}
			
						
			// по реке 
			if((count($urls) == 3) && ($urls[0] == 'cruise') && ($urls[1] == 'rivers') )
			{					
				$links = $this->renderView("CruiseBundle:TagPage:linksRiver.html.twig",[
						'river'=> $urls[2],
						'placesAssoc' => $this->placesAssoc,
						'riverAssoc' => $this->riverAssoc,
						'monthAssoc' => $this->monthAssoc,
						'ships' => $ships,
						]);
			}			
						
			// месяц
			if((count($urls) == 3) && ($urls[0] == 'cruise') && ($urls[1] == 'month') )
			{					
				$links = $this->renderView("CruiseBundle:TagPage:linksMonth.html.twig",[
						'month'=> $urls[2],
						'placesAssoc' => $this->placesAssoc,
						'riverAssoc' => $this->riverAssoc,
						'monthAssoc' => $this->monthAssoc,
						'ships' => $ships,
						]);
			}
			
			
		}
		
		return new Response($links);
	}
	
	
	/**
	 * 1 из города в город
	 * @Route("/cruise/from/{place_from}/to/{place_to}", name="cruise_from_to"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromTo($place_from,$place_to)
	{
	$cruises = $this->get('cruise_search')->searchCruise(
	[
	'placeStart'=>$this->placesAssoc[$place_from]['inf'],
	'placeStop'=>$this->placesAssoc[$place_to]['inf']
	]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	
	
	
	/**
	 * 2 из города по реке 
	 * @Route("/cruise/from/{place_from}/river/{river}", name="cruise_from_river"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromRiver($place_from,$river)
	{
	$cruises = $this->get('cruise_search')->searchCruise(
		['placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'category'=>$river 
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].'  по '.$this->riverAssoc[$river].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].'  по '.$this->riverAssoc[$river].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	
	
	/**
	 * 3 из города месяц
	 * @Route("/cruise/from/{place_from}/month/{month}", name="cruise_from_month"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromMonth($place_from,$month)
	{
	$cruises = $this->get('cruise_search')->searchCruise(
		['placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'month'=>$month 
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->monthAssoc[$month].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].'  в '.$this->monthAssoc[$month].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].'  в '.$this->monthAssoc[$month].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	

	
	/**
	 * 4 из города теплоход
	 * @Route("/cruise/from/{place_from}/on/{shipCode}", name="cruise_from_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromOnShip($place_from,$shipCode)
	{
	$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
	$shipName = $ship->getName();
	$cruises = $this->get('cruise_search')->searchCruise(
		['placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].'  на теплоходе '.$shipName.'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].'  на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	
	
	/**
	 * 5 в город по реке
	 * @Route("/cruise/to/{place_to}/river/{river}", name="cruise_to_river"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseToRiver($place_to,$river)
	{
	$cruises = $this->get('cruise_search')->searchCruise([
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'category'=>$river 
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].' - цены и расписание на 2018',
		'h1'=>'Круизы в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].'',
		'description' => 'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	
	
	/**
	 * 6 в город месяц
	 * @Route("/cruise/to/{place_to}/month/{month}", name="cruise_to_month"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseToMonth($place_to,$month)
	{
	$cruises = $this->get('cruise_search')->searchCruise([
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'month'=>$month
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' в '.$this->monthAssoc[$month].' - цены и расписание на 2018',
		'h1'=>'Круизы в '.$this->placesAssoc[$place_to]['acc'].' в '.$this->monthAssoc[$month].'',
		'description' => 'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' в '.$this->monthAssoc[$month].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}

	
	
	
	
	/**
	 * 7 в город теплоход
	 * @Route("/cruise/to/{place_to}/on/{shipCode}", name="cruise_to_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseToOnShip($place_to,$shipCode)
	{
	$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
	$shipName = $ship->getName();
	$cruises = $this->get('cruise_search')->searchCruise([
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}

	
	
	/**
	 * 8 река месяц
	 * @Route("/cruise/river/{river}/month/{month}", name="cruise_river_month"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseRiverMonth($river,$month)
	{
	$cruises = $this->get('cruise_search')->searchCruise([
		'category'=>$river ,
		'month'=>$month
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].' - цены и расписание на 2018',
		'h1'=>'Круизы по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].'',
		'description' => 'Речные круизы по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	


	
	
	/**
	 * 9 река теплоход
	 * @Route("/cruise/river/{river}/on/{shipCode}", name="cruise_river_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseRiverOnShip($river,$shipCode)
	{
	$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
	$shipName = $ship->getName();		
	$cruises = $this->get('cruise_search')->searchCruise([
		'category'=>$river ,
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	


	
	
	/**
	 * 10 месяц теплоход
	 * @Route("/cruise/month/{month}/on/{shipCode}", name="cruise_month_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseMonthOnShip($month,$shipCode)
	{
	$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
	if ($ship == null) {
		throw $this->createNotFoundException("Страница не найдена.");
	}	
	$shipName = $ship->getName();

			
	$cruises = $this->get('cruise_search')->searchCruise([
		'month'=>$month ,
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	


	
	/**
	 * 11 из города в город река
	 * @Route("/cruise/from/{place_from}/to/{place_to}/river/{river}", name="cruise_from_to_river"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromToRiver($place_from,$place_to,$river)
	{
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'category'=>$river ,
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' по '.$this->riverAssoc[$river].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	



	
	/**
	 * 12 из города в город месяц
	 * @Route("/cruise/from/{place_from}/to/{place_to}/month/{month}", name="cruise_from_to_month"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromToMonth($place_from,$place_to,$month)
	{
	
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'month'=>$month ,
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].'  в '.$this->monthAssoc[$month].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' в '.$this->monthAssoc[$month].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' в '.$this->monthAssoc[$month].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	





	
	/**
	 * 13 из города в город теплоход
	 * @Route("/cruise/from/{place_from}/to/{place_to}/on/{shipCode}", name="cruise_from_to_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromToOnShip($place_from,$place_to,$shipCode)
	{
		$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
		if ($ship == null) {
			throw $this->createNotFoundException("Страница не найдена.");
		}	
		$shipName = $ship->getName();	
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'placeStop'=>$this->placesAssoc[$place_to]['inf'],
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->placesAssoc[$place_to]['acc'].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	






	
	/**
	 * 14 из города по реке месяц
	 * @Route("/cruise/from/{place_from}/river/{river}/month/{month}", name="cruise_from_river_month"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromRiverMonth($place_from,$river,$month)
	{
		
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'category'=>$river ,
		'month'=>$month ,
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' в '.$this->monthAssoc[$month].'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	





	
	/**
	 * 15 из города по реке на теплоходе
	 * @Route("/cruise/from/{place_from}/river/{river}/on/{shipCode}", name="cruise_from_river_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromRiverOnShip($place_from,$river,$shipCode)
	{
		$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
		if ($ship == null) {
			throw $this->createNotFoundException("Страница не найдена.");
		}	
		$shipName = $ship->getName();
		
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'category'=>$river ,
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' по '.$this->riverAssoc[$river].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	




	
	/**
	 * 16 из города месяц на теплоходе
	 * @Route("/cruise/from/{place_from}/month/{month}/on/{shipCode}", name="cruise_from_month_on_ship"  )
	 * @Template("CruiseBundle:Cruise:index.html.twig")		 
	 */
	public function CruiseFromMonthOnShip($place_from,$month,$shipCode)
	{
		$ship = $this->getDoctrine()->getRepository("CruiseBundle:Ship")->findOneByCode($shipCode);
		if ($ship == null) {
			throw $this->createNotFoundException("Страница не найдена.");
		}	
		$shipName = $ship->getName();
		
		$cruises = $this->get('cruise_search')->searchCruise(
		[
		'placeStart'=>$this->placesAssoc[$place_from]['inf'],
		'month'=>$month ,
		'shipCode'=>$shipCode
		]);
		return [
		"months"=>$this->month($cruises),
		'title'=>'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.' - цены и расписание на 2018',
		'h1'=>'Круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.'',
		'description' => 'Речные круизы из '.$this->placesAssoc[$place_from]['gen'].' в '.$this->monthAssoc[$month].' на теплоходе '.$shipName.'. Цены туров и расписание маршрутов на 2018 год с названием теплохода. Купить билет можно сразу на сайте, достаточно лишь оформить заявку.',
		];
	}	








	
	/*
	 * 17 из города в город река  /// есть в 11 
	 */







	
	
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
	
}
