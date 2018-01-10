<?php

namespace BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class SiteMapController extends Controller
{
	 /**
	 * @Route("/sitemap.xml", name="sitemapxml"  )
	 * @Route("/sitemap.xml", name="sitemapxml" , defaults={"_format" = "xml"} )
	 
     */		
	public function indexAction(Request $request)
	{
		$baseUrl = $request->server->get('REQUEST_SCHEME')."://".$request->server->get('SERVER_NAME');
		// домашняя страница
		$sitemap[] = ['loc'=>$baseUrl,'priority'=>1, 'changefreq'=> 'daily'];


		

		
		// страницы 
		$pages = $this->getDoctrine()->getRepository('BaseBundle:Page')->findBy(['active'=>true]);
		foreach($pages as $page)
		{
			$sitemap[] = ['loc'=>$baseUrl.'/'.$page->getFullUrl(),'priority'=>0.8, 'changefreq'=> 'daily'];
		}
		
		// круизы 
		$cruises = $this->getDoctrine()->getManager()->createQueryBuilder()
							->select('c')
							->from('CruiseBundle:Cruise','c')
							->where('c.endDate >= CURRENT_DATE()')
							->getQuery()
							->getResult()
						;
							
		foreach($cruises as $cruise)
		{
			$sitemap[] = ['loc'=>$baseUrl.'/cruise/'.$cruise->getId(),'priority'=>0.8, 'changefreq'=> 'daily'];
		}
		
		
		
		$xmloutput = $this->container->get('templating')
				->render('BaseBundle:SiteMap:index.xml.twig', ['sitemap'=>$sitemap]);
		$response = new Response($xmloutput);		
		return $response ;
	}
	


}
