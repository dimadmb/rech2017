<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


    /**
     * @Route("report")
     */

class ReportController extends Controller
{
    /**
     * @Route("/invoice_user/{id}", name="invoice_user")
     */
    public function testAction($id)
	{
		$em = $this->getDoctrine()->getManager();
		
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		$items = $this->get('cruise')->getOrderPrice($order);
		
		$phpExcelObject = $this->get('phpexcel')->createPHPExcelObject( __DIR__ .'/../Resources/report/invoice_fiz.xlsx');
		
		$phpExcelObject->setActiveSheetIndex(0);
		$aSheet = $phpExcelObject->getActiveSheet();	


	$aSheet->setCellValue("B11", "Оплата по счету № $id от ".$order->getCreated()->format('d.m.Y').'г.');
	$aSheet->setCellValue("B13", "Счет на оплату № $id от ".$order->getCreated()->format('d.m.Y').'г.');
	$aSheet->setCellValue("H17", "Покупатель: ".$order->getBuyer()->getLastName().' '.$order->getBuyer()->getName().' '.$order->getBuyer()->getFatherName());
	
	
	$col = count($items['items']);
	if ($col > 1) $aSheet->insertNewRowBefore(21, $col - 1);
	$row = 20;
	
	foreach($items['items'] as $item)
	{
		$aSheet->mergeCells("B{$row}:C{$row}");
		$aSheet->mergeCells("D{$row}:X{$row}");
		$aSheet->mergeCells("Y{$row}:AA{$row}");
		$aSheet->mergeCells("AB{$row}:AC{$row}");
		$aSheet->mergeCells("AD{$row}:AG{$row}");
		$aSheet->mergeCells("AH{$row}:AL{$row}");
		$aSheet->mergeCells("AM{$row}:AR{$row}");
		$aSheet->mergeCells("AS{$row}:AW{$row}");
		
		$aSheet->setCellValue("B$row", $row - 19);
		
		$aSheet->setCellValue("D$row", $item['name']);

		$aSheet->setCellValue("Y$row", "1");
		$aSheet->setCellValue("AB$row", "шт");
		$aSheet->setCellValue("AD$row",  $item['priceDiscount']);
		
		
		

		$aSheet->setCellValue("AS$row", $item['priceDiscount']);

		$aSheet->getRowDimension($row)->setRowHeight(20);

		$row++;
		
	}

	$row++;
	$aSheet->setCellValue("AS$row", $items['itogo']['priceDiscount']);
	$row++;
	$row++;
	$aSheet->setCellValue("AS$row", $items['itogo']['priceDiscount']);

	$row++;


	//$itogo = $items['itogo']['priceDiscount'];//num2str($all_sum);
	$itogo = $this->get('num2str')->num2str($items['itogo']['priceDiscount']);
	
	$st = "Всего наименований $col , на сумму ".$itogo;

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
