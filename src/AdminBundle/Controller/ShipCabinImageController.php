<?php

namespace AdminBundle\Controller;

use CruiseBundle\Entity\ShipCabin;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("shipcabinimage")
 */
class ShipCabinImageController extends Controller
{
    /**
     * @Route("/", name="shipcabinimage_ships")
     * @Method("GET")
     */
    public function shipsAction()
    {
        $em = $this->getDoctrine()->getManager();

		$query = $em->getRepository('CruiseBundle:Ship')->createQueryBuilder('s')

			->orderBy('s.name', 'ASC')
			->getQuery()
			;		
		$ships = $query->getResult();
        return $this->render('shipcabinimage/ships.html.twig', array(
            'ships' => $ships,
        ));
    }



    /**
     * Lists all shipcabinimage entities.
     *
     * @Route("/ship/{ship_code}", name="shipcabinimage_ship")
     * @Method("GET")
     */
    public function indexAction($ship_code)
    {
        $em = $this->getDoctrine()->getManager();
		
		$ship = $em->getRepository('CruiseBundle:Ship')->findOneByCode($ship_code);
		
		
		$q = "SELECT cab,img
			FROM CruiseBundle:ShipCabin cab
			LEFT JOIN cab.images img
			WHERE cab.ship = ".$ship->getId()."
			ORDER BY cab.deck
		";
		$query = $em->createQuery($q);
		$cabins = $query->getResult();		
		
		
        return $this->render('shipcabinimage/ship.html.twig', array(
            'cabins' => $cabins,
			'ship' =>$ship,
        ));
    }

    /**
     * Creates a new shipcabinimage entity.
     *
     * @Route("/new/{cabin_id}", name="shipcabinimage_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request,$cabin_id)
    {
		$em = $this->getDoctrine()->getManager();
		
		$cabin = $em->getRepository('CruiseBundle:ShipCabin')->findOneById($cabin_id);
		
		$ship = $cabin->getShip();

        $form = $this->createForm('CruiseBundle\Form\ShipCabinType', $cabin);
        $form->handleRequest($request);
		
		$images = $request->request->get("image");


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();



			if ($images)
			{
				foreach($images as $id => $title)
				{
					$image = $em->getRepository('BaseBundle:Image')->findOneById($id);
					if($image == null) continue;
					$image->setTitle($title);
					$cabin->addImage($image);
					$em->persist($image);
				}	
			}
			
            $em->persist($cabin);
            $em->flush();

            return $this->redirectToRoute('shipcabinimage_ship', array('ship_code' => $ship->getCode()));
        }

        return $this->render('shipcabinimage/new.html.twig', array(
            'cabin' => $cabin,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a shipcabinimage entity.
     *
     * @Route("/{id}", name="shipcabinimage_show")
     * @Method("GET")
     */
    public function showAction(Shipcabinimage $shipcabinimage)
    {
        $deleteForm = $this->createDeleteForm($shipcabinimage);

        return $this->render('shipcabinimage/show.html.twig', array(
            'shipcabinimage' => $shipcabinimage,
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing shipcabinimage entity.
     *
     * @Route("/{id}/edit", name="shipcabinimage_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, ShipCabin $cabin)
    {
		$em = $this->getDoctrine()->getManager();
		
		$ship = $em->getRepository('CruiseBundle:Ship')->findOneById($cabin->getShip());


		$deleteForm = $this->createDeleteForm($cabin);
        $editForm = $this->createForm('CruiseBundle\Form\ShipCabinType', $cabin);

		$images = $request->request->get("image");
		$imagesSort = $request->request->get("image-sort");		
		
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
			
			$em =  $this->getDoctrine()->getManager();
			
			if ($images)
			{
				$cabin->removeAllImages();
				
				foreach($images as $id => $title)
				{
					$image = $em->getRepository('BaseBundle:Image')->findOneById($id);
					if($image == null) continue;
					$image->setTitle($title);
					$image->setSort($imagesSort[$id]);
					$cabin->addImage($image);
					$em->persist($image);
				}	
			}			
			
			
           $em->flush();

            return $this->redirectToRoute('shipcabinimage_ship', array('ship_code' => $ship->getCode()));
        }

        return $this->render('shipcabinimage/edit.html.twig', array(
            'cabin' => $cabin,
            'ship' => $ship,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a shipcabinimage entity.
     *
     * @Route("/{id}", name="shipcabinimage_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, Shipcabinimage $shipcabinimage)
    {
		$em_cruise = $this->getDoctrine()->getManager('cruise');
		$ship = $em_cruise->getRepository('CruiseBundle:Motorship')->findOneById($shipcabinimage->getMotorship());
		
        $form = $this->createDeleteForm($shipcabinimage);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($shipcabinimage);
            $em->flush();
        }

        return $this->redirectToRoute('shipcabinimage_ship',['ship_code'=>$ship->getCode()]);
    }

    /**
     * Creates a form to delete a shipcabinimage entity.
     *
     * @param shipcabinimage $shipcabinimage The shipcabinimage entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(ShipCabin $shipCabin)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('shipcabinimage_delete', array('id' => $shipCabin->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
