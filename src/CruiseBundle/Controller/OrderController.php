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
use CruiseBundle\Entity\Ship;
use CruiseBundle\Entity\ShipRoom;
use CruiseBundle\Entity\Ordering;
use CruiseBundle\Entity\OrderItem;
use CruiseBundle\Entity\OrderItemPlace;
use CruiseBundle\Entity\OrderItemService;
use CruiseBundle\Entity\Region;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use CruiseBundle\Form\BuyerType;
use CruiseBundle\Form\OrderItemType;
use CruiseBundle\Form\OrderingType;

use Doctrine\ORM\EntityRepository;

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
     * @Route("/order/remove_service/{hash}/{service}", name="invoice_remove_service")
     */
	public function invoiceRemoveServiceAction(Request $request,$hash,OrderItemService $service)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($this->get('cruise')->hashOrderDecode($hash));
		
		//$service->setActive(false)
		
		$em->remove($service);
		
		
		
		//return new Response("OK");
		
		$em->flush();
		
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
	}	
	/**
     * @Route("/order/add_service/{hash}", name="invoice_add_service")
     */
	public function invoiceAddServiceAction(Request $request,$hash)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($this->get('cruise')->hashOrderDecode($hash));
		$service = new OrderItemService();
		
		$service
			->setOrder($order)
		;
		$em->persist($service);
		$em->flush();
		return $this->redirectToRoute('invoice', ['hash'=>$hash]);
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
		
		$mainTypePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("main");
		
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
			$orderItemPlace->setTypePlace($mainTypePlace);
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
		
		if($place->getTypePlace()->getCode() !== 'main')
		{
			$em->flush();
			return $this->redirectToRoute('invoice', ['hash'=>$hash]);			
		}
		
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
     * @Route("/order/add_place/{hash}/{room}/{type}", name="invoice_add_place")
     */
    public function invoiceAddPlaceAction(Request $request,$hash,ShipRoom $room, $type = null)
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
		
		
		if($type === null)
		{
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
		}
		

		
		$newOrderItemPlace = new OrderItemPlace();
		$newOrderItemPlace->setOrderItem($orderItem);
		
		$typePlace = null;
		switch($type)
		{
			case null: 
					$typePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("main");
					break;
			case 'add':
					$typePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("add");
					break;			
			case 'without':
					$typePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("without");
					break;			
		}
		
		$newOrderItemPlace->setTypePlace($typePlace);
		
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
			$cabinsItem->getPrices()->setInitialized(false);
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
				
				$order->setRegion($user->getAgency()->getRegion());
			}

			
			$em->persist($order);
			
			$mainTypePlace = $em->getRepository("CruiseBundle:TypePlace")->findOneByCode("main");
			
			foreach($basket_session['rooms'] as $room_id => $place_id)
			{
				
				
				$room = $em->getRepository("CruiseBundle:ShipRoom")->findOneById($room_id);
				
				
				$available_rooms = $this->get('cruise')->getRooms($cruise->getId());
				
				if(!in_array($room,$available_rooms))
				{
					return new Response("Каюта ".$room->getNumber()." уже занята");
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
					$orderItemPlace->setTypePlace($mainTypePlace);
					$em->persist($orderItemPlace);
					$orderItem->addOrderItemPlace($orderItemPlace);
				}
			}
			
/* 			
			// если теплоход не в свободной продаже отправим заявку и вернём пользователя на страницу
			if(false && !$cruise->getShip()->getInSale())
			{
				$message = \Swift_Message::newInstance()
					->setSubject('Заказ')
					->setFrom(array('test-rech-agent@yandex.ru'=>'rech-agent.ru'))
					//->setTo('info@rech-agent.ru')
					->setTo('dkochetkov@vodohod.ru')
					->setBcc('dkochetkov@vodohod.ru')
					->setBody(
						$this->renderView(
							'CruiseBundle:Order:emailNotSale.html.twig',
							['order'=>$order]
						),
						'text/html'
					)

				;
				$this->get('mailer')->send($message);
				
			$session->getFlashBag()->add(
				'flash',
				'Ваша заявка принята'
			);				
				

				return $this->redirectToRoute('cruisedetail',['id'=>$cruise->getId()]);
				
			}			
			 */
			
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
	 * @Route("/agency_report", name="agency_report")
	 */
	public function report(Request $request)
	{
		// https://www.rech-agent.ru/web/app_dev.php/agency_report?agency_id=66&date_year=2018&date_month=02
		//return new Response("OK");
		
		$agency_id = $this->getUser()->getAgency()->getId();
		
		//$agency_id = $request->query->get('agency_id');
		$date_year = $request->query->get('date_year');
		$date_month = $request->query->get('date_month');


		$response = $this->get('report_agent')->report($agency_id,$date_year,$date_month);

		return $response;
	}
	
	/**
	 * @Route("/agency_act", name="agency_act")
	 */
	public function act(Request $request)
	{
		$agency_id = $this->getUser()->getAgency()->getId();
		$date_year = $request->query->get('date_year');
		$date_month = $request->query->get('date_month');


		$response = $this->get('report_agent')->act($agency_id,$date_year,$date_month);
		

		
		return $response;
	}
	
	
    /**
	 * @Template()	
     * @Route("/orders", name="orders")
     */
    public function ordersAction(Request $request)
	{
		$em = $this->getDoctrine()->getManager();
		$form = $this->createFormBuilder()
				->add('ship',EntityType::class,[
								'required'=> false,
								'class' => Ship::class,
								'query_builder' => function (EntityRepository $er) {
									return $er->createQueryBuilder('s')
										->orderBy('s.name', 'ASC');
										},
										
										'label'=>"Теплоход"
										

															])
				->add('order',TextType::class,['required'=> false,'label'=>"Заявка"]) 
								
				->add('oplata', ChoiceType::class,[
								'required'=> false,
									'choices'  => array(
										'Неоплачен' => 1,
										'Частично оплачен' => 2,
										'Оплачен' => 3,
										'Переплата' => 4,
									),
									'label'=>"Оплата"
								])								

				->add('submit', SubmitType::class,array('label' => 'Фильтровать'))
				->getForm()
			;	
		$form->handleRequest($request);		
		
		$search = [];
		if ($form->isSubmitted() && $form->isValid()) 
		{
			$search = $form->getData();
		}	
		//dump($search);
		
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
		
		
		if(isset($search['ship']))
		{
			$qb
			//->leftJoin('o.cruise','c')
			->leftJoin('cruise.ship','s')
			->andWhere('s = :ship')
			->setParameter('ship',$search['ship'])
			;
		}
		if(isset($search['order']))
		{
			$qb
			->andWhere($qb->expr()->like('o.id', ':id'))
			->setParameter('id','%'.$search['order'].'%')
			;
		}		
		
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
		
   // оплаты можно посчитать позже на PHP b удалить лишние
		if(isset($search['oplata']))
		{
			foreach($orders as $key => &$order)
			{
				$orderPrice = $order->orderPrice;
				
				// не оплачен
				if ( ($search['oplata'] == 1 ) && ($orderPrice['itogo']['pay'] > 0) ) 
				{
					unset($orders[$key]);
				}
				
				//частично оплачен
				if (($search['oplata'] == 2 ) && ( ($orderPrice['itogo']['pay'] == 0) ||  $orderPrice['itogo']['pay'] >= $orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'])  )
				{
					unset($orders[$key]);
				}
				
				// оплачен
				if (($search['oplata'] == 3 ) && ( $orderPrice['itogo']['pay'] < $orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'])  )
				{
					unset($orders[$key]);
				}
				
				// переплата
				if (($search['oplata'] == 4 ) && ( $orderPrice['itogo']['pay'] <= $orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'])  )
				{
					unset($orders[$key]);
				}
				
 
			}
				
		}		
		


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
					
		$formReport = 
		//$this->createFormBuilder()
		$this->get('form.factory')->createNamed('report')
				->add('date_month',ChoiceType::class,['choices'=> $months, 'label'=>'Месяц'])
				->add('date_year',ChoiceType::class, ['choices'=> $years,'label'=>'Год'])
				
				
				//->getForm()
			;
		
		$formReport->handleRequest($request);		
		

		
		return ['orders'=>$orders,'formOrder'=>$form->createView(),'formReport'=>$formReport->createView()];
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
	
		$ship = $this->getDoctrine()->getRepository("CruiseBundle:Cruise")->findOneById($session->get('basket')['cruise'])->getShip();
		
		if(!$ship->getInSale())
		{
			
			return $this->redirectToRoute('send_email_order');
		}
		
		return $this->redirectToRoute('order');
		 
		return ["request"=>$request,"session"=>$session]; 
    }		
    /**
	 * @Template()	
     * @Route("/send_email_order", name="send_email_order")
     */
    public function orderSendMailAction(Request $request)
    {
		$session = new Session();
		
		$em = $this->getDoctrine()->getManager();
		
		$basket_session = $session->get('basket');
		$cruise = $em->getRepository('CruiseBundle:Cruise')->findOneById($basket_session['cruise']);	
		$rooms = [];
		foreach($basket_session['rooms'] as $room_id => $place_id)
		{
			$rooms[] = $em->getRepository("CruiseBundle:ShipRoom")->findOneById($room_id);
		}

		$form = $this->get('form.factory')->createNamed('send_mail')
			->add('name',TextType::class,['label'=>'Имя'])
			->add('phone',TextType::class,['label'=>'Телефон'])
			->add('submit',SubmitType::class,['label'=>'Отправить'])
		;
		
		$form->handleRequest($request);

		if ($form->isSubmitted() && $form->isValid())
		{
			
			$name = $form->getData()['name'];
			$phone = $form->getData()['phone'];
			
			$message = \Swift_Message::newInstance()
				->setSubject('Заказ')
				->setFrom(array('test-rech-agent@yandex.ru'=>'rech-agent.ru'))
				//->setTo('info@rech-agent.ru')
				->setTo('info@rech-agent.ru')
				->setBcc('dkochetkov@vodohod.ru')
				->setBody(
					$this->renderView(
						'CruiseBundle:Order:emailNotSale.html.twig',
						['cruise'=>$cruise,'rooms'=>$rooms,'name'=>$name,'phone'=>$phone]
					),
					'text/html'
				)

			;
			$this->get('mailer')->send($message);
				
			$session->getFlashBag()->add(
				'flash',
				'Ваша заявка принята'
			);
			
			return $this->redirectToRoute('homepage');
			
		}

		
		return ['cruise'=>$cruise,'rooms'=>$rooms,'form'=>$form->createView()]; 
    }	
}
