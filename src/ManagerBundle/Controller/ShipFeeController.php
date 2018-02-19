<?php

namespace ManagerBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use CruiseBundle\Entity\TurOperator;

use CruiseBundle\Form\ShipType;

/**
 * @Route("manager/ship")
 */

class ShipFeeController extends Controller
{
	
    /**
     * @Template()
     * @Route("/{turOperator}", name="manager_ship_fee_index")
     */
    public function indexAction(Request $request, TurOperator $turOperator)
    {
        $em = $this->getDoctrine()->getManager();
		
		
		$form = $this->createFormBuilder($turOperator)
			->add('ships',CollectionType::class,
				[
					'entry_type' => ShipType::class,
					'entry_options' => ['label' => false ],
				]
			)	
			->add('submit', SubmitType::class,array('label' => 'Сохранить'))
			->getForm()
			;
			
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
			$this->getDoctrine()->getManager()->flush();
        }
		
		$search = [];
		if ($form->isSubmitted() && $form->isValid()) 
		{
			$search = $form->getData();
		}	




        return [
			'form'=>$form->createView()
        ];
    }	
	
}
