<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use PhpOffice\PhpWord;


    /**
     * @Route("report")
     */

class ReportController extends Controller
{





	/**
     * @Route("/boarding_card/{hash}", name="boarding_card")
     */
    public function boardingCardAction($hash)
	{
		$id = $this->get('cruise')->hashPlaceDecode($hash);
		$em = $this->getDoctrine()->getManager();
		
		$orderItemPlace = $em->getRepository("CruiseBundle:OrderItemPlace")->findOneById($id);
		$order = $orderItemPlace->getOrderItem()->getOrdering();
		$cruise = $order->getCruise();

		$dayFirst = $em->getRepository("CruiseBundle:ProgramItem")->findOneBy(["cruise"=>$cruise],['dateStart'=>'ASC']);
		$dayLast = $em->getRepository("CruiseBundle:ProgramItem")->findOneBy(["cruise"=>$cruise],['dateStart'=>'DESC']);


		
		if(($order->getRegion() !== null) && ($order->getRegion()->getId() == 2))
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report//boarding_card_spb.xlsx');
		}
		else
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report//boarding_card.xlsx');
		}

		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();

		// дата и время регистрации 
			
		$aSheet->setCellValue(   'J12', $dayFirst ? $dayFirst->getDateStop()->format("d.m.y H:i") : "");
		
		$aSheet->setCellValue(   'D14', $dayFirst ? $dayFirst->getDateStop()->format("H:i") : "");
		$aSheet->setCellValue(   'D15', $dayFirst ? $dayFirst->getPlaceTitle() : "");
		
		$aSheet->setCellValue(   'J14', $dayLast ? $dayLast->getPlaceTitle() : "");
		
		$aSheet->setCellValue(   'D16', $orderItemPlace->getOrderItem()->getRoom()->getCabin()->getDeck()->getName() );
		$aSheet->setCellValue(   'D17', $orderItemPlace->getOrderItem()->getRoom()->getCabin()->getType()->getName() );
		
		$aSheet->setCellValue(   'A7', "номер договора ". $order->getId() );
		
		$aSheet->setCellValue(   'D10', $cruise->getShip()->getName() );
		
		$aSheet->setCellValue(   'D11', $cruise->getName() );
		$aSheet->setCellValue(   'D13', $dayFirst ? $dayFirst->getDateStop()->format("d.m.y") : "");
		$aSheet->setCellValue(   'J13', $dayLast ? $dayLast->getDateStart()->format("d.m.y H:i") : "");


		$aSheet->setCellValue(   'D12', $dayFirst ? $dayFirst->getDateStop()->modify('-2 hour')->format("d.m.y H:i") : "");	

		$aSheet->setCellValue(   'J15', $orderItemPlace->getOrderItem()->getRoom()->getNumber() );
		
		$aSheet->setCellValue(   'J16',  $orderItemPlace->getOrderItem()->getPlace()->getRpName() ); 
		
		if(null !== $orderItemPlace->getPrice())
		$aSheet->setCellValue(   'J17',  $orderItemPlace->getPrice()->getTariff()->getName() ); 
	

		$fio = $orderItemPlace->getLastName() .' '. $orderItemPlace->getName() . ' '. $orderItemPlace->getFatherName();
		if(null !== $orderItemPlace->getBirthday())
		{
			$fio .= ', дата рождения: '. $orderItemPlace->getBirthday()->format("d.m.Y") .' г';
		}
		$aSheet->setCellValue(   'G18', $fio  );

		if(/*(null !== $orderItemPlace->getPassSeria()) && */ (null !== $orderItemPlace->getPassNum()) &&  (null !== $orderItemPlace->getPassDate()) &&  (null !== $orderItemPlace->getPassWho())   )

		    $data = $orderItemPlace->getPassSeria()
                .', номер '.$orderItemPlace->getPassNum().', выдан '.$orderItemPlace->getPassDate()->format("d.m.Y").' г, кем выдан: '.$orderItemPlace->getPassWho();
            $data2 = $orderItemPlace->getPassSeria().', номер '.$orderItemPlace->getPassNum();
        $aSheet->setCellValue(   'G19', $data ?? $data2 );



		/* $aSheet->setCellValue(   'G19',
			$orderItemPlace->getTypeDoc()->getName().', серия '.
			$orderItemPlace->getPassSeria()
			.', номер '.$orderItemPlace->getPassNum().', выдан '.$orderItemPlace->getPassDate()->format("d.m.Y").' г, кем выдан: '.$orderItemPlace->getPassWho()
		);*/

		
		
		if(($order->getAgency() === null) && ($order->getBuyer() !== null) )
		{
			$aSheet->setCellValue(   'D32', $order->getBuyer()->getLastName() .' '. $order->getBuyer()->getName() . ' '. $order->getBuyer()->getFatherName() );
			$aSheet->setCellValue(   'D33', " ".(string)$order->getBuyer()->getPhone() );			
		}	
		
		if(($order->getAgency() !== null) )
		{
			$aSheet->setCellValue(   'I32', $order->getAgency()->getName() );
			
			$user = $this->getUser();
			if($user !== null)
			{
				$aSheet->setCellValue(   'I33', $user->getLastName(). ' ' . $user->getFirstName(). ' ' . $user->getFatherName() );		
			}
			
			//	
		}


		
		//dump($orderItemPlace);
		//dump($cruise);
		
		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel2007');
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment;filename=посадочный талон ' . $id . '.xlsx');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');		

		return $response;

		
		return new Response($id);
		
	}


	/**
     * @Route("/contract_user/{hash}", name="contract_user")
     */
    public function contractUserAction($hash)
	{
		$id = $this->get('cruise')->hashOrderDecode($hash);
		$month  = array ("01" =>"января","02"=>"февраля","03"=>"марта","04"=>"апреля","05"=>"мая","06"=>"июня","07"=>"июля","08"=>"августа","09"=>"сентября","10"=>"октября","11"=>"ноября","12"=>"декабря");
		
		$em = $this->getDoctrine()->getManager();
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		$items = $this->get('cruise')->getOrderPrice($order);
		
		$dayFirst = $em->getRepository("CruiseBundle:ProgramItem")->findOneBy(["cruise"=>$order->getCruise()],['dateStart'=>'ASC']);
		$dayLast = $em->getRepository("CruiseBundle:ProgramItem")->findOneBy(["cruise"=>$order->getCruise()],['dateStart'=>'DESC']);
		

		if(($order->getRegion() !== null) && ($order->getRegion()->getId() == 2))
		{
			$phpTemplateObject = $this->get('phpword')->createTemplateObject( __DIR__ .'/../Resources/report/contract_spb.docx');
		}
		else
		{
			$phpTemplateObject = $this->get('phpword')->createTemplateObject( __DIR__ .'/../Resources/report/contract.docx');
		}

		
		
		
		$passDate  = ($order->getBuyer()->getPassDate() !== null) ? $order->getBuyer()->getPassDate()->format("d.m.Y") .'г.' : "";
		
		$phpTemplateObject->setValue('ID', $id);
		$phpTemplateObject->setValue('DAY', $order->getCreated()->format("d"));
		$phpTemplateObject->setValue('MONTH', $month[$order->getCreated()->format("m")]);
		$phpTemplateObject->setValue('YEAR', $order->getCreated()->format("Y"));
		$phpTemplateObject->setValue('FIO', $order->getBuyer()->getLastName().' '.$order->getBuyer()->getName().' '.$order->getBuyer()->getFatherName());
		$phpTemplateObject->setValue('ADDRESS', $order->getBuyer()->getAddress());

		$phpTemplateObject->setValue('PASSPORT', $order->getBuyer()->getPassSeria() .' № '. $order->getBuyer()->getPassNum() .', выдан '. 	$order->getBuyer()->getPassWho() .' ' . $passDate);
		$phpTemplateObject->setValue('PHONE', $order->getBuyer()->getPhone());
		$phpTemplateObject->setValue('WAY', $order->getCruise()->getName());
		$phpTemplateObject->setValue('TEPLOHOD', $order->getCruise()->getShip()->getName());
		$phpTemplateObject->setValue('PORT1', $dayFirst ? $dayFirst->getPlaceTitle() : "");
		$phpTemplateObject->setValue('PORT2', $dayLast ? $dayLast->getPlaceTitle() : "");
		$phpTemplateObject->setValue('TIME1', $dayFirst ? $dayFirst->getDateStop()->format("H:i") : "");
		$phpTemplateObject->setValue('TIME2', $dayLast ? $dayLast->getDateStart()->format("H:i") : "");
		$phpTemplateObject->setValue('DATE1', $order->getCruise()->getStartDate()->format("d.m.Y"));
		$phpTemplateObject->setValue('DATE2', $order->getCruise()->getEndDate()->format("d.m.Y"));
		$phpTemplateObject->setValue('TIME_REG', $dayFirst ? $dayFirst->getDateStop()->modify('-2 hour')->format("H:i") : "");
		$phpTemplateObject->setValue('DAYS', $order->getCruise()->getDayCount());
		$phpTemplateObject->setValue('DAYS_1', $order->getCruise()->getDayCount() - 1 );

		
		$phpTemplateObject->cloneRow('NUM', $items['itogo']['countPass']);
		
		$i = 1;
		foreach($items['items'] as $item)
		{
			if($item['orderItemPlace'] !== null)
			{
				$phpTemplateObject->setValue("NUMITEM#$i", $i );
				$phpTemplateObject->setValue("NUM#$i", $item['number'] );
				$phpTemplateObject->setValue("SUM#$i", $item['priceDiscount'] );
				$phpTemplateObject->setValue("CLASS#$i", $item['cabinType'] );
				$phpTemplateObject->setValue("DECK#$i", $item['cabinDeck'] );
				
				$phpTemplateObject->setValue("PASS#$i", $item['orderItemPlace']->getLastName(). " ".$item['orderItemPlace']->getName(). " ".$item['orderItemPlace']->getFatherName() );
				
				
				$i++;				
			}
		}
		

		$phpTemplateObject->cloneRow('SERVICE_NAME', $items['itogo']['countService']);
		
		$i = 1;
		foreach($items['items'] as $item)
		{
			if($item['orderItemPlace'] === null)
			{
				$phpTemplateObject->setValue("SERVICE_NAME#$i", $item['name'] );
				$phpTemplateObject->setValue("SERVICE_PRICE#$i", $item['priceDiscount'] );
				$i++;				
			}
		}			

		$phpTemplateObject->setValue('SUM_ALLPASS', $items['itogo']['priceDiscountPass'] );
		$phpTemplateObject->setValue('SUM_ALL', $items['itogo']['priceDiscount'] );
		$phpTemplateObject->setValue('SUM_ALL_PROPIS', $this->get('num2str')->num2str($items['itogo']['priceDiscount']) );
		
		
		$phpTemplateObject->setValue('NDSPASS', $items['itogo']['ndsPass'] );
		$phpTemplateObject->setValue('NDS', $items['itogo']['nds'] );
		$phpTemplateObject->setValue('NDS_PROPIS', $this->get('num2str')->num2str($items['itogo']['nds']) );
		
		header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
		header('Content-Disposition: attachment;filename="Договор'.$id.'.docx"');
		$phpTemplateObject->saveAs('php://output');
		die();
		/*
		$phpWordObject = $this->get('phpword')->getPhpWordObjFromTemplate($phpTemplateObject);
		$writer = $this->get('phpword')->createWriter($phpWordObject, 'Word2007');
		

		
		$response = $this->get('phpword')->createStreamedResponse($writer);
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'dogovor.docx'
        );
        $response->headers->set('Content-Type', 'application/msword');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response; 		
		*/
	}


    /**
     * @Route("/invoice_agency/{hash}", name="invoice_agency")
     */
    public function invoiceAgencyAction($hash)
	{
		$id = $this->get('cruise')->hashOrderDecode($hash);
		
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		$items = $this->get('cruise')->getOrderPrice($order);
		
		if(($order->getRegion() !== null) && ($order->getRegion()->getId() == 2))
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report/invoice_agent_spb.xlsx');
		}
		else
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report/invoice_agent.xlsx');
		}
		
		
		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();	

		$dateNow = new \DateTime;
		
		//$aSheet->setCellValue("B11", "Оплата по счету № $id от ".$order->getCreated()->format('d.m.Y').'г.');
		//$aSheet->setCellValue("B13", "Счет на оплату № $id от ".$order->getCreated()->format('d.m.Y').'г.');
		$aSheet->setCellValue("B11", "Оплата по счету № $id от ". $dateNow->format('d.m.Y').'г.');
		$aSheet->setCellValue("B13", "Счет на оплату № $id от ".$dateNow->format('d.m.Y').'г.');
		$aSheet->setCellValue("H17", $order->getAgency()->getName() . ", ИНН " . $order->getAgency()->getInn(). ", КПП " .  $order->getAgency()->getKpp(). ", " .  $order->getAgency()->getUrAddress(). ", тел. ".  $order->getAgency()->getPhone());
		
		$col = count($items['items']);

		if ($col > 1) $aSheet->insertNewRowBefore(21, $col - 1);

		$row = 20;
		
		foreach($items['items'] as $item)
		{
			$aSheet->mergeCells("B{$row}:C{$row}");
			$aSheet->mergeCells("D{$row}:O{$row}");
			$aSheet->mergeCells("P{$row}:R{$row}");
			$aSheet->mergeCells("S{$row}:T{$row}");
			$aSheet->mergeCells("U{$row}:X{$row}");
			$aSheet->mergeCells("Y{$row}:AC{$row}");
			$aSheet->mergeCells("AD{$row}:AG{$row}");
			$aSheet->mergeCells("AH{$row}:AL{$row}");
			$aSheet->mergeCells("AM{$row}:AR{$row}");
			$aSheet->mergeCells("AS{$row}:AV{$row}");
			$aSheet->mergeCells("AW{$row}:AZ{$row}");
			$aSheet->mergeCells("BA{$row}:BE{$row}");


			$aSheet->setCellValue("B$row", $row - 19);
			
			
			$aSheet->setCellValue("D$row", $item['name']);


			$aSheet->setCellValue("U$row", $item['price'] );
			$aSheet->setCellValue("AD$row", $item['discount'] );

			$aSheet->setCellValue("AS$row", 'Без НДС');
			$aSheet->setCellValue("S$row", 'шт');

			// добавлено
			$aSheet->setCellValue("P$row","1");
			
			$aSheet->setCellValue("Y$row", $item['price']);
			
			$aSheet->setCellValue("AH$row", $item['fee']);
			
			if($item['nds'] !== 0)
			{
				$aSheet->setCellValue("AS$row", '18%');
				$aSheet->setCellValue("AW$row", $item['nds']);
			}
			
			
			$aSheet->setCellValue("AM$row", $item['fee_summ']);
			
			
			$aSheet->setCellValue("BA$row", $item['priceDiscount'] - $item['fee_summ']);


			$row++;
		}

		$row++;
		
		$itogo = $items['itogo']['priceDiscount'] - $items['itogo']['fee_summ'];
		
		$aSheet->setCellValue("Y$row", $items['itogo']['price']);
		$aSheet->setCellValue("AM$row", $items['itogo']['fee_summ']);
		$aSheet->setCellValue("BA$row", $itogo );

		

		//$aSheet->setCellValue("BA$row", "");

		$row ++;
		$aSheet->mergeCells("BA{$row}:BE{$row}");
		$aSheet->setCellValue("BA$row", $items['itogo']['nds']);
		$row ++;

		$aSheet->setCellValue("BA$row", $itogo );
		$row++;

		$aSheet->setCellValue("BA$row", $items['itogo']['fee_summ'] );

		$row++;

		$st = "Всего наименований $col , на сумму ". number_format($itogo,2,'.' ,' ') ." RUB ";

		$aSheet->setCellValue("B$row", $st);

		$row++;


	//$itogo =  floor($itogo);
		$st =$this->get('num2str')->num2str($itogo);


		$aSheet->setCellValue("B$row", $st);

		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment;filename=Счёт ' . $id . '.xls');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');		

		return $response;		
	}




	/**
     * @Route("/invoice_user/{hash}", name="invoice_user")
     */
    public function invoiceUserAction($hash)
	{
		$id = $this->get('cruise')->hashOrderDecode($hash);
		
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		$items = $this->get('cruise')->getOrderPrice($order);
		

		
		if(($order->getRegion() !== null) && ($order->getRegion()->getId() == 2))
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report/invoice_fiz_spb.xlsx');
		}
		else
		{
			$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report/invoice_fiz.xlsx');
		}
		
		
		
		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();	


		$aSheet->setCellValue("B11", "Оплата по счету № $id от ".$order->getCreated()->format('d.m.Y').'г.');
		$aSheet->setCellValue("B13", "Счет на оплату № $id от ".$order->getCreated()->format('d.m.Y').'г.');
		$aSheet->setCellValue("H17", $order->getBuyer()->getLastName().' '.$order->getBuyer()->getName().' '.$order->getBuyer()->getFatherName());
		
		$col = count($items['items']);

		if ($col > 1) $aSheet->insertNewRowBefore(21, $col - 1);

		$row = 20;
		
		foreach($items['items'] as $item)
		{
			$aSheet->mergeCells("B{$row}:C{$row}");
			$aSheet->mergeCells("D{$row}:O{$row}");
			$aSheet->mergeCells("P{$row}:R{$row}");
			$aSheet->mergeCells("S{$row}:T{$row}");
			$aSheet->mergeCells("U{$row}:X{$row}");
			$aSheet->mergeCells("Y{$row}:AC{$row}");
			$aSheet->mergeCells("AD{$row}:AG{$row}");
			$aSheet->mergeCells("AH{$row}:AK{$row}");
			$aSheet->mergeCells("AL{$row}:AO{$row}");
			$aSheet->mergeCells("AP{$row}:AT{$row}");
			//$aSheet->mergeCells("AW{$row}:AZ{$row}");
			//$aSheet->mergeCells("BA{$row}:BE{$row}");


			$aSheet->setCellValue("B$row", $row - 19);
			
			
			$aSheet->setCellValue("D$row", $item['name']);


			$aSheet->setCellValue("U$row", $item['price'] );
			$aSheet->setCellValue("AD$row", $item['discount'] );

			$aSheet->setCellValue("AH$row", 'Без НДС');
			$aSheet->setCellValue("S$row", 'шт');

			// добавлено
			$aSheet->setCellValue("P$row","1");
			
			$aSheet->setCellValue("Y$row", $item['price']);
			
			//$aSheet->setCellValue("AH$row", $item['fee']);
			
			if($item['nds'] !== 0)
			{
				$aSheet->setCellValue("AH$row", '18%');
				$aSheet->setCellValue("AL$row", $item['nds']);
			}
			
			
			//$aSheet->setCellValue("AM$row", $item['fee_summ']);
			
			
			$aSheet->setCellValue("AP$row", $item['priceDiscount'] - $item['fee_summ']);


			$row++;
		}

		$row++;
		
		$itogo = $items['itogo']['priceDiscount'] - $items['itogo']['fee_summ'];
		
		$aSheet->setCellValue("Y$row", $items['itogo']['price']);
		//$aSheet->setCellValue("AM$row", $items['itogo']['fee_summ']);
		$aSheet->setCellValue("AP$row", $itogo );

		

		//$aSheet->setCellValue("BA$row", "");

		$row ++;
		$aSheet->mergeCells("AP{$row}:AT{$row}");
		$aSheet->setCellValue("AP$row", $items['itogo']['nds']);
		$row ++;

		$aSheet->setCellValue("AP$row", $itogo );
		$row++;

		//$aSheet->setCellValue("AP$row", $items['itogo']['fee_summ'] );

		$row++;

		$st = "Всего наименований $col , на сумму ". round($itogo,3) ." RUB";

		$aSheet->setCellValue("B$row", $st);

		$row++;


	//$itogo =  floor($itogo);
		$st =$this->get('num2str')->num2str($itogo);


		$aSheet->setCellValue("B$row", $st);


		$writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
		$response = $this->get('phpexcel')->createStreamedResponse($writer);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment;filename=Счёт ' . $id . '.xls');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');		

		return $response;
	}	
}
