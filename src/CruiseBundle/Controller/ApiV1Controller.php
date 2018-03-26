<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use CruiseBundle\Entity\Ordering;
use CruiseBundle\Entity\OrderItem;
use CruiseBundle\Entity\OrderItemPlace;

use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

	/**
	 * @Route("/api/v1", name="api_v1" )
     */	


class ApiV1Controller extends Controller
{
    
const PATH_IMG = "files/ship/";
	
	/**
	 * @Template()
	 * @Route("/cruises/{pre}", name="api_v1_json_cruises" )
     */			
	public function cruisesAction($pre = false)
	{

		$cruises_json = $this->getCruises();

		
		if($pre) return array('cruises_json'=> '<pre>'.print_r($cruises_json,1).'</pre>');
		return array('cruises_json'=> json_encode($cruises_json));
	}
	
	private function getCruises()
	{
		$cruisesRepository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
		
		$cruises = $cruisesRepository->findApiAll();
		//$cruises = $this->get('cruise_search')->searchCruise();
		
		// если есть параметры для поиска, то можно давать поиск и из-за уменьшения строк скорость компенсируется 
		
		foreach($cruises as $cruise)
		{
			$cruises_json[$cruise->getId()]['is_happy'] = $cruise->getTypeDiscount() === null ? 0 : ($cruise->getTypeDiscount()->getCode() == 'happy' ? 1 : 0 ) ;
			$cruises_json[$cruise->getId()]['is_special'] = $cruise->getTypeDiscount() === null ? 0 : ($cruise->getTypeDiscount()->getCode() == 'special' ? 1 : 0 ) ;
			$cruises_json[$cruise->getId()]['date_start'] = $cruise->getStartdate()->format('U');
			$cruises_json[$cruise->getId()]['date_stop'] = $cruise->getEnddate()->format('U');
			$cruises_json[$cruise->getId()]['days'] = $cruise->getDaycount();
			$cruises_json[$cruise->getId()]['ship'] = $cruise->getShip()->getName();
			$cruises_json[$cruise->getId()]['ship_id'] = $cruise->getShip()->getId();
			$cruises_json[$cruise->getId()]['ship_photo_main'] = $this->renderView("CruiseBundle:ApiV1:img.html.twig",[
					'img_url' =>  
					self::PATH_IMG.$cruise->getShip()->getCode().'/'.$cruise->getShip()->getCode().'-main.jpg',
			]);
			$cruises_json[$cruise->getId()]['route'] = $cruise->getName();
			
		}
		return $cruises_json;
	}
	


	private function getDescription($cruise_code)
	{
		
		$cruisesRepository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
		$cruise_prices = $cruisesRepository->findOneById($cruise_code);

		
		if($cruise_prices == null)
		{
			return array('array' => json_encode(array('error' => "Продажи путевок на выбранный тур завершены")));
		}		
		
		$cruise_description  = array(
				'date_start' => $cruise_prices->getStartdate()->format('U'),
				'date_stop' => $cruise_prices->getEnddate()->format('U'),
				'route' => $cruise_prices->getName(),
				'ship' => $cruise_prices->getShip()->getName(),
				'ship_img' => $cruise_prices->getShip()->getImgurl(),
				
		);
		return $cruise_description;
	}
	
	private function getProgramm($cruise_code)
	{
		$cruisesRepository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
			
		$cruise_programm = array(); 
		
		$cruise =  $cruisesRepository->findOneById($cruise_code);
		
		if($cruise == null)
		{
			return array('array' => json_encode(array('error' => "Продажи путевок на выбранный тур завершены")));
		}
		
		foreach($cruise->getPrograms() as $programmItem)
		{
			$cruise_programm[] = array(
					
					'date_start' => $programmItem->getDateStart()->format('U'),
					'date_stop' => $programmItem->getDateStop()->format('U'),
					'place' => $programmItem->getPlace()->getName(),
					'description' => $programmItem->getDescription(),
					
					);
			
		}
		
		return $cruise_programm;
	}
	
	
	private function getPrices($cruise_code)
	{
		$cruisesRepository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
		
		$cruise = $cruisesRepository->findOneById($cruise_code);
		
		$cruise_prices = $cruisesRepository->findOneApiByCode($cruise_code);
		$prices = $cruise_prices->getPrices();
		
		$available_rooms = $this->get('cruise')->getRoomsIdArray($cruise->getId());
		

		
		//dump($available_rooms);
		
		
		$roomDiscounts = $this->getDoctrine()->getRepository("CruiseBundle:RoomDiscount")->findByCruise($cruise);
		foreach($roomDiscounts as  $roomDiscount)
		{
			$active_rooms[] = $roomDiscount->getRoom()->getId();
		}	
				
				
		foreach($prices as $price)
		{ 
			
			foreach($price->getCabin()->getRooms() as $room)
			{

				$is_happy_kayuta = 0;
				$is_special_kayuta = 0;
				
				
				if(in_array( $room->getId(), $available_rooms))
				{
					$available_room = 1;
				}
				else
				{
					 $available_room = 0;
				}
				
				$price_koeff = $price->getPrice();
				
				if($cruise->getTypeDiscount() !== null)
				{
					if(in_array($room->getId() , $active_rooms))
					{
						//$available_room = 1;
						$koef = (100 - $cruise->getTypeDiscount()->getValue()) / 100; 
						$price_koeff *= $koef;
						
						$is_special_kayuta = $cruise->getTypeDiscount()->getCode() == 'special' ? 1 : 0 ;
						$is_happy_kayuta = $cruise->getTypeDiscount()->getCode() == 'happy' ? 1 : 0 ;
						
					}
					
					
					
				}
				
				
				
				
				
				$rooms[$room->getNumber()][$price->getPlace()->getRpId()][$price->getTariff()->getId()] = array(
						
						'deck' => $price->getCabin()->getDeck()->getName(),
						'room_type' => $price->getCabin()->getType()->getName(),
						'number' => $room->getNumber(),
						'count_place' => $price->getPlace()->getRpId(),
						'name_place' => $price->getPlace()->getRpName(),
						'tariff_name' => $price->getTariff()->getName(),
						'tariff_id' => $price->getTariff()->getId(),
						'price_old' =>$price->getPrice(),
						'price' => $price_koeff,
						'is_happy' => $is_happy_kayuta,
						'is_special' => $is_special_kayuta,
						'available_rooms' => $available_room,
						);
			}
			
			
		}
		
		return $rooms;
		
	}
	
	
    /**
	 * @Template()
	 * @Route("/cruise/{cruise_code}/{pre}", name="api_v1_json_cruise" )
     */			
	public function kautaAction($cruise_code, $pre = false)
	{
		
		$cruisesRepository = $this->getDoctrine()->getRepository('CruiseBundle:Cruise');
		$cruise = $cruisesRepository->findOneById($cruise_code);
		if(null === $cruise)
		{
			return new Response("Круиз с таким ID не найден.",404);
		}

		if($pre) return array('array' => '<pre>'.print_r(array('cruise' => $this->getDescription($cruise_code) ,'programm' => $this->getProgramm($cruise_code) , 'prices' => $this->getPrices($cruise_code),),1).'</pre>');
		
		return array('array' => json_encode(array('cruise' => $this->getDescription($cruise_code) ,'programm' => $this->getProgramm($cruise_code) , 'prices' => $this->getPrices($cruise_code),)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:kauta.html.twig")
	 * @Route("/prices/{cruise_code}/{pre}", name="api_v1_json_prices" )
     */			
	public function pricesAction($cruise_code, $pre = false)
	{
		if($pre) return array('array' => '<pre>'.print_r(  $this->getPrices($cruise_code) ,1).'</pre>');
		
		return array('array' => json_encode($this->getPrices($cruise_code)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:kauta.html.twig")
	 * @Route("/timetable/{cruise_code}/{pre}", name="api_v1_json_timetable" )
     */			
	public function timetableAction($cruise_code, $pre = false)
	{
		if($pre) return array('array' => '<pre>'.print_r(  $this->getProgramm($cruise_code) ,1).'</pre>');
		
		return array('array' => json_encode($this->getProgramm($cruise_code)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:booking.html.twig")
	 * @Route("/booking_del/{agency_code}/{auth}", name="api_v1_json_booking_del" )
     */			
	public function bookingDelAction( $agency_code=null, $auth = "", Request $request )
	{
		$em = $this->getDoctrine()->getManager();		
		// проверка агентства
		
		$agency = $em->getRepository('CruiseBundle:Agency')->findOneBy(['id'=>$agency_code, 'auth'=>$auth]);
		
		
		//return $this->render("dump.html.twig",['dump'=>$agency]);
		
		if(null === $agency)
		{
			$error[] = "Такого агентства нет или неправильный код авторизации";
			return ['json'=>json_encode(['error'=>$error])];			
		}
	
		$json = $request->getContent();
		$arr  = json_decode($json, true );		

		
		
		
		$schet_id = $arr['schet_id'];
		
		if($schet_id == "")
		{
			$error[] = "Нет номера счёта";
			return ['json'=>json_encode(['error'=>$error])];			
		}
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($schet_id);
		
		// найти счёт от этого агентства
		
		if(null === $order)
		{
			$error[] = "Такого счёта нет";
			return ['json'=>json_encode(['error'=>$error])];			
		}
		
		if($order->getAgency() !== $agency)
		{
			$error[] = "Счёт создан другим пользователем";
			return ['json'=>json_encode(['error'=>$error])];				
		}
		
		$order->setActive(false);
		$em->flush();
	
		
		return ['json'=>json_encode(['schet'=>$schet_id])];
	}

	
    /**
	 * @Template()
	 * @Route("/booking/{agency_code}/{auth}", name="api_v1_json_booking" )
     */			
	public function bookingAction( $agency_code=null, $auth = false, Request $request )	
	{
		$error = [];
		$em = $this->getDoctrine()->getManager();		
		// проверка агентства
		
		$agency = $em->getRepository('CruiseBundle:Agency')->findOneBy(['id'=>$agency_code, 'auth'=>$auth]);
		
		if(null === $agency)
		{
			return ['json'=>json_encode(['error'=>['Отказ в авторизации']])];
		}


		
		$json = $request->getContent();
		
		
		//$json = '{"cruise_id":1004163,"rooms":{"103":[{"tariff_id":10,"name":"\u0418\u0432\u0430\u043d","surname":"\u0418\u0432\u0430\u043d\u043e\u0432","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u0438\u0447","birthday":"1945-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"},{"tariff_id":11,"name":"\u0418\u0432\u0430\u043d","surname":"\u0418\u0432\u0430\u043d\u043e\u0432","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u0438\u0447","birthday":"1945-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"},{"tariff_id":10,"name":"\u041c\u0430\u0440\u0438\u044f","surname":"\u0418\u0432\u0430\u043d\u043e\u0432\u0430","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u043d\u0430","birthday":"1955-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"}]}}';
		
		
		$arr  = json_decode($json, true );

		if(!isset($arr['cruise_id']))
		{
			return ['json'=>json_encode(['error'=>["Нет круиза в звпросе"]])];
		}	
		
		$cruise_id = $arr['cruise_id'];
		$rooms = $arr['rooms'];
		if(count($rooms) == 0)
		{
			$error[] = "Нет выбранных кают";
		}

		$cruise = $em->createQueryBuilder()
				->select('c,td')
				->from("CruiseBundle:Cruise",'c')
				->leftJoin('c.typeDiscount','td')
				->where('c.id = '.$cruise_id)
				->getQuery()
				->getOneOrNullResult()
			;
		if(null === $cruise) return ['json'=>json_encode(['error'=>["Нет такого круиза"]])];
		
		$typeDiscount = $cruise->getTypeDiscount();
		
		if(null !== $typeDiscount)
		{
			$discounKoef = (100 - $typeDiscount->getValue())/100;
		}
		else
		{
			$discounKoef = 1;
		}
		
		$availibeRooms = $this->get('cruise')->getRoomsArray($cruise_id);
	
	
		//return new Response($json);
		
		foreach($rooms as $number=>$room)
		{
			if(!array_key_exists($number, $availibeRooms))
			{
				$error[] = "Каюта $number занята в круизе $cruise_id";
			}

		}
		
		if(count($error) != 0)
		{
			return ['json'=>json_encode(['error'=>$error])];
		}
		else // нет ошибок
		{
			$mainTypePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("main");
			$order = new Ordering();
			$order
					->setFee($agency->getFee())
					->setCruise($cruise)
					->setAgency($agency)
					->setSesonDiscount(null)
					->setUser($agency->getUsers()[0])
				;
			
			foreach($rooms as $number=>$room)
			{
				
				$place = $em->getRepository("CruiseBundle:ShipCabinPlace")->findOneByRpId(count($room));
				
				//$meals = $em->getRepository("CruiseBundle:Meals")->findOneByName("");
				
				$orderItem = new OrderItem();
				$orderItem
						->setOrdering($order)
						->setRoom($availibeRooms[$number])
						->setTypeDiscount( $availibeRooms[$number]->discount === null ? null : $typeDiscount )
						->setPlace($place)
					;
				$em->persist($orderItem);
				$order->addOrderItem($orderItem);
				foreach($room as $placeItem)
				{
					// плучить прайс зная тариф
					$tariff = $em->getRepository("CruiseBundle:Tariff")->findOneById($placeItem['tariff_id']);
					$priceParameters = [
									'place'=>$place,
									'cabin'=>$availibeRooms[$number]->getCabin(),
									'tariff'=>$tariff,
									'cruise'=>$cruise
								];
					if(isset($placeItem['meals_id']))
					{
						$meals = $em->getRepository("CruiseBundle:Meals")->findOneById($placeItem['meals_id']);
						$priceParameters['meals'] = $meals;
					}
					
					$price = $em->getRepository("CruiseBundle:Price")->findOneBy($priceParameters);
					
					$orderItemPlace = new OrderItemPlace();
					$orderItemPlace
							->setOrderItem($orderItem)
							->setName($placeItem['name'])
							->setLastName($placeItem['surname'])
							->setFatherName($placeItem['patronymic'])
							->setBirthday(new \DateTime($placeItem['birthday']))
							->setPassSeria($placeItem['pass_seria'])
							->setPassNum($placeItem['pass_num'])
							->setPassDate(new \DateTime($placeItem['pass_date']))
							->setPassWho($placeItem['pass_who'])
							->setPrice($price)
							->setTypePlace($mainTypePlace)
						;
					$orderItem->addOrderItemPlace($orderItemPlace);
					$em->persist($orderItemPlace);
				}
			}
			$em->persist($order);
		}
		$em->flush();
		
		//осталось отдать счёт и посадочные талоны
		
		return ['json'=>json_encode([
					'schet_id'=>$order->getId(),
					'schet_url'=>$this->generateUrl('invoice_agency',['hash'=>$order->getIdHash()], UrlGeneratorInterface::ABSOLUTE_URL),
					//'place_urls'=>$place_hash
					])];
		
		

	}
	
}
