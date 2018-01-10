<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

use CruiseBundle\Entity\Pay;


class PayController extends Controller
{
	
    /**
     *
     * @Template()
     * @Route("/manager/addPay", name="manager_add_pay")
     * @Method({"GET", "POST"})
     */	
	public function addAction(Request $request)
	{
		
		//dump($request);
		
		$em = $this->getDoctrine()->getManager();
		
		$order_id = $request->query->get('order_id');
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($order_id);
		
		$pay = new Pay();
		$pay->setOrder($order);
		$form = $this->createForm('CruiseBundle\Form\PayType', $pay);
		$form->handleRequest($request);
		
		
		if ($form->isSubmitted() ) 
		{
			//dump($form);
			
			$em->persist($pay);
			$em->flush();
			return $this->render('CruiseBundle:Pay:responseSumm.html.twig',['pay'=>$pay]);
		}
		
        return [
            'form' => $form->createView(),
        ];
		
	}
	
    /**
     *
     * @Template()
     * @Route("/manager/delPay", name="manager_del_pay")
     */	
	public function delAction(Request $request)	
	{
		//dump($request);
		
		$em = $this->getDoctrine()->getManager();
		
		$id = $request->request->get('id');
		$pay = $em->getRepository("CruiseBundle:Pay")->findOneById($id);
		$pay->setIsDelete(true);
		$em->flush();
		return new Response("OK");
		
	}
	
	// это можно кинуть в сервис 
	public function addPay($amount, $comment = null)
	{
		$em = $this->getDoctrine()->getManager();
		
		$pay = new Pay();
		$pay
			->setPay($pay)
			->setComment($comment)
		;
		$em->persist($pay);
		$em->flush();
	}
}
