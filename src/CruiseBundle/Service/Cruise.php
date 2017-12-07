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


    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
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
		$orderingRooms = $em->createQueryBuilder()
			->select('o,oi')
			->from('CruiseBundle:Ordering','o')
			->leftJoin('o.orderItems','oi')
			->where('o.cruise ='.$cruise_id)
			->andWhere('o.paid = 1')

			->getQuery()
			->getResult()
		;
		// создаём массив кают
		$occupied_rooms = [];
		foreach($orderingRooms as $orderingRoom)
		{
			foreach($orderingRoom->getOrderItems() as $orderItem)
			{
				$occupied_rooms[] = $orderItem->getRoom();
			}
		}



		// доступные из водохода каюты
		$available_rooms = [];
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
		
		// берём ве каюты из $cruise
		
		// оставляем только те, которые разрешает водоход
		
		// добавляем скидочные
		
		// вычетаем купленные



		
	}
	


	private $discounts_this_year = [1=>6,2=>5,3=>0,4=>0,5=>0,6=>0,7=>0,8=>0,9=>0,10=>0,11=>0,12=>0,];
	private $discounts_next_year = [1=>12,2=>12,3=>12,4=>12,5=>12,6=>11,7=>11,8=>11,9=>10,10=>9,11=>8,12=>7,];
	
	public function getSesonDiscount($order, $year_now = null)
	{
		if(null == $year_now) $year_now = date('Y');
		
		$year_tur = $order->getCruise()->getStartDate()->format("Y");
		
		if(($year_tur != null) && ( $year_tur > $year_now ) )
		{
			return $this->discounts_next_year[date('n')];
		}	
		
		return $this->discounts_this_year[date('n')];		
	}	
	
	public function getOrderPrice($order)
	{
		$em = $this->doctrine->getManager();
		
		$order = $em->createQueryBuilder()
			->select('o,oi,oip,price,room,cabin,typeDiscount,pay')
			->from('CruiseBundle:Ordering','o')
			->leftJoin('o.orderItems','oi')
			->leftJoin('o.pays','pay')
			->leftJoin('oi.orderItemPlaces','oip')
			->leftJoin('oi.room' , 'room')
			->leftjoin('room.cabin','cabin')
			->leftJoin('cabin.prices','price')
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
						];
						
		foreach($order->getOrderItems() as $orderItem)
		{
			

			foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
			{
				$price = $orderItemPlace->getPriceValue();
				$seson = $order->getSesonDiscount();
				$permanent = $order->getPermanentDiscount();
				$discount = round($price - $price * (100 - $seson) * (100 - $permanent) /10000) ;
				
				$priceDiscount = $price - $discount;
				
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
						
					];
				$itogo['price'] += 	$price;
				$itogo['discount'] += 	$discount;
				$itogo['priceDiscount'] += 	$priceDiscount;
				$itogo['fee_summ'] += 	$fee_summ;
					
			}
		}
		$itogo['pays'] = $order->getPays();
		foreach($order->getPays() as $pay)
		{
			$itogo['pay'] += $pay->getAmount();
		}

		return ['order'=>$order, 'items'=>$items, 'itogo'=>$itogo];
	}
	
}	