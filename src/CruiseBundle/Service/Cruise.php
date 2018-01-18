<?php
namespace CruiseBundle\Service;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
//use Doctrine\ORM\EntityManager;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Symfony\Component\Config\Definition\Exception\Exception;
//use Symfony\Component\DependencyInjection\Container;

class Cruise
{

    private $doctrine;


    public function __construct($doctrine, $mailer)
    {
        $this->doctrine = $doctrine;
        $this->mailer = $mailer;
    }

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

	public function getRoomsArray($cruise_id)
	{
		$rooms = $this->getRooms($cruise_id);
		$roomsArr = [];
		foreach($rooms as $room)
		{
			$roomsArr[$room->getNumber()] = $room;
		}
		
		return $roomsArr;
	}
	
	public function getRoomsIdArray($cruise_id)
	{
		$rooms = $this->getRooms($cruise_id);
		
		$roomsArr = [];
		foreach($rooms as $room)
		{
			$roomsArr[$room->getNumber()] = $room->getId();
		}
		
		return $roomsArr;
	}
	
	public function getRooms($cruise_id)
	{
		
		$em = $this->doctrine->getManager();
		
		// все каюты
		$cruise = $em->createQueryBuilder()
			->select('cruise,ship,cabin,room')
			//->addSelect('room.number+0 as HIDDEN room_number')
			->from('CruiseBundle:Cruise','cruise')
			->leftJoin('cruise.ship','ship')
			->leftJoin('ship.cabin','cabin')
			->leftJoin('cabin.rooms','room')
			->where('cruise.id = '.$cruise_id)
			->orderBy('room.number+0')
			->getQuery()
			->getOneOrNullResult()
		;	
		
		// купленные каюты	
		// заказанные 		
		$orderingRooms = $em->createQueryBuilder()
			->select('o,oi')
			->from('CruiseBundle:Ordering','o')
			->leftJoin('o.orderItems','oi')
			->where('o.cruise ='.$cruise_id)
			//->andWhere('o.paid = 1')
			->andWhere('o.active = 1')

			->getQuery()
			->getResult()
		;
		// создаём массив кают
		
		//dump($orderingRooms);
		
		$occupied_rooms = [];
		foreach($orderingRooms as $orderingRoom)
		{
			foreach($orderingRoom->getOrderItems() as $orderItem)
			{
				$occupied_rooms[] = $orderItem->getRoom();
			}
		}

		// доступные из водохода каюты
		$available_rooms = $this->getAvailibleRooms($cruise);
		

		// каюты со скидками
		$roomDiscounts = $this->doctrine->getRepository("CruiseBundle:RoomDiscount")->findByCruise($cruise);
		$discount = $cruise->getTypeDiscount();
		$discount_rooms = [];
		if(null !== $discount)
		{
			foreach($roomDiscounts as  $roomDiscount)
			{
				$discount_rooms[] = $roomDiscount->getRoom();
			}				
		}		

		$rooms = [];
		
		foreach($cruise->getShip()->getCabin() as $cabin)
		{
			foreach($cabin->getRooms() as $room)
			{
				if(in_array($room,$occupied_rooms))
				{
					continue;
				}
				$room->discount = false;
				if(in_array($room,$discount_rooms))
				{
					$room->discount = true;
					$discountInCabin = true;
					$rooms[] = $room;
				}
				elseif(in_array($room->getNumber(),$available_rooms))
				{
					$rooms[] = $room;
				}				
			}
		}
		
		return $rooms;
	}
	
	
	public function createOrderMosturflot($order, $fullOrder = false)
	{
		$request = [];		
		// проверить создал ли счёт у мостурфлота 
		if(null === $order->getOuterId())
		{
			//dump("create");
			$action = 'ordercreate';
		}
		else
		{
			//dump("edit");
			$action = 'orderedit';
			$request['orderid'] = $order->getOuterId();
		}
		
		//$request['test'] = 1;
		$request['ordertourid'] = $order->getCruise()->getId() - 1000000;

		$url = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&request=ship&cabins=1&shipid=".($order->getCruise()->getShip()->getId() - 1000);
		
		$tariff_assocc = [
			10 => 1,
			11 => 2,
			6  => 3,
			7  => 5,
			8  => 4,
			9  => 6,
		];
		
		$type_docs_assocc = [
			1 => 0,
			2 => 4,
			3 => 3,
		];
		
		$xml = simplexml_load_string($this->curl_get_file_contents($url));

		//dump($xml->answer->shipcabins);
		
		foreach($order->getOrderItems() as $orderItem)
		{
			//dump( $orderItem);
			
			$cabin_id = null;
			$room_num = $orderItem->getRoom()->getNumber();
			
			foreach($xml->answer->shipcabins->item as $room_mosturflot)
			{
				//dump($room_mosturflot);
				//dump($room_num);
				
				
				if($room_mosturflot->cabinnumber == $room_num)
				{
					$cabin_id = (int)$room_mosturflot->cabinid;
				}
			}
			//dump($cabin_id);

			if(null === $cabin_id)
			{
				return null;
			}
			
			
			foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
			{
				
				$passanger = [
						'cabinid' => $cabin_id,
						'berthtype' => "main",
						'tariffid' => $tariff_assocc[$orderItemPlace->getPrice()->getTariff()->getId()] ,
						'mealid' => $orderItemPlace->getPrice()->getMeals()->getId() - 1,
						];
				if($fullOrder)
				{
					$passanger['passenger'] = 
					[
						'cpname' => $orderItemPlace->getLastName() . " ". $orderItemPlace->getName() . " " . $orderItemPlace->getFatherName(),
						'cpgender' => $orderItemPlace->getGender()->getId(),
						'cpdoctype' => $type_docs_assocc[$orderItemPlace->getTypeDoc()->getId()],
						'cpdocser' => $orderItemPlace->getPassSeria(),
						'cpdocnum' => $orderItemPlace->getPassNum(),
						'cpdocby' => $orderItemPlace->getPassWho(),
						'cpdocdate' => $orderItemPlace->getPassDate()->format("c"),
						'cpcitizenship' => 185,
						'cpbirthdate' => $orderItemPlace->getBirthday()->format("c"),					
					];
				}
				$request['orderpassengers'][] = $passanger;
				
				//dump( $orderItemPlace);
				/*
				$request['orderpassengers'][] = [
						'cabinid' => $cabin_id,
						'berthtype' => "main",
						'tariffid' => $tariff_assocc[$orderItemPlace->getPrice()->getTariff()->getId()] ,
						'mealid' => $orderItemPlace->getPrice()->getMeals()->getId() - 1,
						'passenger' => [
										'cpname' => $orderItemPlace->getLastName() . " ". $orderItemPlace->getName() . " " . $orderItemPlace->getFatherName(),
										'cpgender' => $orderItemPlace->getGender()->getId(),
										'cpdoctype' => $type_docs_assocc[$orderItemPlace->getTypeDoc()->getId()],
										'cpdocser' => $orderItemPlace->getPassSeria(),
										'cpdocnum' => $orderItemPlace->getPassNum(),
										'cpdocby' => $orderItemPlace->getPassWho(),
										'cpdocdate' => $orderItemPlace->getPassDate()->format("c"),
										'cpcitizenship' => 185,
										'cpbirthdate' => $orderItemPlace->getBirthday()->format("c"),
									]
				];
				*/
			}
		}
		
		$url = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&request=$action&";
		
		
		//dump($request);
		//dump(http_build_query($request));
		
		$xml = simplexml_load_string($this->curl_get_file_contents($url.http_build_query($request)));
		
		//dump($xml);
		
		$outerId = (int)$xml->answer->orderid;
		
		$order->setOuterId($outerId);
		$this->doctrine->getManager()->flush();
		
		return null;
	}
	
	public function deleteOrderMosturflot($order)
	{
		
		if($order->getOuterId() !== null)
		{
			$url = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&request=ordercancel&orderid=".$order->getOuterId();
			$xml = simplexml_load_string($this->curl_get_file_contents($url));
			
			
			
			
			$order->setOuterId(null);
			$this->doctrine->getManager()->flush();
			
			
		}
		
		return null;
	}
	
	public function getAvailibleRooms($cruise)
	{
		$available_rooms = [];		
		// для водохода
		if($cruise->getShip()->getTuroperator()->getId() == 1)
		{
			$url = "http://cruises.vodohod.com/agency/json-prices.htm?pauth=jnrehASKDLJcdakljdx&cruise=".$cruise->getId();
			$rooms_json = $this->curl_get_file_contents($url);
			$rooms_v = json_decode($rooms_json,true);
			foreach($rooms_v['room_availability'] as $room_group_v)
			{
				foreach($room_group_v as $room_v)
				{
					$available_rooms[] = $room_v;
				}
			}
		}// для водохода
		
		
		// для мостурфлота
		if($cruise->getShip()->getTuroperator()->getId() == 2)
		{
			$url = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&request=tour&loading=true&tourid=". ($cruise->getId()-1000000);
			$xml = $this->curl_get_file_contents($url);
			foreach(simplexml_load_string($xml)->answer->tourloading->item as $item)
			{
				if($item->cabinstatus > 0)
				{
					$available_rooms[] = (string)$item->cabinnumber;
				}
			}
		}// для мостурфлота				
		
		// для инфофлота
		if($cruise->getShip()->getTuroperator()->getId() == 3)
		{
			$url = "http://api.infoflot.com/JSON/68a3d0c23cf277febd26dc1fa459787522f32006/CabinsStatus/".($cruise->getShip()->getId() - 2000)."/". ($cruise->getId()-2000000)."/";
			$ans = $this->curl_get_file_contents($url);
			$json = json_decode($ans,true);
			//dump($json);
			
			foreach($json as $item)
			{
				if($item['status'] == 0)
				{
					$available_rooms[] = $item['name'];
				}
			}
		}// для инфофлота				
		
		// для Гама
		if($cruise->getShip()->getTuroperator()->getId() == 4)
		{
			$url = "http://gama-nn.ru/execute/way/". ($cruise->getId()-3000000)."/";
			$xml = $this->curl_get_file_contents($url);
			
			$xml = simplexml_load_string($xml);
			foreach($xml->cabins->cabin as $item)
			{

					$available_rooms[] = (string)$item->attributes()['name'];
				
			}
		}// для Гама	
		
		return $available_rooms;
	}

	private $discounts_this_year = [1=>5,2=>5,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,];
	private $discounts_next_year = [1=>12,2=>12,3=>12,4=>12,5=>12,6=>11,7=>11,8=>11,9=>10,10=>9,11=>8,12=>7,];
	
	public function getSesonDiscount($order, $year_now = null)
	{
		$cruise = $order->getCruise();
		return $this->getSesonDiscountCruise($cruise, $year_now = null);		
	}
	
	public function  getSesonDiscountMosturflot($order)
	{
		$cruise = $order->getCruise();
		$url = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&request=tour&loading=true&tourid=". ($cruise->getId()-1000000);
		$xml = $this->curl_get_file_contents($url);
		//dump($xml);
		return (int)simplexml_load_string($xml)->answer->tourdiscount;
	}
	
	public function getSesonDiscountCruise($cruise, $year_now = null)
	{
		if(null == $year_now) $year_now = date('Y');
		$year_tur = $cruise->getStartDate()->format("Y");
		if(($year_tur != null) && ( $year_tur > $year_now ) )
		{
			return $this->discounts_next_year[date('n')];
		}	
		return $this->discounts_this_year[date('n')];		
	}	
	
	public function deleteOrder($order)
	{
		if($order)
		{
			$em = $this->doctrine->getManager();
			$order->setActive(false);
			$em->flush();
		}
		
		if($order->getCruise()->getTuroperator()->getCode() == 'mosturflot')
		{
			$this->deleteorderMosturflot($order);
		}
		
		$message = \Swift_Message::newInstance()
							->setSubject('Удаление заявки '.$order->getId())
							->setFrom('test-rech-agent@yandex.ru')
							->setTo(['dkochetkov@vodohod.ru','info@rech-agent.ru','spb@rech-agent.ru'])
							->setBody("Заявка № ".$order->getId()." удалена")
						;
        $this->mailer->send($message);		
	}
	
	public function getOrderPrice($order)
	{
		$em = $this->doctrine->getManager();
		
		$order = $em->createQueryBuilder()
			->select('o,oi,oip,price,room,cabin,cabin_type,cabin_deck,typeDiscount,pay')
			->from('CruiseBundle:Ordering','o')
			->leftJoin('o.orderItems','oi')
			->leftJoin('o.pays','pay')
			->leftJoin('oi.orderItemPlaces','oip')
			->leftJoin('oi.room' , 'room')
			->leftjoin('room.cabin','cabin')
			->leftJoin('cabin.prices','price')
			->leftJoin('cabin.type','cabin_type')
			->leftJoin('cabin.deck','cabin_deck')
			->leftJoin('oi.typeDiscount','typeDiscount')
			->where('o.id = '.$order->getId())
			->andWhere('price.place = oi.place')
			->andWhere('price.cruise = o.cruise')
			->getQuery()
			->getOneOrNullResult()
		;

		// создать массив заказа со всеми суммами и скидками
		$items = [];
		$itogo = [		'price' => 0,
						'discount' => 0,
						'priceDiscount' => 0,
						'fee_summ' => 0,
						'pay' =>0,
						'tariffs' => []
						];
						
						
		if(null !== $order)	
		{
			foreach($order->getOrderItems() as $orderItem)
			{
				

				foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
				{
					$price = $orderItemPlace->getPriceValue();
					$seson = $order->getSesonDiscount();
					$permanent = $order->getPermanentDiscount();
					
					$surcharge = $orderItemPlace->getSurcharge();
					
					$price += + $surcharge;
					
					$priceDiscount = round( $price * (100 - $seson) * (100 - $permanent) /10000) ;
					$discount = $price - $priceDiscount;
						
					
					$fee = $order->getFee();
					$fee_summ = $fee * $priceDiscount / 100;			
					
					$items[] = [
							'name' => $order->getCruise()->getName().', ' .$order->getCruise()->getStartDate()->format('d.m.Y'). ' - ' .$order->getCruise()->getEndDate()->format('d.m.Y'). ', '.$order->getCruise()->getShip()->getName().', каюта '.$orderItem->getRoom()->getNumber() ,
							'price' => $price,
							'seson' => $seson,
							'permanent' => $permanent,
							'discount' => $discount,
							'priceDiscount' => $priceDiscount,
							'fee' => $fee,
							'fee_summ' => $fee_summ,
							'number' => $orderItem->getRoom()->getNumber(),
							'cabinType' => $orderItem->getRoom()->getCabin()->getType()->getComment(),
							'cabinDeck' => $orderItem->getRoom()->getCabin()->getDeck()->getName(),
							'orderItemPlace' =>$orderItemPlace,
							
							
						];
					$itogo['price'] += 	$price ;
					$itogo['discount'] += 	$discount;
					$itogo['priceDiscount'] += 	$priceDiscount;
					$itogo['fee_summ'] += 	$fee_summ;
					
					
					if(null !== $orderItemPlace->getPrice())
					{
						isset($itogo['tariffs'][$orderItemPlace->getPrice()->getTariff()->getName()]) ? $itogo['tariffs'][$orderItemPlace->getPrice()->getTariff()->getName()]++ : $itogo['tariffs'][$orderItemPlace->getPrice()->getTariff()->getName()] = 1;
					}
						
				}
			}
			$itogo['pays'] = $order->getPays();
			foreach($order->getPays() as $key => &$pay)
			{
				if(!$pay->getIsDelete())
				{
					$itogo['pay'] += $pay->getAmount();
				}
				else
				{
					unset($order->getPays()[$key]);
				}
			}			
		}


		return ['order'=>$order, 'items'=>$items, 'itogo'=>$itogo];
	}
	
	
	public function hashOrderEncode($id)
	{
		$schet_salt = '5bxhjV7R4vBQJKk8NFqPCQCCnrUKvDVu';
		$hashids_schet = new \Hashids\Hashids($schet_salt,32);
		return $hashids_schet->encode($id);
	}
	
	public function hashOrderDecode($id)
	{
		$schet_salt = '5bxhjV7R4vBQJKk8NFqPCQCCnrUKvDVu';
		$hashids_schet = new \Hashids\Hashids($schet_salt,32);
		//dump($hashids_schet->decode($id));
		return $hashids_schet->decode($id)[0];
	}	
	
	public function hashPlaceEncode($id)
	{
		$place_salt = 'c56CC5enUbrGKWn8x3FhXkZbHaMk6Ms7Mm3KdTHY';
		$hashids_place = new \Hashids\Hashids($place_salt,32);
		return $hashids_place->encode($id);
	}
	
	public function hashPlaceDecode($id)
	{
		$place_salt = 'c56CC5enUbrGKWn8x3FhXkZbHaMk6Ms7Mm3KdTHY';
		$hashids_place = new \Hashids\Hashids($place_salt,32);
		return $hashids_place->decode($id)[0];
	}
	
}	