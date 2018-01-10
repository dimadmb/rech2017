<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AjaxController extends Controller
{
	
    /**
	 * @Template()	
     * @Route("/ajax/order", name="order_ajax")
     */
    public function orderAction(Request $request)
    {
	
	$post = $request->request->all();
	
	//return new Response (print_r($post,1));
	
	$cruise = $this->getDoctrine()->getRepository("CruiseBundle:Cruise")->findOneById($post["cruise_id"]);

    $message = \Swift_Message::newInstance()
		->setSubject('Заказ')
		->setFrom(array('test-rech-agent@yandex.ru'=>'КРУИЗНЫЙ МАГАЗИН'))
        ->setTo('bron@mirkruizov.ru')
        //->setTo('dkochetkov@vodohod.ru')
        ->setBcc('dkochetkov@vodohod.ru')
        ->setBody(
            $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                'CruiseBundle:Ajax:order.html.twig',
                array('post' => $post, "cruise"=>$cruise)
            ),
            'text/html'
        )
        /*
         * If you also want to include a plaintext version of the message
        ->addPart(
            $this->renderView(
                'Emails/registration.txt.twig',
                array('name' => $name)
            ),
            'text/plain'
        )
        */
    ;
    $this->get('mailer')->send($message);

		
		return new Response ("success");
		return new Response (print_r($request->request->all(),1));
    }

	
}
