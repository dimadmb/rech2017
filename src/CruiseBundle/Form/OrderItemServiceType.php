<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemServiceType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       
		if($options['is_manager'])
		$builder
				->add('name')
				->add('priceValue')
				->add('isFee', null , ['label'=>'Агентское вознаграждение'])
				->add('isPermanentDiscount', null , ['label'=>'Скидка постоянного'])
				->add('isSesonDiscount', null , ['label'=>'Сезонная скидка'])
				//->add('active')
				//->add('order')
			;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\OrderItemService',
			'is_manager' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_orderitemservice';
    }


}
