<?php

namespace ManagerBundle\Controller;

use CruiseBundle\Entity\Agency;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Agency controller.
 *
 * @Route("manager/agency")
 */
class AgencyController extends Controller
{
    /**
     * Lists all agency entities.
     *
     * @Template()
     * @Route("/", name="manager_agency_index")

     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
		
		
		$form = $this->get('form.factory')->createNamedBuilder('')
				->add('agency',EntityType::class,[
								'required'=> false,
								'class' => Agency::class,
								'query_builder' => function (EntityRepository $er) {
									return $er->createQueryBuilder('a')
										->orderBy('a.name', 'ASC');
										},
										
										'label'=>"Агентство"
										

															])	
				->add('agencyEditable',HiddenType::class)		
				->add('submit', SubmitType::class,array('label' => 'Фильтровать'))
				->getForm()
			;
			
		$form->handleRequest($request);		
		
		//dump($request);
		
		$search = [];
		if ($form->isSubmitted() && $form->isValid()) 
		{
			$search = $form->getData();
		}	

		//dump($search);
		//dump($request);
		
		
		if($request->query->get('all') === null)
		{
			if(isset($search['agency']))
			{
				$agencies = $em->getRepository('CruiseBundle:Agency')->findById($search['agency']);
			}
			else
			{
				$agencies = $em->getRepository('CruiseBundle:Agency')->findBy(['active'=>true]);
			}
		}
		else
		{
			$agencies = $em->getRepository('CruiseBundle:Agency')->findAll();	
		}



        return [
            'agencies' => $agencies,
			'form'=>$form->createView()
        ];
    }

    /**
     * Creates a new agency entity.
     * @Template()
     * @Route("/new", name="manager_agency_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $agency = new Agency();
        $form = $this->createForm('CruiseBundle\Form\AgencyType', $agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($agency);
            $em->flush();

            return $this->redirectToRoute('manager_agency_show', array('id' => $agency->getId()));
        }

        return [
            'agency' => $agency,
            'form' => $form->createView(),
        ];
    }

    /**
     * Finds and displays a agency entity.
     *
     * @Template()	 
     * @Route("/{id}", name="manager_agency_show")
     * @Method("GET")
     */
    public function showAction(Agency $agency)
    {
        $deleteForm = $this->createDeleteForm($agency);

        return [
            'agency' => $agency,
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Displays a form to edit an existing agency entity.
     *
     * @Template()	 	 
     * @Route("/{id}/edit", name="manager_agency_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Agency $agency)
    {
        $deleteForm = $this->createDeleteForm($agency);
        $editForm = $this->createForm('CruiseBundle\Form\AgencyType', $agency);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('manager_agency_index'/*, array('id' => $agency->getId())*/);
        }

        return [
            'agency' => $agency,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ];
    }

    /**
     * Deletes a agency entity.
     *
     * @Route("/{id}", name="manager_agency_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Agency $agency)
    {
        $form = $this->createDeleteForm($agency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //$em->remove($agency);
			$agency->setActive(false);
            $em->flush();
        }

        return $this->redirectToRoute('manager_agency_index');
    }

    /**
     * Creates a form to delete a agency entity.
     *
     * @param Agency $agency The agency entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Agency $agency)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('manager_agency_delete', array('id' => $agency->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
