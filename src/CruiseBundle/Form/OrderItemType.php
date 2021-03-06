<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;



class OrderItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {


		//dump($options);
		
		$builder

			->add('orderItemPlaces',CollectionType::class,				
				[
					'entry_type' => OrderItemPlaceType::class,
					'entry_options' => ['label' => false , 'is_manager'=>$options['is_manager']],
				])
		;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\OrderItem',
			'is_manager' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_orderitem';
    }


}
