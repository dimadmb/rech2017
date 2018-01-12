<?php

namespace CruiseBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

use CruiseBundle\Form\BuyerType;

use Symfony\Component\Form\FormEvents;
use CruiseBundle\Entity\Agency;
use Doctrine\ORM\EntityRepository;


use CruiseBundle\Service\Cruise;

class OrderingType extends AbstractType
{

	private $cruiseService;
	
    public function __construct()
    {
      //  $this->cruiseService = new \CruiseBundle\Service\Cruise();
    }	


    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
       	   
	   if($builder->getData()->getBuyer() !== null)
		{
			$builder->add('buyer', BuyerType::class, ['label'=>'Покупатель'] );
		}
		
		$builder


			->add('commentUser')

			->add('orderItems',CollectionType::class,
				[
					'entry_type' => OrderItemType::class,
					'entry_options' => ['label' => false , 'is_manager'=>$options['is_manager']],
				]
			)
			->add('submit',SubmitType::class)
			
			

		;
		
		if($options['is_manager'])
		{
			$builder
					->add('commentManager')
					->add('fee')
					->add('permanentDiscount')
					->add('sesonDiscount')
					->add('agency',EntityType::class, [
								'class' => Agency::class,
								'query_builder' => function(EntityRepository $er)
								{
									return $er->createQueryBuilder('a')
									->where('a.active = 1');
								},
								'required' => false
								])
					->add('region')
			;			
		}
		
		
		if( !$options['is_manager'] and ($builder->getData()->getAgency() === null))
		{
			$builder
					->add('permanentRequest')		;	
		}
		
		

		
    }
    
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'CruiseBundle\Entity\Ordering',
			'is_manager' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'cruisebundle_ordering';
    }


}
