<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use CruiseBundle\Entity\Price;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class OrderItemPlaceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
      
            $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
				$data  = $event->getData();
				$form = $event->getForm();
				
					
				
				
				
				$order = $data->getOrderItem()->getOrdering();
				$cabin = $data->getOrderItem()->getRoom()->getCabin();
				
				$prices = $cabin->getPrices();

				
				$form->add('price', EntityType::class, [
						'class' => Price::class,
						'choices' => $prices,
						'choice_attr' => function ($val, $key, $index) {
								return ['data-price' => $val->getPrice()];
							},
						
					])
					;	
					
			

            });
		
		$builder
			->add('name',null,['required'=>true])
			->add('lastName',null,['required'=>true])
			->add('fatherName',null,['required'=>true])
			->add('birthday',null,['widget' => 'single_text','required'=>true])
			->add('passSeria',null,['required'=>true])
			->add('passNum',null,['required'=>true])
			->add('passDate',null,['widget' => 'single_text','required'=>true])
			->add('passWho',null,['required'=>true])
			
			;
			
/*
				

		
		$builder
			->add('name')
			->add('lastName')
			->add('fatherName')
			->add('birthday',null,['widget' => 'single_text'])
			->add('passSeria')
			->add('passNum')
			->add('passDate',null,['widget' => 'single_text'])
			->add('passWho')
			
			;
*/			
			
		
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\OrderItemPlace'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_orderitemplace';
    }


}
