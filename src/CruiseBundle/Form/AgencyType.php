<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AgencyType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
			->add('shortName')
			->add('name')
			->add('bankName')
			->add('rs')
			->add('ks')
			->add('bik')
			->add('inn')
			->add('kpp')
			->add('urAddress')
			->add('faktAddress')
			->add('phone')
			->add('email')
			->add('fee')
			->add('numDog')
			->add('dateDog')

			->add('auth')
			->add('active')
			->add('region')
			
		;	
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\Agency'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_agency';
    }


}
