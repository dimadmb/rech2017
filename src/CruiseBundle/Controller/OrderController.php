<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use CruiseBundle\Entity\Pay;
use CruiseBundle\Entity\Buyer;
use CruiseBundle\Entity\ShipRoom;
use CruiseBundle\Entity\Ordering;
use CruiseBundle\Entity\OrderItem;
use CruiseBundle\Entity\OrderItemPlace;


use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use CruiseBundle\Form\BuyerType;
use CruiseBundle\Form\OrderItemType;
use CruiseBundle\Form\OrderingType;

use Hashids\Hashids;

use LoadBundle\Controller\Helper;

class OrderController extends Controller
{


	
	

    /**
	 * @Template("dump.html.twig")	
     * @Route("/test/{id}", name="test")
     */
    public function testAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		

		
		return $this->get('cruise')->getOrderPrice($order);
	}

    /**
	 * @Template("dump.html.twig")	
     * @Route("/order/pay/{hash}", name="pay")
     */
    public function payAction($hash)
	{
		
		$id = $this->get('cruise')->hashOrderDecode($hash);
		
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		
		// проверить водоход ли это и выставить скидку
		if(($order->getCruise()->getShip()->getTurOperator()->getCode() == 'vodohod') && ($order->getSesonDiscount() === null))
		{
			$order->setSesonDiscount($this->get('cruise')->getSesonDiscount($order));
			$this->getDoctrine()->getManager()->flush();
		}
		
		//return ['dump'=>$this->get('cruise')->getOrderPrice($order), $this->get('cruise')->getSesonDiscount($order) ];
		
		
	$href = "https://b2c.appex.ru/payment/choice?orderSourceCode=$id&billingCode=Rechnoeagentstvo003";

	return $this->redirect($href);
		
	}



	/**
     * @Route("/order/remove_room/{hash}/{orderitem}", name="invoice_remove_room")
     */
	public function invoiceRemoveRoomAction(Request $request,$hash,OrderItem $orderitem)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($this->get('cruise')->hashOrderDecode($hash));
		
		$em->remove($orderitem);
		
		
		
		//return new Response("OK");
		
		$em->flush();
		
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
	}
	
	/**
     * @Route("/order/add_room/{hash}", name="invoice_add_room")
     */
	public function invoiceAddRoomAction(Request $request,$hash)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($this->get('cruise')->hashOrderDecode($hash));
		$room = $em->getRepository("CruiseBundle:ShipRoom")->findOneById($request->query->get("room"));
		$place = $em->getRepository("CruiseBundle:ShipCabinPlace")->findOneById($request->query->get("place"));


		$typeDiscount = null;
		$discount = $em->getRepository("CruiseBundle:RoomDiscount")->findOneBy(['cruise'=>$order->getCruise(),'room'=>$room]) === null ? false : true;
		if($discount)
		{
			$typeDiscount = $order->getCruise()->getTypeDiscount();
		}
		
		$orderItem = new OrderItem();
		$orderItem->setRoom($room);
		$orderItem->setPlace($place);
		$orderItem->setOrdering($order);
		$orderItem->setTypeDiscount($typeDiscount);
		
		$em->persist($orderItem);	

		$order->addOrderItem($orderItem);
	
		for($i=1;$i<=$place->getRpId();$i++)
		{
			$orderItemPlace = new OrderItemPlace();
			$orderItemPlace->setOrderItem($orderItem);
			$em->persist($orderItemPlace);
			$orderItem->addOrderItemPlace($orderItemPlace);
		}
	
		$em->flush();
		
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
	}

	
	/**
	 * Удалить место из каюты
     * @Route("/order/remove_place/{hash}/{place}", name="invoice_remove_place")
     */
    public function invoiceRemovePlaceAction(Request $request,$hash,OrderItemPlace $place)
    {
		$em = $this->getDoctrine()->getManager();

		$em->remove($place);
		
		$orderItem  = $place->getOrderItem();
		
		// место на одно меньше
		$place = $em->getRepository("CruiseBundle:ShipCabinPlace")
						->findOneByRpId( $orderItem->getPlace()->getRpId() - 1 );
		$orderItem->setPlace($place);	


		// прайс на размещение ниже
		foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
		{
			$oldPrice = $orderItemPlace->getPrice();
			if(null !== $oldPrice)
			{
				$newPrice = $em->getRepository("CruiseBundle:Price")
							->findOneBy([
								'place' => $oldPrice->getPlace()->getRpId() - 1 ,
								'cabin' => $oldPrice->getCabin(),
								'cruise' => $oldPrice->getCruise(),
								'tariff' => $oldPrice->getTariff(),
								'meals' => $oldPrice->getMeals(),
							]);
				//dump($newPrice);			
				$orderItemPlace->setPrice($newPrice);		
				//$orderItemPlace->setPriceValue($newPrice->getPrice());		
			}

		}		
		
		$em->flush();
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
	}
	/**
	 * Добавить место в каюту	
     * @Route("/order/add_place/{hash}/{room}", name="invoice_add_place")
     */
    public function invoiceAddPlaceAction(Request $request,$hash,ShipRoom $room)
    {
		$em = $this->getDoctrine()->getManager();
		$orderItem = $em->createQueryBuilder()
			->select('oi,oip,price')
			->from('CruiseBundle:OrderItem','oi')
			->leftJoin('oi.orderItemPlaces','oip')
			->leftJoin('oip.price','price')
			->where('oi.ordering = '.$this->get('cruise')->hashOrderDecode($hash))
			->andWhere('oi.room = '.$room->getId())
			->getQuery()
			->getOneOrNullResult()
		;
		
		// место на одно больше 
		$place = $em->getRepository("CruiseBundle:ShipCabinPlace")
						->findOneByRpId( $orderItem->getPlace()->getRpId() + 1 );
		if(null === $place)
		{
			return $this->redirectToRoute('invoice', ['hash'=>$hash]);
		}
		
		$orderItem->setPlace($place);

		// прайс на размещение выше
		foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
		{
			$oldPrice = $orderItemPlace->getPrice();
			if(null !== $oldPrice)
			{
				$newPrice = $em->getRepository("CruiseBundle:Price")
							->findOneBy([
								'place' => $oldPrice->getPlace()->getRpId() + 1 ,
								'cabin' => $oldPrice->getCabin(),
								'cruise' => $oldPrice->getCruise(),
								'tariff' => $oldPrice->getTariff(),
								'meals' => $oldPrice->getMeals(),
							]);
							
				if(null === $newPrice)
				{
					return $this->redirectToRoute('invoice', ['hash'=>$hash]);
				}
				//dump($newPrice);			
				$orderItemPlace->setPrice($newPrice);		
				//$orderItemPlace->setPriceValue($newPrice->getPrice());		
			}

		}
		
		$newOrderItemPlace = new OrderItemPlace();
		$newOrderItemPlace->setOrderItem($orderItem);
		$em->persist($newOrderItemPlace);
		$orderItem->addOrderItemPlace($newOrderItemPlace);		
		
		$em->flush();
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
	}

	
    /**
	 * @Template()	
     * @Route("/order/{hash}", name="invoice")
     */
    public function invoiceAction(Request $request,$hash)
    {
		$session = new Session();
		
		$session->get('basket');	




		$em = $this->getDoctrine()->getManager();
		
		$order = $em->createQueryBuilder()
			->select('o,oi,oip,price,room,cabin')
			->from('CruiseBundle:Ordering','o')
			->leftJoin('o.orderItems','oi')
			->leftJoin('oi.orderItemPlaces','oip')
			->leftJoin('oi.room' , 'room')
			->leftjoin('room.cabin','cabin')
			->leftJoin('cabin.prices','price')
			->where('o.id = '.$this->get('cruise')->hashOrderDecode($hash))
			->andWhere('price.place = oi.place')
			->andWhere('price.cruise = o.cruise')
			->getQuery()
			->getOneOrNullResult()
		;
	
	
 
			// проверить водоход ли это и выставить скидку
			if(($order->getCruise()->getShip()->getTurOperator()->getCode() == 'vodohod') && ($order->getSesonDiscount() === null))
			{
				$order->setSesonDiscount($this->get('cruise')->getSesonDiscount($order));
				//$this->getDoctrine()->getManager()->flush();
			} 	
 
			// проверить мостурфлот ли это и выставить скидку
			if(($order->getCruise()->getShip()->getTurOperator()->getCode() == 'mosturflot') && ($order->getSesonDiscount() === null))
			{
				//dump("");
				$order->setSesonDiscount($this->get('cruise')->getSesonDiscountMosturflot($order));
				//$this->getDoctrine()->getManager()->flush();
			} 
			// если агентство - ставим комиссию
			if(($order->getAgency() !== null) && ($order->getFee() === null))
			{
				$order->setFee($order->getAgency()->getFee());
				//dump($order->getAgency()->getFee());
				$this->getDoctrine()->getManager()->flush();
			}
			
			
			if(($order->getBuyer() === null) && ($order->getAgency() === null))
			{
				$buyer = new Buyer();
				$em->persist($buyer);
				
				$order->setBuyer($buyer);
			}
				

			
			
 
		$is_manager = $this->get('security.authorization_checker')->isGranted('ROLE_MANAGER') ? true : false;
		
        $editForm = $this->createForm(OrderingType::class, $order, ['is_manager'=>$is_manager])	;

        $editForm->handleRequest($request);
		
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            
		
			
			$this->getDoctrine()->getManager()->flush();
        }
		
		
		/// проверка заполнения свех полей для оплаты
		$allow_order = true;
		$allow_pay = true;
		foreach($order->getOrderItems() as $orderItem)
		{
			foreach($orderItem->getOrderItemPlaces() as $orderItemPlace)
			{
				if(
					$orderItemPlace->getPrice() === null
				or 	$orderItemPlace->getName() === null
				or 	$orderItemPlace->getLastName() === null
				or 	$orderItemPlace->getFatherName() === null
				or 	$orderItemPlace->getBirthday() === null
				or 	$orderItemPlace->getPassSeria() === null
				or 	$orderItemPlace->getPassNum() === null
				or 	$orderItemPlace->getPassDate() === null
				or 	$orderItemPlace->getPassWho() === null
				or 	$orderItemPlace->getGender() === null

				)
				{

					$allow_pay = false;
					//$allow_order = false;
				}
				if($orderItemPlace->getPrice() === null)
				{
					$allow_order = false;
				}
				
				
			}
			// ищем другие варианты размещений 
			$prices = $em->createQueryBuilder()
					->select("price")
					->from("CruiseBundle:Price","price")
					->leftJoin("price.cabin","cabin")
					->leftJoin("cabin.rooms", "room")
					->andWhere("room.id = ".$orderItem->getRoom()->getId())
					->andWhere("price.cruise = ".$order->getCruise()->getId())
					->groupBy("price.place")
					->getQuery()
					->getResult()
				;
			$places = ['add'=>false,'remove'=>false];
			foreach($prices as $price)
			{
				if($price->getPlace()->getRpId() > $orderItem->getPlace()->getRpId() )
				{
					$places['add'] = true;
				}
				if($price->getPlace()->getRpId() < $orderItem->getPlace()->getRpId() )
				{
					$places['remove'] = true;
				}
				
				
			}
			$orderItem->otherPlaces = $places;			
			
		}
		if(($order->getSesonDiscount() === null) && ($order->getCruise()->getShip()->getTurOperator()->getCode() !== 'vodohod'))
		{
			$allow_pay = false;
			//$allow_order = false;
		}
		
		if($order->getPermanentRequest())
		{
			if(($order->getPermanentDiscount() == null) /*or ($order->getPermanentDiscount() == 0)*/ ) // возможно убрать второй аргумент
			{
				$allow_pay = false;
				$allow_order = false;
			}
		}
		
		if( $allow_order && ($order->getCruise()->getShip()->getTurOperator()->getCode() === 'mosturflot') && $order->getActive())
		{
			$this->get('cruise')->createOrderMosturflot($order, $allow_pay);
		}
		
		
		
		$orderPrice = $this->get('cruise')->getOrderPrice($order);
		
		// для добавления дополнительных кают
		$available_rooms = $this->get('cruise')->getRooms($order->getCruise()->getId());
		$cruiseShipPrice = $em->getRepository("CruiseBundle:Cruise")->getPrices($order->getCruise()->getId());
		$cabinsAll = $cruiseShipPrice->getShip()->getCabin();
		$cabins = [];
		foreach($cabinsAll as $cabinsItem)
		{
			$rooms_in_cabin = [];
			$price = [];
			foreach($cabinsItem->getRooms() as $room)
			{
	
				if(in_array($room->getNumber(),$available_rooms) /*|| true*/)
				{
					$rooms_in_cabin[] = $room;
				}				
			}
			foreach($cabinsItem->getPrices() as $prices)
			{
				$price[$prices->getPlace()->getRpName()] = $prices->getPlace()->getRpId();
			}
			
			$cabins[] = [
				'price'=>$price,
				'rooms' => $rooms_in_cabin
			];
		}
	
		return [
					'order'=>$order,
					'form'=>$editForm->createView(),
					'rooms'=>$available_rooms,
					'cruiseShipPrice'=>$cruiseShipPrice,
					'allow_pay' => $allow_pay,
					'orderPrice'=>$orderPrice,
					'cabins' => $cabins
				];
	}
	

    /**
     * @Route("/invoice_del/{hash}", name="invoice_del")
     */
    public function invoiceDeleteAction($hash)
    {
		$em = $this->getDoctrine()->getManager();
		$id = $this->get('cruise')->hashOrderDecode($hash);
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
	
		$this->get('cruise')->deleteOrder($order);		
		
		return $this->redirectToRoute('orders');
    }	
	
    /**
	 * @Template("dump.html.twig")	
     * @Route("/order", name="order")
     */
    public function orderAction(Request $request)
    {
		$em = $this->getDoctrine()->getManager();
		
		// из сессии в заказ в базу
		$session = new Session();

		$session->get('basket');	


		$basket_session = $session->get('basket');
		//$order_session = $session->get('order');


		
		//return['session' =>$session];
		
		if(null !== $basket_session)
		{
			$user = $this->getUser();
			$cruise = $em->getRepository('CruiseBundle:Cruise')->findOneById($basket_session['cruise']);
			$order = new Ordering();
			$order->setCruise($cruise);
			$order->setUser($user);
			
			if($user->getAgency() === null) // если не агентство, то создаём покупателя
			{
				$buyer = new \CruiseBundle\Entity\Buyer();
				
				if( !$this->get('security.authorization_checker')->isGranted('ROLE_MANAGER') )
				{
					$buyer->setName($user->getFirstName());
					$buyer->setLastName($user->getLastName());
					$buyer->setFatherName($user->getFatherName());
					$buyer->setEmail($user->getEmail());					
					$buyer->setPhone($user->getPhone());
				}

				
				$em->persist($buyer);
				$order->setBuyer($buyer);			
			}
			else
			{
				$order->setAgency($user->getAgency());
			}

			
			$em->persist($order);
			
			
			
			foreach($basket_session['rooms'] as $room_id => $place_id)
			{
				
				
				$room = $em->getRepository("CruiseBundle:ShipRoom")->findOneById($room_id);
				
				
				$available_rooms = $this->get('cruise')->getRooms($cruise->getId());
				
				if(!in_array($room,$available_rooms))
				{
					return new Response("Каюта уже занята");
				}
				
				if($room->getCountPassMax() !==  null)
				{
					if($room->getCountPassMax() < $place_id )
					{
						$place_id  = $room->getCountPassMax();
					}
				}
				elseif($room->getCountPass() !==  null)
				{
					if($room->getCountPass() < $place_id )
					{
						$place_id  = $room->getCountPass();
					}					
				}

				
				$place = $em->getRepository("CruiseBundle:ShipCabinPlace")->findOneByRpId($place_id);
				
				$typeDiscount = null;
				$discount = $em->getRepository("CruiseBundle:RoomDiscount")->findOneBy(['cruise'=>$cruise,'room'=>$room]) === null ? false : true;
				if($discount)
				{
					$typeDiscount = $cruise->getTypeDiscount();
				}
				
				$orderItem = new OrderItem();
				$orderItem->setRoom($room);
				$orderItem->setPlace($place);
				$orderItem->setOrdering($order);
				$orderItem->setTypeDiscount($typeDiscount);
				
				$em->persist($orderItem);
				
				$order->addOrderItem($orderItem);
				
				for($i=1;$i<=$place_id;$i++)
				{
					$orderItemPlace = new OrderItemPlace();
					$orderItemPlace->setOrderItem($orderItem);
					$em->persist($orderItemPlace);
					$orderItem->addOrderItemPlace($orderItemPlace);
				}
			}
			$em->flush();
			
			$session->remove('basket');
			
			return $this->redirectToRoute('invoice',['hash'=>$this->get('cruise')->hashOrderEncode($order->getId())]);
			
		}
		/*
		elseif(null !== $order_session)
		{
			$order = $em->createQueryBuilder()
				->select('o,oi,oip')
				->from('CruiseBundle:Ordering','o')
				->leftJoin('o.orderItems','oi')
				->leftJoin('oi.orderItemPlaces','oip')
				->where('o.id = '.$order_session)
				->getQuery()
				->getOneOrNullResult()
			;	
		}
		*/
		else
		{

			
			return new Response('Error');
		}

		
		
		return ["request"=>$request,'order'=>$order];
    }	
	
    /**
	 * @Template()	
     * @Route("/orders", name="orders")
     */
    public function ordersAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$qb = $em->createQueryBuilder()
			->select('o,oi,oip,room,cruise,buyer,agency,user')
			->from('CruiseBundle:Ordering','o')	
			->leftJoin('o.cruise','cruise')
			->leftJoin('o.buyer','buyer')
			->leftJoin('o.agency','agency')
			->leftJoin('o.user','user')
			->leftJoin('o.orderItems','oi')
			->leftJoin('oi.room', 'room')
			->leftJoin('oi.orderItemPlaces','oip')
			->andWhere('o.active = 1')
			->orderBy('o.id','ASC')
		;
		if($this->getUser()->getAgency() !== null)
		{
			$qb 
				//->leftJoin('o.user','user')
				->andWhere('o.agency = :agency')
				->setParameter('agency',$this->getUser()->getAgency())
			;				
		}
		elseif($this->get('security.authorization_checker')->isGranted('ROLE_MANAGER'))
		{
			
		}
		else
		{
			$qb 
				->andWhere('o.user = :user')
				->setParameter('user',$this->getUser())
			;			
		}
	
		$orders = $qb	
			->orderBy('o.id','DESC')
			->getQuery()
			->getResult()			
		;	
		
		foreach($orders as &$order)
		{
			$order->orderPrice = $this->get('cruise')->getOrderPrice($order);
		}
		//dump($orders );
		
		return ['orders'=>$orders];
	}
	
    /**
	 * @Template("dump.html.twig")	
     * @Route("/basket", name="basket")
     */
    public function basketAction(Request $request)
    {
		$session = new Session();

		$session->get('basket');
		
		$session->set('basket',$request->request->all());
	
		
		
		return $this->redirectToRoute('order');
		 
		return ["request"=>$request,"session"=>$session]; 
    }	
}
