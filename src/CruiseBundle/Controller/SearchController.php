<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SearchController extends Controller
{

	private	$assocPlace = 
		[
		"moscow" => ["inf" => "Москва", "gen" => "Москвы"],
		"nnovgorod" =>  ["inf" => "Нижний Новгород", "gen" => "Нижнего Новгорода"],
		"volgograd" => ["inf" => "Волгоград", "gen" => "Волгограда"],
		"saratov" => ["inf" => "Саратов", "gen" => "Саратова"],
		//"yaroslavl" => ["inf" => "Ярославль", "gen" => "Ярославля"],
		"spb" => ["inf" => "Санкт-Петербург", "gen" => "Санкт-Петербурга"],
		"kazan" => ["inf" => "Казань", "gen" => "Казани"],
		"samara" => ["inf" => "Самара", "gen" => "Самары"],
		"astrahan" => ["inf" => "Астрахань", "gen" => "Астрахани"],
		];

	public function searchFormAction($action = null)
	{
		$request =  Request::createFromGlobals();
		$repository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
		
		$minDate = $repository->findMinStartDate();
		$maxDate = $repository->findMaxStartDate();
		if($minDate == null) 
		{
			return new Response('');
		}
		// понадобятся для фильтра по дням
		$minDays = $repository->findMinDays()->getDaycount();
		$maxDays = $repository->findMaxDays()->getDaycount();

		$days = $request->query->get('days');
		if($days == null)
		{
			$days = $minDays.','.$maxDays;
		}

		foreach($this->assocPlace as $placeStart)
		{
			$selected = false;
			if($request->query->get('placeStart') == $placeStart['inf']) $selected = true;
			$form["placesStart"][] = ["name"=>$placeStart['inf'], "selected"=>$selected];
		}		

		
		$form["startDate"] = $request->query->get('startDate') == null ? $minDate->getStartDate() : $request->query->get('startDate');
		$form["endDate"] =   $request->query->get('endDate') == null ? $maxDate->getEndDate() : $request->query->get('endDate');
		$form["ships"] = $this->getActiveShip();
		if($request->query->get('ship') != null) 
		{
			foreach($form["ships"] as $ship)
			{
				if($ship->getId() == $request->query->get('ship'))
				{
					$ship->selected = true;
				}
			}
		}
		$form["minDays"] = $minDays;
		$form["maxDays"] = $maxDays;
		$form["days"] = $request->query->get('days') == null ? $minDays.','.$maxDays : $request->query->get('days');
		$form["places"] = $this->getActivePlaces();
		if(null != $places = $request->query->get('places') )
		{
			foreach($form["places"] as $place)
			{
				if(in_array($place->getId(),$places))
				{
					$place->checked = true;
				}
			}
		}
		
		$form["offers"] = $this->getOffers();
		if(null != $offers = $request->query->get('offers') )
		{
			foreach($form["offers"] as $offer)
			{
				if(in_array($offer->getId(),$offers))
				{
					$offer->checked = true;
				}
			}
		}
		
		
		
		
		//dump($action);
        return $this->render('CruiseBundle:Search:searchForm.html.twig', array(
            'form' => $form,
			'action' => $action
        ));		

	}

	public function getActivePlaces()
	{
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			"SELECT p 
			FROM CruiseBundle:Place p 
			WHERE EXISTS 
				(SELECT pi FROM CruiseBundle:ProgramItem pi WHERE pi.place = p.id AND p.url <> '' )
			ORDER BY p.name
			"
		);	
		return $query->getResult();

	}
	public function getOffers()
	{
		$em = $this->getDoctrine()->getManager();

		return $em->getRepository("CruiseBundle:TypeDiscount")->findAll();

	}

	
	public function getActiveShip()
	{
		$em = $this->getDoctrine()->getManager();
		$query = $em->createQuery(
			'SELECT s 
			FROM CruiseBundle:Ship s 
			WHERE EXISTS
				(SELECT c FROM CruiseBundle:Cruise c WHERE c.ship = s.id )
			ORDER BY s.name
			'
		);
		
		return $query->getResult();

	}
}
