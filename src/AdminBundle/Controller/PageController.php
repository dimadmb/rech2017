<?php

namespace AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use BaseBundle\Entity\Page;
use BaseBundle\Entity\Image;
use BaseBundle\Form\PageType;

/**
 * Page controller.
 *
 * @Route("/page")
 */
class PageController extends Controller
{
    /**
     * Lists all Page entities.
     *
     * @Route("/", name="page_index")
     * @Method("GET")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $pages = $em->getRepository('BaseBundle:Page')->findBy([],['sort'=>'ASC']);
		foreach($pages as $page)
		{
			$parent = $page->getParent();
			if ($parent)
			{
				$parent->addChild($page);
			}
			$page->getChildren()->setInitialized(true);
		}

        return $this->render('page/index.html.twig', array(
            'pages' => $pages,
        ));
    }

    /**
     * Creates a new Page entity.
     *
     * @Route("/new/{parent}", name="page_new")
     * @Method({"GET", "POST"})
     */
    public function newAction(Request $request, $parent = null)
    {
        $page = new Page();
		
		// Назначаем родителя, если нужно
		if(!null == $parent)
		{
			$em = $this->getDoctrine()->getManager();
			$parent = $em->getRepository('BaseBundle:Page')->findOneById($parent);
			$page->setParent($parent);
		}
		
        $form = $this->createForm('BaseBundle\Form\PageType', $page);
        $form->handleRequest($request);

		$images = $request->request->get("image");

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
			$localUrl = trim($page->getLocalUrl())."";
			$parentUrl = ($page->getParent() == null) ? "" : $page->getParent()->getFullUrl();
			$fullUrl = ($parentUrl == "" || $parentUrl == "/" ) ? $localUrl : $parentUrl.'/'.$localUrl;
			$page->setParentUrl($parentUrl);
			$page->setFullUrl($fullUrl);
			$page->setLocalUrl($localUrl);
			
			if ($images)
			{
				foreach($images as $id => $title)
				{
					$image = $em->getRepository('BaseBundle:Image')->findOneById($id);
					if($image == null) continue;
					$image->setTitle($title);
					$page->addFile($image);
					$em->persist($image);
				}	
			}

			$em->persist($page);
            $em->flush();
			
			return $this->redirectToRoute('page', array('url' => $page->getFullUrl()));
            return $this->redirectToRoute('page_show', array('id' => $page->getId()));
        }

        return $this->render('page/new.html.twig', array(
            'page' => $page,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Page entity.
     *
     * @Route("/{id}", name="page_show")
     * @Method("GET")
     */
    public function showAction(Page $page)
    {
        $deleteForm = $this->createDeleteForm($page);

        return $this->render('page/show.html.twig', array(
            'page' => $page,
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * AJAX правка страницы
     *
     * @Route("/ajax/{id}/edit", name="page_ajax_edit")
     * @Method({"GET", "POST"})
     */
    public function editAjaxAction(Request $request, Page $page)
	{
		$em = $this->getDoctrine()->getManager();
		
		$page_h1 = $request->request->get('page_h1');
		$page_body = $request->request->get('page_body');
		
		$page->setH1($page_h1);
		$page->setBody($page_body);
		
		$em->flush();
		
		return new Response('OK');
		
		return $this->render('dump.html.twig', ['dump'=>[$page_h1,$page_body , $request,$page]]);
	}
    /**
     * Displays a form to edit an existing Page entity.
     *
     * @Route("/{id}/edit", name="page_edit")
     * @Method({"GET", "POST"})
     */
    public function editAction(Request $request, Page $page)
    {
        $deleteForm = $this->createDeleteForm($page);
        $editForm = $this->createForm('BaseBundle\Form\PageType', $page);
        $editForm->handleRequest($request);
		
		$images = $request->request->get("image");
		$imagesSort = $request->request->get("image-sort");

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
						
			// если попытка назначить родителем саму же сусщность, то назначаем родителем по адресу выше
			if( ($page->getParent()) && $page->getId() == $page->getParent()->getId())
			{
				$pos = strrpos($page->getFullUrl(), "/");
				if ($pos !== false)
				{
					$newUrl = substr($page->getFullUrl(),0,$pos);
				}
				$pageOld = $em->getRepository('BaseBundle:Page')->findOneByFullUrl($newUrl);
				$page->setParent($pageOld);
			}
			
			$localUrl = trim($page->getLocalUrl())."";
			$parentUrl = ($page->getParent() == null) ? "" : $page->getParent()->getFullUrl();
			$fullUrl = ($parentUrl == "" || $parentUrl == "/" ) ? $localUrl : $parentUrl.'/'.$localUrl;
			$page->setParentUrl($parentUrl);
			$page->setFullUrl($fullUrl);
			$page->setLocalUrl($localUrl);			
			
			if ($images)
			{
				$page->removeAllFile();
				
				foreach($images as $id => $title)
				{
					$image = $em->getRepository('BaseBundle:Image')->findOneById($id);
					if($image == null) continue;
					$image->setTitle($title);
					$image->setSort($imagesSort[$id]);
					$page->addFile($image);
					$em->persist($image);
				}	
			}
            $em->persist($page);
            $em->flush();
       /* return $this->render('page/edit.html.twig', array(
            'dump' => $page,
			'page' => $page,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));*/
			
            return $this->redirectToRoute('page', array('url' => $page->getFullUrl()));
            return $this->redirectToRoute('page_edit', array('id' => $page->getId()));
        }

        return $this->render('page/edit.html.twig', array(
            'page' => $page,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Deletes a Page entity.
     *
     * @Route("/{id}/delete", name="page_delete")
     */
    public function deleteAction( $id )
    {
        $em = $this->getDoctrine()->getManager();
		
		$page = $em->getRepository("BaseBundle\Entity\Page")->findOneById($id);
		
		$dump = $images = $page->getFile();
		
		foreach($images as $image)
		{
			// проверку на существование
			if(file_exists($this->getParameter('upload_directory').'/'.$image->getFilename()))
			unlink($this->getParameter('upload_directory').'/'.$image->getFilename());
			$em->remove($image);
		}
        $em->remove($page);
        $em->flush();

        return $this->redirectToRoute('page_index');
		
    }

    /**
     * Creates a form to delete a Page entity.
     *
     * @param Page $page The Page entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm(Page $page)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('page_delete', array('id' => $page->getId())))
            ->setMethod('DELETE')
            ->getForm()
        ;
    }
}
