<?php

namespace LoadBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

use LoadBundle\Controller as Load;

class LoadVodohodShipCommand extends ContainerAwareCommand
{

	private $container; 
	


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            // a good practice is to use the 'app:' prefix to group all your custom application commands
            ->setName('loadvodohod:ship')
			->setDescription('Загрузка теплохода, его описания и круизы с сайта Водохода')
			->addArgument('id')	
        ;
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $id= $input->getArgument('id');

		$load = $this->getContainer()->get('load.loadvodohod');

        $output->writeln($load->load($id,false));
    }
	
}