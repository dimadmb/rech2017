<?php

namespace CruiseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use CruiseBundle\Entity\Pay;

class PayAppexController extends Controller
{
	

	private function curl_get_file_contents_starrus($command, $data = null)
	{
		$URL = "http://62.117.111.84:54444/fr/api/v2/".$command;
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		curl_setopt($c, CURLOPT_CONNECTTIMEOUT, 3);
		if(null != $data) curl_setopt($c, CURLOPT_POSTFIELDS, $data);
		$contents = curl_exec($c);
		curl_close($c);
		if ($contents) return $contents;
			else return FALSE;
	}
	
	
    /**
     * @Route("/payappex", name="payappex")
     */
    public function payAppexAction(Request $request)
	{
		if(!(($request->query->get('LOGIN') == "rechagent") &&  ($request->query->get('PASS') == "volgavolgareka")))
		{
			return new Response("EXIT");
		}
		$id = $request->query->get('CODE1');
		
		$em = $this->getDoctrine()->getManager();
		$order = $em->getRepository("CruiseBundle:Ordering")->findOneById($id);
		$cruise = $order->getCruise();
		$orderPrice = $this->get('cruise')->getOrderPrice($order);
		
		if($request->query->get('TYPE') == "1" )
		{
$answer = '<?xml version="1.0" encoding="windows-1251" ?>
<RESPONSE>
<RESULTCODE>0</RESULTCODE>
<RESULTMESSAGE>OK</RESULTMESSAGE>
<DATE>'.date("YmdHis").'</DATE>
<ADDINFO>
<AGENCY>
<NAME>Ђ–ечное јгентствої</NAME>
<BRANCH></BRANCH>
<INN>9710008811</INN>
</AGENCY>
<FULLPRICE>'.number_format($orderPrice['itogo']['priceDiscount'], 2, '.', '').'</FULLPRICE>
<CURRENCY>RUB</CURRENCY>
<AMOUNTTOPAY>'.number_format($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['pay'], 2, '.', '').'</AMOUNTTOPAY>
<EXCHANGERATE>1</EXCHANGERATE>
<AMOUNTTOPAYRUB>'.number_format($orderPrice['itogo']['priceDiscount'] - $orderPrice['itogo']['pay'], 2, '.', '').'</AMOUNTTOPAYRUB>
<AGENCYCOMISSION>0</AGENCYCOMISSION>
<STARTDATE>'.$cruise->getStartDate()->format("Ymd000000").'</STARTDATE>
<ENDDATE>'.$cruise->getEndDate()->format("Ymd000000").'</ENDDATE>
<TOURISTLIST>
<TOURIST>
<FIRSTNAME></FIRSTNAME>
<LASTNAME></LASTNAME>
<PATRONYMIC></PATRONYMIC>
<BIRTHDATE>19700101000000</BIRTHDATE>
</TOURIST>
</TOURISTLIST>
<PAYER>
<FIRSTNAME></FIRSTNAME>
<LASTNAME></LASTNAME>
<PATRONYMIC></PATRONYMIC>
<BIRTHDATE>19611019000000</BIRTHDATE>
</PAYER>
<SERVICELIST>
<SERVICE>'.iconv("utf-8","windows-1251",$cruise->getName()).'</SERVICE>
</SERVICELIST>
</ADDINFO>
</RESPONSE>	
';		
      $response = new Response($answer);
      $response->headers->set('Content-Type', 'xml');
      $response->headers->set('charset', 'windows-1251');
      //$response->headers->set('charset', 'utf-8');

	  
	  
	  return $response;

			return $this->render("CruiseBundle:Order:check.xml.twig", [
						'orderPrice'=>$orderPrice,
						'cruise'=>$cruise,
						], $response);
		}
		
		
		
		if($request->query->get('TYPE') == "2" )
		{
			
			$amount = $request->query->get('AMOUNT') / 100;
			$pay = new Pay();
			$pay
				->setAmount($amount)
				->setComment('ONLINE')
				->setOrder($order)
				->setDate(new \DateTime)
			;
			$em->persist($pay);
			$em->flush();
			
			$payid = $request->query->get('PAYID');
			
			
			// НУЖНО ПРОБИТЬ ЧЕК 
			
			$this->starrus($order, $orderPrice, $amount, $payid, $pay);
			


			$response = new Response();
			$response->headers->set('Content-Type', 'xml');
			$response->headers->set('charset', 'windows-1251');
				
			return $this->render("CruiseBundle:Order:pay.xml.twig", ['payid'=>$payid], $response);		
			
		}
		
		return new Response("NO");
	}


	public function starrus($order, $orderPrice, $amount, $payid, $pay)
	{
		
		$Lines = [];
		
		if( $orderPrice['itogo']['priceDiscount'] > $amount )
		{
			
			// частичная оплата
			
			$summ = $amount;
			
			$Lines[]= [
			"Qty"=> 1000,
			"Price"=> (int)($amount*100),
			"PayAttribute"=> 1,
			"TaxId"=> 4,
			"Description"=> "Частичная оплата по договору " .  $order->getId() ,
			];				
		}
		else
		{
			
			$summ = $orderPrice['itogo']['priceDiscount'];
			
			foreach($orderPrice['items'] as $item)
			{
				$Lines[]= [
				"Qty"=> (int)round(1000),
				"Price"=> (int)($item['priceDiscount']*100),
				"PayAttribute"=> 1,
				"TaxId"=> 4,
				"Description"=> "Договор " . $order->getId(). " т/х " . $order->getCruise()->getShip()->getName() . " каюта "   . " " . $order->getCruise()->getStartDate()->format("Y.m.d") ,
				];					
			}
		}
		
		$email = $order->getBuyer() === null ? "" : $order->getBuyer()->getEmail();
		if(($email === null) || ($email == ""))
		{
			$email = $order->getUser()->getEmail();
		}
		
		$req = [
		"RequestId"=> "rech-agent".$order->getId()."-".$payid,
		"Password"=> 5,
		"Lines"=> $Lines,
		"Cash"=> 0,
		"NonCash"=> [ (int)(100*$summ), 0,  0 ],
		"PhoneOrEmail"=> $email,
		"MaxDocumentsInTurn"=> 5000,
		"FullResponse"=> false
		];		
				
		$json=json_encode($req);
		
		$pay->setRequestStarrus($json);
		//dump($req);
		$ans = $this->curl_get_file_contents_starrus("Complex",$json);
		
		
		$pay->setResponseStarrus($ans);
		
		$this->getDoctrine()->getManager()->flush();
		
	}	
	
}
