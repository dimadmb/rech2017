<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;


use Symfony\Component\Form\Extension\Core\Type\HiddenType;

class PayType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('order',null,[ 'attr'=>['style'=>'display:none;'], 'label'=>''])
			->add('comment')
			->add('amount')
			->add('date',null,['widget' => 'single_text',])
			->add('submit',SubmitType::class, [
				'label'=>'Добавить оплату',
				'attr'=>['class'=>'btn btn-primary']
			])
			;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\Pay'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_pay';
    }


}
