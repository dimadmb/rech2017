<?php
namespace BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class BreadCrumbsController extends Controller
{

	 /**
	 * @Template()
     */
    public function indexAction()
    {
		$request = $this->get('request_stack')->getMasterRequest();
		
		if($request->getPathInfo() == '/')
		{
			$pages[] = $this->getDoctrine()->getRepository('BaseBundle:Page')->findOneByFullUrl('');
		}
		else
		{
			$aliases = explode('/',trim($request->getPathInfo()));
			$fullUrl = [];
			foreach($aliases as $alias)
			{
				$fullUrl[] = $alias;
				$findUrl = trim(implode('/',$fullUrl),'/');
				$page = $this->getDoctrine()->getRepository('BaseBundle:Page')->findOneByFullUrl($findUrl);

				if($page != null)	$pages[] = $page;
			}
		}
		

		return  [ 'pages' => $pages , $request->getPathInfo()];
	}

}
