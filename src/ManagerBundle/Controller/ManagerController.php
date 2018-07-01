<?php

namespace ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

use CruiseBundle\Entity\Ship;
use CruiseBundle\Entity\Region;
use CruiseBundle\Entity\Ordering;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

/**
 * Manager controller.
 *
 * @Route("/manager")
 
 */

class ManagerController extends Controller
{

    /**
	 * @Template()	
     * @Route("/cruises", name="manager_cruises")
     */
    public function indexAction(Request $request)
    {
		$cruises = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:Cruise")->findAll();
		return ['cruises'=>$cruises];
	}	


    /**
     * @Route("/invoice_del/{order}", name="manager_invoice_del")
     */
    public function invoiceDeleteAction(Ordering $order)
    {
		
		$this->get('cruise')->deleteOrder($order);
		
		
		return $this->redirectToRoute('manager_invoices');		

    }

    /**
     * @Route("/invoice_no_del/{order}", name="manager_invoice_no_del")
     */
    public function invoiceNoDeleteAction(Ordering $order)
    {
		if($order)
		{
			$em = $this->getDoctrine()->getManager();
			$order->setActive(true);
			$em->flush();
		}
		
						
		
		
		return $this->redirectToRoute('manager_invoices');
    }
	
	
    /**
	 * @Template()	
     * @Route("/invoices", name="manager_invoices")
     */
    public function invoicesAction(Request $request)
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
								
				->add('year', ChoiceType::class,[
								'required'=> false,
									'choices'  => array(
										'2018' => 2018,
										'2019' => 2019,
									),
									'label'=>"Год круиза"
								])										
				->add('count', ChoiceType::class,[
								'required'=> true,
									'choices'  => array(
										'50' => null,
										'200' => 200,
										'1000' => 1000,
										'все' => 10000,
									),
									'label'=>"Выводить по"
								])								
				->add('del', CheckboxType::class,[
								'required'=> false,
								'label'  => "Показать удалённые",
								])
				->add('region',EntityType::class,[
								'required'=> false,
								'class' => Region::class,
								'label'=>"Регион"								
								])
				->add('buyer',TextType::class,['required'=> false,'label'=>"Покупатель"])
				->add('agency',TextType::class,['required'=> false,'label'=>"Агентство"])
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
		
		//$orders = $em->getRepository("CruiseBundle:Ordering")->findAll();
		
		$qb = $em->createQueryBuilder()
						->select('o')
						->from('CruiseBundle:Ordering','o')
						;
		if(isset($search['order']))
		{
			$qb
			->andWhere($qb->expr()->like('o.id', ':id'))
			->setParameter('id','%'.$search['order'].'%')
			;
		}
		if(isset($search['region']))
		{
			$qb
			->andWhere('o.region = :region')
			->setParameter('region',$search['region'])
			;
		}
		if(isset($search['ship']))
		{
			$qb
			->leftJoin('o.cruise','c')
			->leftJoin('c.ship','s')
			->andWhere('s = :ship')
			->setParameter('ship',$search['ship'])
			;
		}
		if(isset($search['buyer']))
		{
			$qb->leftJoin('o.buyer','b');			
			$qb->andWhere($qb->expr()->orX(
							$qb->expr()->like('b.name', ':buyer') , 
							$qb->expr()->like('b.lastName', ':buyer') , 
							$qb->expr()->like('b.fatherName', ':buyer') , 
							$qb->expr()->like('b.email', ':buyer') , 
							$qb->expr()->like('b.phone', ':buyer') 
							));
			$qb->setParameter('buyer', '%'.$search['buyer'].'%');				
			
		}
		if(isset($search['agency']))
		{
			$qb->leftJoin('o.agency','a');			
			$qb->andWhere($qb->expr()->orX(
							$qb->expr()->like('a.name', ':agency') , 
							$qb->expr()->like('a.shortName', ':agency') , 
							$qb->expr()->like('a.phone', ':agency') 
							));
			$qb->setParameter('agency', '%'.$search['agency'].'%');				
			
		}
		
		if(isset($search['del']) && $search['del'] === true)
		{
			
		}
		else
		{
			$qb->andWhere('o.active = 1');
		}
		
		if(isset($search['count']) && $search['count'] !== null)
		{
			$qb->setMaxResults($search['count']);
		} 
		else
		{
			$qb->setMaxResults(50);
		}
		
		
		if(isset($search['year']) && $search['year'] !== null)
		{
			$qb->leftJoin('o.cruise','c')
			->andWhere("c.startDate LIKE '" . $search['year'] . "%' ")
			;
		}		

		
		$orders = $qb
				->orderBy('o.id','DESC')
				->getQuery()
				->getResult()
			;
		
		
		foreach($orders as $key => &$order)
		{
			$order->orderPrice = $this->get('cruise')->getOrderPrice($order);
		}
		
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
				if (($search['oplata'] == 2 ) && ( ($orderPrice['itogo']['pay'] == 0) ||  $orderPrice['itogo']['pay'] >= ($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'] ))  )
				{
					unset($orders[$key]);
				}
				
				// оплачен
				if (($search['oplata'] == 3 ) && ( $orderPrice['itogo']['pay'] < ($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'] ))  )
				{
					unset($orders[$key]);
				}
				
				// переплата
				if (($search['oplata'] == 4 ) && ( $orderPrice['itogo']['pay'] <= ($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'] ))  )
				{
					unset($orders[$key]);
				}
				
 
			}
				
		}
	
		return ['orders'=>$orders, 'form'=>$form->createView()];
	}	
	
    /**
	 * @Template()	
     * @Route("/turs", name="manager_turs")
     */
    public function tursAction(Request $request)
    {
		$cruises = $this->get('cruise_search')->searchCruise($request->query->all());
		$typeDiscounts = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:TypeDiscount")->findAll();
		
		//dump($cruises);
		
		return ['cruises'=>$cruises, 'typeDiscounts' => $typeDiscounts];
	}
		
	
    /**
	 * @Template()	
     * @Route("/get_ajax_rooms/{cruise_id}", name="manager_get_ajax_rooms")
     */
    public function getAjaxRoomsAction(Request $request, $cruise_id = null)
    {
		$em = $this->getDoctrine()->getManager();
		$qb = $em->createQueryBuilder()
			->select('c,s,cab,room,rd')
			->from('CruiseBundle:Cruise','c')
			->leftJoin('c.ship','s')
			->leftJoin('s.cabin','cab')
			->leftJoin('cab.rooms','room')
			->leftJoin('c.roomDiscount','rd')
			->where('c.id = '.$cruise_id)
			//->andWhere('rd.cruise = c.id')
			//->andWhere('rd.room = room.id')
		;	
		$cruise = $qb->getQuery()->getOneOrNullResult();
		
		$typeDiscount = $em->getRepository("CruiseBundle:TypeDiscount")->findOneById($request->query->get('typeDiscount'));
		$cruise->setTypeDiscount($typeDiscount);
		$em->flush();
		
		// сбросим круиз из кэша 
		
		$this->get('memcache.default')->delete('cruise'.$cruise->getId());
		
		
		
		$roomDiscounts = $em->getRepository("CruiseBundle:RoomDiscount")->findByCruise($cruise);
		foreach($roomDiscounts as $roomDiscount)
		{
			$roomDiscountArr[$roomDiscount->getRoom()->getId()] = $roomDiscount;
		}
		
		
		$rooms = [];
		foreach($cruise->getShip()->getCabin() as $cabins)
		{
			foreach($cabins->getRooms() as $room)
			{
				if(isset($roomDiscountArr[$room->getId()]))
				{
					$room->discount = true;
				}
				else
				{
					$room->discount = false;
				}
				$rooms[$room->getNumber()] = $room;
			}
		}
		ksort($rooms);
		
		return ['rooms'=>$rooms, 'cruise'=>$cruise, 'request'=>$request];
	}

    /**
	 * @Template()	
     * @Route("/set_ajax_room/", name="manager_set_ajax_room_root")
     */
	public function setAjaxRoomRootAction() 
	{
		return null;
	}
	
    /**
	 * @Template()	
     * @Route("/set_ajax_room/{cruise_id}/{room_id}", name="manager_set_ajax_room")
     */
    public function setAjaxRoomAction($cruise_id,$room_id)
    {
		$em = $this->getDoctrine()->getManager();
		$cruise = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:Cruise")->findOneById($cruise_id);
		$room = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:ShipRoom")->findOneById($room_id);
		
		$roomDiscount = $em->getRepository("CruiseBundle:RoomDiscount")->findOneBy([
							'cruise'=>$cruise,
							'room'=>$room,
						]);
		$answer="";
		//dump($roomDiscount);
		if(null == $roomDiscount)
		{
			$roomDiscount = new \CruiseBundle\Entity\RoomDiscount;
			$roomDiscount
					->setCruise($cruise)
					->setRoom($room)
			;
			$em->persist($roomDiscount);
			$answer = 'set';
		}	
		else
		{
			$em->remove($roomDiscount);
			$answer = 'del';
		}	
		$em->flush();
		
		return new Response($answer);
	}
	
}
