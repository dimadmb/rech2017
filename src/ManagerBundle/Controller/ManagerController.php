<?php

namespace ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


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
	 * @Template()	
     * @Route("/invoices", name="manager_invoices")
     */
    public function invoicesAction(Request $request)
    {
		$cruises = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:Cruise")->findAll();
		return ['cruises'=>$cruises];
	}	
	
    /**
	 * @Template()	
     * @Route("/turs", name="manager_turs")
     */
    public function tursAction(Request $request)
    {
		$cruises = $this->get('cruise_search')->searchCruise($request->query->all());
		$typeDiscounts = $this->getDoctrine()->getManager()->getRepository("CruiseBundle:TypeDiscount")->findAll();
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
