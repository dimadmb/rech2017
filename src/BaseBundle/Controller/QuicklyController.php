<?php

namespace BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\HttpFoundation\Session\Session;

class QuicklyController extends Controller
{
    /**
	 * @Template()
     * @Route("/quickly")
     */
    public function sendQuicklyAction()
    {
        $request = Request::createFromGlobals();
        
        $session = new Session();
        
        $form = $this->get('form.factory')->createNamed('send_mail')
			->add('name',TextType::class,['label'=>'Имя', 'required' => false])
			->add('phone',TextType::class,['label'=>'Телефон'])
			->add('email',EmailType::class,['label'=>'Email', 'required' => false])
            ->add('city',TextType::class,['label'=>'Город проживания', 'required' => false])
            ->add('body',TextareaType::class,['label'=>'Пожелания', 'required' => false])
			->add('submit',SubmitType::class,['label'=>'Отправить'])
		;
        
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
			$name = $form->getData()['name'];
			$phone = $form->getData()['phone'];
			$email = $form->getData()['email'];
			$body = $form->getData()['body'];
			$city = $form->getData()['city'];
			
			$message = \Swift_Message::newInstance()
				->setSubject('Быстрое бронирование от '.date("Y-d-m H:i:s"))
				->setFrom(array('test-rech-agent@yandex.ru'=>'rech-agent.ru'))
				->setTo(['info@rech-agent.ru','spb@rech-agent.ru'])
				//->setTo('dk@made.ru.com')
				->setBcc('dk@made.ru.com')
				->setBody(
					$this->renderView(
						'BaseBundle:Quickly:email.html.twig',
						['name'=>$name,'phone'=>$phone,'email'=>$email, 'body'=>$body, 'city' => $city]
					),
					'text/html'
				)

			;
			$this->get('mailer')->send($message);
				
			$session->getFlashBag()->add(
				'flash',
				'Ваша заявка принята'
			);
			
			return $this->redirectToRoute('homepage');
			
		}
        return ['form'=>$form->createView()];
    }
}
