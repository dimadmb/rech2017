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
				}
			}
		}
		if(($order->getSesonDiscount() === null) && ($order->getCruise()->getShip()->getTurOperator()->getCode() !== 'vodohod'))
		{

			$allow_pay = false;
		}
		
		if($order->getPermanentRequest())
		{
			if(($order->getPermanentDiscount() == null) /*or ($order->getPermanentDiscount() == 0)*/ ) // возможно убрать второй аргумент
			{

				$allow_pay = false;
			}
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
		if($order)
		{
			$em = $this->getDoctrine()->getManager();
			$order->setActive(false);
			$em->flush();
		}
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
