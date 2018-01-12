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

class ApiController extends Controller
{
    
	/**
	 * @Template()
	 * @Route("/api/json", name="api_json" )
     */	
	public function jsonAction()
	{
		return [];
	}
	
	/**
	 * @Template()
	 * @Route("/api/json/cruises/{pre}", name="api_json_cruises" )
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
	 * @Route("/api/json/kauta/{cruise_code}/{pre}", name="api_json_kauta" )
     */			
	public function kautaAction($cruise_code, $pre = false)
	{
		if($pre) return array('array' => '<pre>'.print_r(array('cruise' => $this->getDescription($cruise_code) ,'programm' => $this->getProgramm($cruise_code) , 'prices' => $this->getPrices($cruise_code),),1).'</pre>');
		
		return array('array' => json_encode(array('cruise' => $this->getDescription($cruise_code) ,'programm' => $this->getProgramm($cruise_code) , 'prices' => $this->getPrices($cruise_code),)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:kauta.html.twig")
	 * @Route("/api/json/prices/{cruise_code}/{pre}", name="api_json_prices" )
     */			
	public function pricesAction($cruise_code, $pre = false)
	{
		if($pre) return array('array' => '<pre>'.print_r(  $this->getPrices($cruise_code) ,1).'</pre>');
		
		return array('array' => json_encode($this->getPrices($cruise_code)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:kauta.html.twig")
	 * @Route("/api/json/timetable/{cruise_code}/{pre}", name="api_json_timetable" )
     */			
	public function timetableAction($cruise_code, $pre = false)
	{
		if($pre) return array('array' => '<pre>'.print_r(  $this->getProgramm($cruise_code) ,1).'</pre>');
		
		return array('array' => json_encode($this->getProgramm($cruise_code)));
	}
	
    /**
	 * @Template("CruiseBundle:Api:booking.html.twig")
	 * @Route("/api/json/booking_del/{agency_code}/{auth}", name="api_json_booking_del" )
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
	
		$json = $request->request->get('json');
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
	 * @Route("/api/json/booking/{agency_code}/{auth}", name="api_json_booking" )
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


		
		$json = $request->request->get('json');
		
		
		//$json = '{"cruise_id":1004163,"rooms":{"103":[{"tariff_id":10,"name":"\u0418\u0432\u0430\u043d","surname":"\u0418\u0432\u0430\u043d\u043e\u0432","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u0438\u0447","birthday":"1945-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"},{"tariff_id":11,"name":"\u0418\u0432\u0430\u043d","surname":"\u0418\u0432\u0430\u043d\u043e\u0432","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u0438\u0447","birthday":"1945-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"},{"tariff_id":10,"name":"\u041c\u0430\u0440\u0438\u044f","surname":"\u0418\u0432\u0430\u043d\u043e\u0432\u0430","patronymic":"\u0418\u0432\u0430\u043d\u043e\u0432\u043d\u0430","birthday":"1955-05-05","pass_seria":"1233","pass_num":"123456","pass_date":"2000-05-05","pass_who":"\u043a\u0435\u043c \u0432\u044b\u0434\u0430\u043d"}]}}';
		
		
		$arr  = json_decode($json, true );
		
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
		
		
		return $this->render("dump.html.twig",['dump'=>[
						/*$agency,*/
						$rooms,
						/*$cruise,
						$typeDiscount,
						$discounKoef,
						$availibeRooms,*/
						$order,
						]]);
		
		


		$ship = $cruise->getShip();
		
		if(count($rooms) == 0)
		{
			$error[] = "Нет выбранных кают";
		}
		else 
		{


			
			///  проверка агентства
			$sql="
			SELECT `aa_agent`.*,jdr8t_users.name FROM `aa_agent`
			
			LEFT JOIN jdr8t_users ON jdr8t_users.id = aa_agent.user_id
			
			WHERE `aa_agent`.`user_id` = ".$agency_code."
			
			";
			$statement = $connection->prepare($sql);
			$statement->execute();
			$agency = $statement->fetch();	
			$count_agency = $statement->rowCount();
			
			
			if($count_agency != 1) 
			{
				$error[] = "Такого агентства нет или неправильный код авторизации";
			}

			else
			{
				foreach($rooms as $num=>$places)
				{
				
					/// проверка возможности бронирования
					$sql="
					SELECT * FROM `aa_tur`
					LEFT JOIN `aa_discount` ON `aa_tur`.`id` = `aa_discount`.`id_tur`
					WHERE `aa_tur`.`id` = $cruise_code
					AND `aa_discount`.`num` = $num
					";
					$statement = $connection->prepare($sql);
					$statement->execute();
					$results = $statement->fetchAll();					
					$count_discount = $statement->rowCount();
					
					if($count_discount == 1) 
					{	
						$sql2="
						SELECT * FROM `aa_schet`
						LEFT JOIN `aa_order` ON `aa_order`.`id_schet` = `aa_schet`.`id`
						WHERE `aa_schet`.`id_tur` = $cruise_code
						AND `aa_schet`.`status` = 1
						AND `aa_order`.`num` = $num
						";				
						$statement = $connection->prepare($sql2);
						$statement->execute();
						$results2 = $statement->fetchAll();	
						$count_order = $statement->rowCount();

						if($count_order == 1) $error[] = "Каюта $num занята в круизе $cruise_code";
					}
					else 
					{
						$error[] = "Нет каюты $num со скидкой в этом круизе";
					}
				}
				
			}
		}		

		// тут сделаны все проверки и можно создать заказ
		if(count($error) == 0)
		{
			
			// счёт у нас один
			$sql = "
			INSERT INTO `aa_schet` 
			(`fee`, `user_id`, `owner`, `status`, `buyer`, `id_tur`, `comment_manager`, `comment_user`, `timecreate`, `permanent`, `permanent_request`, `seson_discount`, `fee_pos`)
			VALUES
			(".$agency['fee'].", ".$agency['user_id'].", ".$agency['user_id'].", 1, 'ur', $cruise_code, '','', NOW(), NULL, NULL, NULL, ".$agency['fee_pos']."  )
			";
			$statement = $connection->prepare($sql);
			$statement->execute();
			

			// тут нужно вернуть номер получившегося счёта
			$schet_id = $connection->lastInsertId();
			
			
			// создать пару записей для физ и юр лиц
			$sql = "INSERT INTO `aa_buyer_fiz` (`id`, `id_schet`, `name`, `surname`, `patronymic`, `address`, `birthday`, `pass_seria`, `pass_num`, `pass_date`, `pass_who`, `phone`, `email`, `timecreate`) VALUES (NULL, $schet_id, '', '', '', '', '0000-00-00', '', '', '0000-00-00', '', '', '', CURRENT_TIMESTAMP)";
			$statement = $connection->prepare($sql);
			$statement->execute();

			
			$sql = "
			INSERT INTO `aa_buyer_ur`
			(`id`, `id_schet`, `name`, `bank`, `rs`, `ks`, `bik`, `inn`, `kpp`, `ur_address`, `fakt_address`, `phone`,`timecreate`) 
			SELECT NULL, $schet_id, '".$agency['name']."', `bank`, `rs`, `ks`, `bik`, `inn`, `kpp`, `ur_address`, `fakt_address`, `phone`, CURRENT_TIMESTAMP FROM aa_agent WHERE aa_agent.user_id = ".$agency['user_id']."
			";
			$statement = $connection->prepare($sql);
			$statement->execute();			
				
			foreach($rooms as $num=>$places)
			{
				$countPlaces = count($places);
				
				// а вот каюты по orders 
				$sql = "
				INSERT INTO `aa_order`
				( `id_schet`, `num`, `places`, `is_delete`, `timecreate`) 
				VALUES 
				($schet_id, '$num', $countPlaces, 0, NOW() )
				";				
				$statement = $connection->prepare($sql);
				$statement->execute();
				
				// номера заказов по этому счёту
				$order_id = $connection->lastInsertId();
				
				// получим каюту 
				
				$sql = "SELECT cab 
					FROM BaseBundle\Entity\CruiseShipCabin cab
					LEFT JOIN cab.rooms r
					WHERE cab.ship = ".$ship->getId()."
					AND   r.roomNumber = '".$num."'
					";
				$q = $em->createQuery($sql);
				//$q->setParameter(2, $cruise_code);
				$cab = $q->getOneOrNullResult();				
				
				 
				
				foreach($places as $place)
				{
					/// узнать цену 
					
					if(!isset($place['tariff_id'])) 
						return ['json'=>json_encode(['error'=>["Не указан тариф"]])];
					
					$name           = isset($place['name']) ? $place['name'] : "";
					$surname        = isset($place['surname']) ? $place['surname'] : "";
					$patronymic     = isset($place['patronymic']) ? $place['patronymic'] : "";
					$birthday       = isset($place['birthday']) ? $place['birthday'] : "0000-00-00";
					$pass_seria     = isset($place['pass_seria']) ? $place['pass_seria'] : "";
					$pass_num       = isset($place['pass_num']) ? $place['pass_num'] : "";
					$pass_date      = isset($place['pass_date']) ? $place['pass_date'] : "0000-00-00";
					$pass_who       = isset($place['pass_who']) ? $place['pass_who'] : "";
					
					
					$sql = "SELECT price 
						FROM BaseBundle\Entity\CruiseShipCabinCruisePrice price
						WHERE price.rp_id = ".$countPlaces."
						AND   price.cabin = ".$cab->getId()."
						AND   price.cruise = ".$cruise->getId()."
						AND   price.tariff = ".$place['tariff_id']."
						AND   price.meals = 1
						";
					$q = $em->createQuery($sql);
					//$q->setParameter(2, $cruise_code);
					$price = $q->getOneOrNullResult();					
					
					$sql = "
					INSERT INTO `aa_place`(`id_order`, `price`, `type_price`, `name`, `surname`, `patronymic`, `birthday`, `pass_seria`, `pass_num`, `pass_date`, `pass_who`, `timecreate`) 
					VALUES 
					($order_id, ".$price->getPrice() * $koeff.",0,'$name','$surname','$patronymic','$birthday','$pass_seria','$pass_num','$pass_date','$pass_who',NOW())
					";
				$statement = $connection->prepare($sql);
				$statement->execute();	

				$place_id = $connection->lastInsertId();
				$place_hash[] = "https://booking.rech-agent.ru/service/report/boarding_card.php?place=" .$hashids_place->encode($place_id);
				
				}
				
				

					
			}

		$schet_hash = "https://booking.rech-agent.ru/service/report/invoice.php?hash=".$hashids_schet->encode($schet_id);
			
			return ['json'=>json_encode(['schet_id'=>$schet_id,'schet_url'=>$schet_hash,'place_urls'=>$place_hash])];
		}
		else
		{
			return ['json'=>json_encode(['error'=>$error])];
		}
		
		
		
	}
	
}
