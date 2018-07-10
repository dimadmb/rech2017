<?php

namespace BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;


class PageController extends Controller
{
	 /**
	 * @Template()
     */
    public function indexAction()
    {
		return  $this->pageAction("");
	}



	/**
	 * @Template()
     */
    public function pageAction($url)
    {
		
		
		$repository = $this->getDoctrine()->getRepository('BaseBundle:Page');
		$page = $repository->findOneByFullUrl($url);
		if ($page == null) {
			throw $this->createNotFoundException("Страница $url не найдена.");
		}
		
		
		$html = $page->getBody();
/*
		$html = preg_replace_callback(
			'/{\{(.*)\}}/U',
			function ($m) {
				
				//return htmlspecialchars_decode($m[1],ENT_QUOTES );
				
				eval("\$temp = ".htmlspecialchars_decode($m[1],ENT_QUOTES ). " ;");
				
				//return $temp;
				$ret = $this->forward($temp[0],isset($temp[1])?$temp[1]:[])->getContent();
				return $ret;
			},
			$html
		);
*/

		$html = htmlspecialchars_decode($html,ENT_QUOTES );
		$html = html_entity_decode($html);
        
		$template =  $this->container->get('twig')->createTemplate($html);
		$html = $template->render([]);
		
		/// переносим JS из дополнительно отрендерренных контроллеров
		$re = '/<JS>(.*)<\/JS>/Us';
		preg_match_all($re, $html, $matches, PREG_SET_ORDER, 0);
		$js = '';
		foreach($matches as $match)
		{
			$js .= $match[1];
		}
		$html = preg_replace($re,"",$html);
	
		$page->html = $html;
		
		return ['page' => $page, 'js' => $js  ];
    }
	
	 /**
	 * @Template()
     */	
	public function childsAction($page_id)
	{
		$em = $this->getDoctrine()->getManager();
		$pages = $em->createQueryBuilder()
				->select('p')
				->from('BaseBundle:Page','p')
				->where('p.parent = '.$page_id)
				->andWhere('p.active = 1')
				->getQuery()
				->getResult()
			;


		
		return ['pages'=>$pages];
	}

	
}
