<?php
namespace CruiseBundle\Service;


use Symfony\Component\HttpFoundation\Response;

class ReportAgent
{
    private $doctrine;
    private $em;
    private $phpExcel;
    private $cruise;
    private $num2str;
	
	private $monthAssoc = [
					1=>'январь',
					'февраль',
					'март',
					'апрель',
					'май',
					'июнь',
					'июль',
					'август',
					'сентябрь',
					'октябрь',
					'ноябрь',
					'декабрь',
					];
	
	private $month2 = array(1 => "января", "февраля", "марта", "апреля", "мая", "июня", "июля", "августа", "сентября", "октября", "ноября", "декабря");

    public function __construct($doctrine, $phpExcel, $cruise, $num2str)
    {
        $this->doctrine = $doctrine;
        $this->phpExcel = $phpExcel;
        $this->num2str = $num2str;
        $this->cruise = $cruise;
        $this->em = $doctrine->getManager();
    }	
	
	

	public function report($agency = null, $year = null, $month = null )
	{
		
		if(($agency === null) || ($year === null) || ($month === null)) 
		return ['error'];

		$phpExcelObject = $this->phpExcel->createPHPExcelObject( __DIR__ .'/../Resources/report/report_agent.xlsx');
		
		
		$agency = $this->em->getRepository("CruiseBundle:Agency")->findOneById($agency);
		
		if(null === $agency)
		{
			return new Response("Агентство не найдено");
		}
		
		
		//$qb = $this->em->createQueryBuilder();
		
	
		$orders = $this->em->createQueryBuilder()
					->select('o,pay')
					->from("CruiseBundle:Ordering","o")
					->leftJoin("o.pays","pay")
					
					->where("pay.date LIKE '".$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT)."%' OR (pay.date IS NULL AND pay.created LIKE '".$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT)."%')")
					
					->andWhere("o.agency = ".$agency->getId())
					
					->andWhere("pay.isDelete = 0")
					->andWhere("o.active = 1")
					
					->getQuery()
					->getResult()
				;
		
		$orderPrices = [];
		$totalFee = 0;
		$totalPriceDiscount = 0;
		$totalPriceDiscountWithoutFee = 0;
		
		//dump($orders);
		
		foreach($orders as $order)
		{
			$orderPrice = $this->cruise->getOrderPrice($order);
			if( round(($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ']),2) <=  $orderPrice['itogo']['pay'] )
			{
				$orderPrices[] = $orderPrice;
				
				$totalFee += $orderPrice['itogo']['fee_summ'];
				$totalPriceDiscount += $orderPrice['itogo']['priceDiscount'];
				$totalPriceDiscountWithoutFee += $totalPriceDiscount - $totalFee;
			}
			
		}
		if(count($orderPrices) == 0)
		{
			return new Response("Оплаченных счетов не найдено");
		}



		
		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();			
		
		
		
		$aSheet->setCellValue('A2', "Отчет агента № ____" );
		$aSheet->setCellValue('A3', "между  ".$agency->getName()." (Агент) и ООО \"Речное Агентство\" (Компания)" );
		
		$num_dog =  $agency->getNumDog() === null ? '________' : $agency->getNumDog();
		$date_dog =  $agency->getDateDog() === null ? '________' : $agency->getDateDog()->format("d.m.Y");;

				
		$st = $agency->getName().", именуемое в дальнейшем \"Агент\", в лице ______________________________________________, ".
			"действующего на основании _______________, представляет, а ООО \"Речное Агентство\",".
			" именуемое в дальнейшем \"Компания\",  в лице Генерального директора Гладилиной Любови Владимировны, действующего на основании Устава,".
			" принимает настоящий отчет об исполнении агентского поручения по агентскому договору №{$num_dog} от $date_dog";

		$aSheet->setCellValue('A5', $st );	
		
		$st = "За ".$this->monthAssoc[$month]." " . $year ;
		$aSheet->setCellValue('A12', $st );		
		

		$row = 16;
		$col= count($orderPrices);
		
		if ($col > 1) $aSheet->insertNewRowBefore($row+1, $col - 1);	
		
		$i = 1;
		
		foreach($orderPrices as $orderPrice)
		{
			$numRooms = [];
			foreach($orderPrice['items'] as $item)
			{
				$numRooms[$item['number']] = $item['number'];
			}
			
			$aSheet->setCellValue("A$row", $i );
			$aSheet->setCellValue("B$row", $orderPrice['order']->getCruise()->getShip()->getName() );
			$aSheet->setCellValue("C$row", $orderPrice['order']->getCruise()->getStartDate()->format("d.m.Y") . " - " .$orderPrice['order']->getCruise()->getEndDate()->format("d.m.Y")  );

			$aSheet->setCellValue("D$row", "Заказ на круиз #".$orderPrice['order']->getId()." от ".$orderPrice['order']->getCreated()->format("Y-m-d"));

			$aSheet->setCellValue("E$row", implode(',',$numRooms) );
			
			$aSheet->setCellValue("F$row", $orderPrice['itogo']['priceDiscount'] );
			$aSheet->setCellValue("G$row", $orderPrice['itogo']['nds'] );
			$aSheet->setCellValue("H$row", $orderPrice['itogo']['fee_summ'] );
			$aSheet->setCellValue("I$row", round($orderPrice['itogo']['fee_summ']* $orderPrice['itogo']['nds'] / $orderPrice['itogo']['priceDiscount'] ,2));
			$aSheet->setCellValue("J$row", $orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'] );

		$row++;	
		$i++;	
		}


		$aSheet->setCellValue("F$row", $totalPriceDiscount);
		$aSheet->setCellValue("H$row", $totalFee);
		$aSheet->setCellValue("J$row", $totalPriceDiscountWithoutFee);



		$row += 2;

		$st = "Вознаграждение агента за ".$this->monthAssoc[$month]." " . $year ." составило $totalFee (". $this->num2str->num2str($totalFee).")";
		$aSheet->setCellValue("A$row", $st);		

		$summ_vozvrata = 0;
		$row += 2+9;
		$st ="Сумма уменьшения вознаграждения агента за ".$this->monthAssoc[$month]." " . $year ." составила  $summ_vozvrata (". $this->num2str->num2str($summ_vozvrata).")";
		$aSheet->setCellValue("A$row", $st);		
		
		
		$writer = $this->phpExcel->createWriter($phpExcelObject, 'Excel5');
		$response = $this->phpExcel->createStreamedResponse($writer);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment;filename=Отчёт агента за ' .$year ."-". str_pad($month, 2, '0', STR_PAD_LEFT). '.xls');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');		

		return $response;


	}	

	public function act($agency = null, $year = null, $month = null )
	{
		
		if(($agency === null) || ($year === null) || ($month === null)) 
		return ['error'];

		$phpExcelObject = $this->phpExcel->createPHPExcelObject( __DIR__ .'/../Resources/report/report_agent_act.xlsx');
		
		
		$agency = $this->em->getRepository("CruiseBundle:Agency")->findOneById($agency);
		
		if(null === $agency)
		{
			return new Response("Агентство не найдено");
		}

		$orders = $this->em->createQueryBuilder()
					->select('o,pay')
					->from("CruiseBundle:Ordering","o")
					->leftJoin("o.pays","pay")
					->where("pay.date LIKE '".$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT)."%' OR (pay.date IS NULL AND pay.created LIKE '".$year.'-'.str_pad($month, 2, '0', STR_PAD_LEFT)."%')")
					->andWhere("o.agency = ".$agency->getId())
					->andWhere("pay.isDelete = 0")
					->andWhere("o.active = 1")
					->getQuery()
					->getResult()
				;
		
		$orderPrices = [];
		$totalFee = 0;
		$totalPriceDiscount = 0;
		$totalPriceDiscountWithoutFee = 0;
		
		//dump($orders);
		
		foreach($orders as $order)
		{
			$orderPrice = $this->cruise->getOrderPrice($order);
			if( round(($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ']),2) <=  $orderPrice['itogo']['pay'] )
			{
				$orderPrices[] = $orderPrice;
				
				$totalFee += $orderPrice['itogo']['fee_summ'];
				$totalPriceDiscount += $orderPrice['itogo']['priceDiscount'];
				$totalPriceDiscountWithoutFee += $totalPriceDiscount - $totalFee;
			}
			
		}
		if(count($orderPrices) == 0)
		{
			return new Response("Оплаченных счетов не найдено");
		}



		
		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();			
		
		$aSheet->setCellValue('A1', "Акт оказанных услуг по реализации турпродукта от  ". cal_days_in_month(CAL_GREGORIAN, $month, $year). " ". $this->month2[$month]. " " .$year);
		
		
		$num_dog =  $agency->getNumDog() === null ? '________' : $agency->getNumDog();
		$date_dog =  $agency->getDateDog() === null ? '________' : $agency->getDateDog()->format("d.m.Y");;

		$st ="ООО «Речное Агентство», именуемое в дальнейшем «Компания», с одной стороны и ".$agency->getName().
			", именуемое в дальнейшем «Агент» с другой стороны, далее именуемые «Стороны», согласно агентскому договору №{$num_dog} от $date_dog".
			" за ".$this->monthAssoc[$month]." " . $year ."  составили настоящий Акт оказанных услуг по реализации турпродукта.";
		$aSheet->setCellValue('B4', $st );
	
	
		
		foreach($orderPrices as $orderPrice)
		{
//			$numRooms = [];
//			foreach($orderPrice['items'] as $item)
//			{
//				$numRooms[$item['number']] = $item['number'];
//			}
//			
//			$aSheet->setCellValue("B$row", $orderPrice['order']->getCruise()->getShip()->getName() );
//			$aSheet->setCellValue("C$row", $orderPrice['order']->getCruise()->getStartDate()->format("d.m.Y") . " - " .$orderPrice['order']->getCruise()->getEndDate()->format("d.m.Y")  );
//
//			$aSheet->setCellValue("D$row", "Заказ на круиз #".$orderPrice['order']->getId()." от ".$orderPrice['order']->getCreated()->format("Y-m-d"));
//
//			$aSheet->setCellValue("E$row", implode(',',$numRooms) );
//			
//			$aSheet->setCellValue("F$row", $orderPrice['itogo']['priceDiscount'] );
//			$aSheet->setCellValue("G$row", $orderPrice['itogo']['nds'] );
//			$aSheet->setCellValue("H$row", $orderPrice['itogo']['fee_summ'] );
//			$aSheet->setCellValue("I$row", round($orderPrice['itogo']['fee_summ']* $orderPrice['itogo']['nds'] / $orderPrice['itogo']['priceDiscount'] ,2));
//			$aSheet->setCellValue("J$row", $orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['fee_summ'] );
//
//		$row++;	
		}




		$st  ="Размер вознаграждения Агента составляет $totalFee (". $this->num2str->num2str($totalFee) .")";
		$aSheet->setCellValue('B6', $st  );
		
		
		$nds = round($orderPrice['itogo']['fee_summ']* $orderPrice['itogo']['nds'] / $orderPrice['itogo']['priceDiscount'] ,2);
		$st  ="в т.ч. НДС  $nds (". $this->num2str->num2str($nds) .")";
		$aSheet->setCellValue('B7', $st  );

	
		
		
		$writer = $this->phpExcel->createWriter($phpExcelObject, 'Excel5');
		$response = $this->phpExcel->createStreamedResponse($writer);
		$response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
		$response->headers->set('Content-Disposition', 'attachment;filename=Отчёт агента за ' .$year ."-". str_pad($month, 2, '0', STR_PAD_LEFT). '.xls');
		$response->headers->set('Pragma', 'public');
		$response->headers->set('Cache-Control', 'maxage=1');		

		return $response;


	}
	
}