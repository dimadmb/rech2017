<?php

namespace BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MenuController extends Controller
{
	 /**
	 * @Template()
     */
	public function getMenuAction()
	{
        $repository = $this->getDoctrine()->getRepository("BaseBundle:Page");
		$pages = $repository->findBy(array("isMenu"=>true, "active"=>true),array('sort' => 'ASC'));
		
		foreach($pages as $page)
		{
			$parent = $page->getParent();
			if ($parent)
			{
				$parent->addChild($page);
			}
			$page->getChildren()->setInitialized(true);
		}
		
		return ["pages"=>$pages];
	}
}
