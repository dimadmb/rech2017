<?php

namespace AdminBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;

use CruiseBundle\Entity\Agency;
use CruiseBundle\Entity\Ordering;
use BaseBundle\Entity\Page;
use BaseBundle\Entity\Image;
use CruiseBundle\Entity\RoomDiscount;

class ParseController extends Controller
{
	
	const BASE_URL_KEY = "http://api.infoflot.com/JSON/68a3d0c23cf277febd26dc1fa459787522f32006";
	
	public function curl_get_file_contents($URL)
	{
		$c = curl_init();
		curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_URL, $URL);
		$contents = curl_exec($c);
		curl_close($c);

		if ($contents) return $contents;
			else return FALSE;
	}
	
			
	
	
	/**
	 * @Route("/infoflot_ship/{ship_id}", name="infoflot_ship" )
	 */
	public function getCruisesInfoflot($ship_id)
	{
		$url = self::BASE_URL_KEY."/Cabins/$ship_id/";
		$ans = $this->curl_get_file_contents($url);
		
		$ans = json_decode($ans, true);
		
		$kauta_id = null;
		
		foreach($ans as $id => $kauta)
		{
			if($kauta['name'] == '202')
			{
				$kauta_id = $id;
			}
		}
		
		
		dump($kauta_id);
		dump($ans);
		return new Response("OK");
	}
	
/*	
	
	const BASE_URL_KEY = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&";
	
	public function URL2XML( $url )
	{
		$string = $this->curl_get_file_contents($url);
		return simplexml_load_string($string);
	}	
	
	// ПОЛУЧИТЬ СПИСОК КРУИЗОВ ТЕПЛОХОДА
	public function getCruises($ship_id)
	{
		$url = self::BASE_URL_KEY."request=tours&routedetail=true&shipid=".$ship_id."&tariffs=true";
		return $this->URL2XML( $url );
	}	

	
	// ПОЛУЧИТЬ ИНФОРМАЦИЮ О ТЕПЛОХОДЕ
	public function getShip($ship_id)
	{
		$url = self::BASE_URL_KEY."request=ship&shipid=".$ship_id."&cabins=true";
		return $this->URL2XML( $url );
	}	
*/	
	/**
	 * @Route("/mosturflot_ship/{ship_id}", name="mosturflot_ship" )
     */	
	public function mosturflotShipAction($ship_id)	
	{
		$cruises = $this->getShip($ship_id);
		
		dump($cruises);
		
		return new Response("OK");
	}	
	/**
	 * @Route("/mosturflot/{ship_id}", name="mosturflot" )
     */	
	public function mosturflotAction($ship_id)	
	{
		$cruises = $this->getCruises($ship_id);
		
		dump($cruises);
		
		return new Response("OK");
	}
	
	/**
	 * @Route("/invoice_parse/{invoice_id}", name="invoice_parse" )
     */		
	public function invoiceParse($invoice_id = null)
	{
		$em = $this->getDoctrine()->getManager();
		$em_booking = $this->getDoctrine()->getManager('booking');	

		$connection_booking = $em_booking->getConnection();

		$sql = "
			select  id_tur
			from aa_schet
			where id = $invoice_id
		";	
		$statement = $connection_booking->prepare($sql);
		$statement->execute();		
		$res = $statement->fetchColumn();	
		
		dump($res);
		
		
		$cruise = $em->getRepository("CruiseBundle:Cruise")->findOneById($res);
		
		dump($cruise); 
		
		$sql = "
			select * 
			from aa_order
			where aa_order.id_schet = $invoice_id
		";	
		$statement = $connection_booking->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();	

		dump($results);
		
		//$order = new Ordering();
		$arr = [];
		
		foreach($results as $result)
		{
			///  создать заказ 
			//$room = $em->getRepository("CruiseBundle:ShipRoom")->findOneByNumber($result['num']);
			
			$room = $em->createQueryBuilder()
					->select('r')
					->from("CruiseBundle:ShipRoom", "r")
					->leftJoin("r.cabin","cab")
					->leftJoin("cab.ship" , "ship")
					->leftJoin("ship.cruises","c")
					
					->where("c.id = ".$res)
					->andWhere("r.number = ".$result['num'])
					->getQuery()
					->getOneOrNullResult()
				;
			
			
			$arr[$room->getId()] =  $result['places'];
		}
		dump(http_build_query(['rooms'=>$arr,'cruise'=>$cruise->getId()]));
		
		return new Response("OK");		
	}
	
	/**
	 * @Route("/del_sold", name="del_sold" )
     */	
	public function delSoldAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$em_ra = $this->getDoctrine()->getManager('booking');
		
		$connectionB = $em_ra->getConnection();
		$connection  = $em->getConnection();
		
		$sql = "
			select  aa_schet.id_tur , aa_discount.num
			from aa_schet 
			left join aa_order ON aa_order.id_schet = aa_schet.id
			left join aa_discount ON aa_discount.id_tur = aa_schet.id_tur and aa_discount.num = aa_order.num 
			where aa_discount.id_tur is not null
			and is_delete = 0
			and aa_discount.id_tur >= 5589
		";	
		$statement = $connectionB->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();	

		//dump($results);
		
		
		$idD = [];
		foreach($results as $result)
		{
			$sql = 	"
				SELECT `room_discount`.id 
				FROM `room_discount` 
				LEFT JOIN ship_room ON ship_room.id = room_discount.room_id
				WHERE room_discount.cruise_id = ".$result['id_tur']."
				And ship_room.number = 	".$result['num']."		
			";
			$statement = $connection->prepare($sql);
			$statement->execute();
			$res = $statement->fetchColumn();	
			if($res)
			$idD[] 	= $res;		
			
		}
		
		dump($idD);
		
		foreach($idD as $id)
		{
			$connection->delete('room_discount', array('id' => $id));
		}
		
		return new Response("OK");		
		
	}	
	/**
	 * @Route("/pageIn", name="pageIn" )
     */	
	public function pageInAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$em_ra = $this->getDoctrine()->getManager('ra');
		
		$connection = $em_ra->getConnection();
		
		$sql = "
			SELECT *FROM 	document
			WHERE category_id IN (1003)
		";
		
		$statement = $connection->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();
		
		$pages = [];
		
		
		$parent_url = 'info/river';
		
		$pageParent = $em->getRepository('BaseBundle:Page')->findOneByFullUrl($parent_url);
		
		$base_url = 'https://www.rech-agent.ru';
		
		
		
		$dir = (__DIR__).'/../../../web/files';
		
		foreach($results as $result)
		{
			$page = new Page();
			

			$arr = explode('/',$result['url']);
			
			$alias = end($arr);
			
			
			$page
				->setTitle($result['title'])
				->setDescription($result['description'])
				->setKeywords($result['keywords'])
				->setParentUrl($parent_url)
				->setFullUrl($parent_url.'/'.$alias)
				->setLocalUrl($alias)
				->setBody($result['body'])
				->setH1($result['contentTitle'])
				->setName($result['contentTitle'])
				->setParent($pageParent)
				->setIsMenu(false)
				->setIsFolder(false)
				
			;	
			$em->persist($page);
			
			
			
			//$em->flush();
			
			$sql = "
				SELECT * FROM 	photo
				WHERE document_id = ".$result['id']."
			";
			
			$statement = $connection->prepare($sql);
			$statement->execute();
			$results = $statement->fetchAll();			
			
			$sort = 1;
			foreach($results as $result)
			{
				$photo_url = $base_url."/web/bundles/base/files/cruise/river/".$alias."/".$result['fileName'];
				
				$arr = explode('/',$photo_url);
				$photo_name = array_pop($arr);				
				
				$photo_title = $result['title'];
				
				$image = new Image();
				$image
					->setTitle($photo_title)
					->setFilename('river/'.$alias."/".$photo_name)
					->setSort($sort)
				;
				$sort++;				
				
				$em->persist($image);
				
				$page->addFile($image);		


				if(!is_dir($dir.'/'.'river/'.$alias))  mkdir($dir.'/'.'river/'.$alias,0777,true);
				
				// сохраняем файл на диск 
				$newfile = $dir.'/'.'river/'.$alias."/".$photo_name;
				$file_content = $this->curl_get_file_contents($photo_url);
				$fp = fopen($newfile, "w");
				$test = fwrite($fp, $file_content); // Запись в файл
				//if ($test) echo 'Данные в файл успешно занесены.';
				//else echo 'Ошибка при записи в файл.';
				fclose($fp); //Закрытие файла		
				
				
			}
			
			
			/*
			$parser = $this->get('simple_html_dom');
			$parser->load($result['body']);
			
			foreach($parser->find('img') as $element) 
			{
				
				
				$photo_url =  $base_url.$element->src;
				$page->img[] = $photo_url;
				
				// получаем название файла 
				$arr = explode('/',$photo_url);
				$photo_name = array_pop($arr);
								
				// сохраняем файл на диск 
				$newfile = $dir.'/'.$photo_name;
				$file_content = $this->curl_get_file_contents($photo_url);
				$fp = fopen($newfile, "w");
				$test = fwrite($fp, $file_content); // Запись в файл
				//if ($test) echo 'Данные в файл успешно занесены.';
				//else echo 'Ошибка при записи в файл.';
				fclose($fp); //Закрытие файла					
			}
			
			
			
			*/
			
			
			
			$pages[] = $page;
			
			$em->flush();
		}
		dump($pages);
		
	
		
		
		return new Response("OK");
	}	
	
	/**
	 * @Route("/agency", name="agency" )
     */	
	public function agencyAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$em_booking = $this->getDoctrine()->getManager('booking');
		
		$connection = $em_booking->getConnection();
		
		$sql = "
			SELECT * FROM 	aa_agent
			LEFT JOIN jdr8t_users ON jdr8t_users.id = aa_agent.user_id
		";
		
		$statement = $connection->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();

		foreach($results as $result)
		{
			$agency = new Agency();
			$agency
				->setBankName($result['bank'])
				->setName($result['name'])
				->setRs($result['rs'])
				->setKs($result['ks'])
				->setBik($result['bik'])
				->setInn($result['inn'])
				->setKpp($result['kpp'])
				->setUrAddress($result['ur_address'])
				->setFaktAddress($result['fakt_address'])
				->setPhone($result['phone'])
				->setFee($result['fee'])
				->setNumDog($result['num_dog'] == 0 ? null : $result['num_dog'])
				->setCreated(new \DateTime($result['timecreate']))
				->setShortName($result['short_name'])
				->setAuth('auth')
			;	
			$em->persist($agency);
			//$em->flush();
		}
		
		
		return new Response("OK");
	}		
	
	/**
	 * @Route("/discount", name="discount" )
     */	
	public function discountAction()
	{
		$em = $this->getDoctrine()->getManager();
		
		$typeDiscounts = [];
		$tds = $em->getRepository("CruiseBundle:TypeDiscount")->findAll();
		foreach($tds as $td)
		{
			$typeDiscounts[$td->getCode()] = $td;
		}
		
		
		
		$em_booking = $this->getDoctrine()->getManager('booking');
		
		$connection = $em_booking->getConnection();
		/*
		$sql = "
			SELECT * FROM aa_tur 
			
		";
		
		$statement = $connection->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();
		
		
		foreach($results as $tur)
		{
			$cruise = $em->getRepository("CruiseBundle:Cruise")->findOneById($tur['id']);
			if(null === $cruise)
			{
				continue;
			}
			$cruise->setTypeDiscount( isset($typeDiscounts[$tur['type_discount']]) ? $typeDiscounts[$tur['type_discount']] : null );
			$em->flush();
		}
		*/
		$sql = "
			SELECT * FROM aa_discount where id_tur >= 7000 
			
		";
		$statement = $connection->prepare($sql);
		$statement->execute();
		$results = $statement->fetchAll();		
		
		//dump($results);
		
		$cruises = [];
		$crs = $em->getRepository("CruiseBundle:Cruise")->findAll();
		foreach($crs as $cr)
		{
			$cruises[$cr->getId()] = $cr;
		}

		
		foreach($results as $result)
		{
			
			//dump($result);
			$cruise = isset($cruises[$result['id_tur']]) ? $cruises[$result['id_tur']] : null ;
			if((null === $cruise)   )
			{
				continue;	
			}			
			//$ship = $cruise->getShip();
			//dump($ship);
			
			//$room = $em->getRepository("CruiseBundle:ShipRoom")->findOneBy(['number'=>$result['num'], 'ship'=>$ship->getId()]);
			
			$room = $em->createQueryBuilder()
					->select('r')
					->from("CruiseBundle:ShipRoom",'r')
					->leftJoin("r.cabin","cab")
					->leftJoin("cab.ship",'s')
					->leftJoin('s.cruises','c')
					->where("c.id = ".$cruise->getId()." AND r.number = '".$result['num']."'")
					->getQuery()
					->getOneOrNullResult()
					;
			//dump($room);
			if((null === $cruise) or (null === $room)  )
			{
				continue;
				
			}
			
			
			
			$roomDiscount = new RoomDiscount();
			$roomDiscount
					->setCruise($cruise)
					->setRoom($room)
			;	
			$em->persist($roomDiscount);
			
			//dump($roomDiscount);
			
			//$em->flush();
			
			//break;
			
		}
		
		
		
		return new Response("OK");
	}	
	
	



	
	
}
