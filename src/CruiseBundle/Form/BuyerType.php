<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BuyerType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
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
			->add('phone')
			->add('email')
		;
		*/
        $builder
			->add('name',null,['required'=>true])
			->add('lastName',null,['required'=>true])
			->add('fatherName',null,['required'=>true])
			->add('birthday',null,['widget' => 'single_text','required'=>true])
			->add('passSeria',null,['required'=>true])
			->add('passNum',null,['required'=>true])
			->add('passDate',null,['widget' => 'single_text','required'=>true])
			->add('passWho',null,['required'=>true])
			->add('phone',null,['required'=>true])
			->add('email',null,['required'=>true])
		;
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\Buyer'
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_buyer';
    }


}
