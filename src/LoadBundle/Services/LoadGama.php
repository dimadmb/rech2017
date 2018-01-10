<?php

namespace LoadBundle\Services;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;


use BaseBundle\Entity\Page;
use BaseBundle\Entity\Image;
use CruiseBundle\Entity\Ship;
use CruiseBundle\Entity\ShipDeck;
use CruiseBundle\Entity\ShipCabinType;
use CruiseBundle\Entity\ShipCabinPlace;
use CruiseBundle\Entity\Place;
use CruiseBundle\Entity\Cruise;
use CruiseBundle\Entity\Category;
use CruiseBundle\Entity\Tariff;
use CruiseBundle\Entity\Price;
use CruiseBundle\Entity\ShipRoom;
use CruiseBundle\Entity\ShipCabin;
use CruiseBundle\Entity\ProgramItem;
use CruiseBundle\Entity\Meals;
use LoadBundle\Controller\Helper;


class LoadGama  extends Controller
{
	const PATH_IMG = "/files/ship/";	
	
	private $path_ships = 'http://api.gama-nn.ru/execute/view/ships/';
	private $path_decks = 'http://api.gama-nn.ru/execute/view/deck/';
	private $path_cruises = 'http://gama-nn.ru/execute/navigation/';
	private $path_cruise = 'http://gama-nn.ru/execute/way/';
	
	
	private $doctrine;
	private $em;
	private $templating;
	private $simple_html_dom;
	
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
	
	public function URL2XML( $url )
	{
		$string = $this->curl_get_file_contents($url);
		return simplexml_load_string($string);
	}
	
	public function __construct($doctrine, $templating, $simple_html_dom)
	{
		$this->doctrine = $doctrine;
		$this->em = $this->doctrine->getManager();
		$this->templating = $templating;
		$this->simple_html_dom = $simple_html_dom;
	}
	
	public function load($ship_id, $update = false)
	{
		
		$em = $this->em;
		
		ini_set("memory_limit","2G");
		ini_set("max_execution_time","1200");
		
		

		
		
		/// получаем палубы теплохода
		$xml_ship_deck = $this->URL2XML($this->path_ships);
		//dump($xml_ship_deck);
		foreach($xml_ship_deck->ships->ship as $ship_xml)
		{
			if($ship_xml->attributes()['iid'] ==  $ship_id )
			{
				//dump($ship_xml);
				break;
			}
		}	
		//dump($ship_xml);
		// получаем кабины на этих палубах



		
		$shipName = $ship_xml->attributes()['name'];
		
		//$roomsAssoc = [];
		$categories_name = [];
		$decks_name = [];
		foreach($ship_xml->decks->deck as $xml_deck)
		{
			$xml_ship_cabin = $this->URL2XML($this->path_decks.'/'.$xml_deck->attributes()['iid']);
			//dump($xml_ship_cabin);
			
			// получим категории из этих кают 
			foreach($xml_ship_cabin->categories->category as $category)
			{
				$categories_xml[(int)$category->attributes()['iid'] ] = $category->attributes()['name'];
			}			
			// получим каюты
			foreach($xml_ship_cabin->cabins->cabin as $cabin)
			{

					//$roomsAssoc[(int)$cabin->attributes()['iid']] = (string)$cabin->attributes()['name'];
					$categories_name[(string)$categories_xml[(int)$cabin->attributes()['category']]] = (string)$categories_xml[(int)$cabin->attributes()['category']];
					$decks_name[(string)$xml_ship_cabin->deck->attributes()['name']] = (string)$xml_ship_cabin->deck->attributes()['name'];
				
				$roomsForLoad[(string)$categories_xml[(int)$cabin->attributes()['category']]][(string)$xml_ship_cabin->deck->attributes()['name']][] = [
						  "name" => (string)$cabin->attributes()['name'],
						  "places" => (string)$cabin->attributes()['places'],					
				];
			}
			
		}
		

		//dump($roomsForLoad);
		//dump($categories_name);
		
		// тип каюты 2Б уже есть в максимально трёхместном размещении но тут он в 4-х местном
		
		// проверим есть ли такие категории, нет - добавим.
		$cabinsTypeAll = []; // типы кабин, название - ключ массива
		$cabinsType = $em->getRepository("CruiseBundle:ShipCabinType")->findAll();
		foreach($cabinsType as $cabinType)
		{
			$cabinsTypeAll[$cabinType->getName()] =  $cabinType; 
		}
		foreach($categories_name as $category_name)
		{
			if(!array_key_exists($category_name,$cabinsTypeAll))
			{
				$cabinTypeNew = new ShipCabinType();
				$cabinTypeNew
						->setName($category_name)
						->setComment($category_name)
					;
				$em->persist($cabinTypeNew);
				//$em->flush();
				$cabinsTypeAll[$cabinTypeNew->getName()] = $cabinTypeNew;
			}
		}
		
		// и палубы
		$decksAll = []; // 
		$decks = $em->getRepository("CruiseBundle:ShipDeck")->findAll();
		foreach($decks as $deck)
		{
			$decksAll[$deck->getName()] =  $deck; 
		}
		foreach($decks_name as $deck_name)
		{
			if(!array_key_exists($deck_name,$decksAll))
			{
				$deckNew = new ShipDeck();
				$deckNew
						->setName($deck_name)
					;
				$em->persist($deckNew);
				//$em->flush();
				$decksAll[$deckNew->getName()] = $deckNew;
			}
		}	
		
		// места стоянок 
		$placesPortsAll = [];
		$placesPorts = $em->getRepository("CruiseBundle:Place")->findAll();
		foreach($placesPorts as $placePort)
		{
			$placesPortsAll[$placePort->getName()] = $placePort;
		}	

		
		// и размещение 
		$placesAll = [];
		$places = $em->getRepository("CruiseBundle:ShipCabinPlace")->findAll();
		foreach($places as $place)
		{
			$placesAll[$place->getRpId()] = $place;
		}


		$turOperator = $em->getRepository("CruiseBundle:TurOperator")->findOneByCode("gama");
		$ship = $em->getRepository("CruiseBundle:Ship")->findOneById($ship_id+3000);	
		if($ship == null  || $update )
		{
			if ($ship === null) {
				$ship = new Ship();
				
			
			$shipCode = Helper\Convert::translit($shipName);
			
			$ship
				->setId(3000 + $ship_id)
				->setShipId(3000 + $ship_id)
				->setName($shipName)
				->setTurOperator($turOperator)
				->setCode($shipCode)
				;				
			}
			
			
			$this->ShipPageCreate($ship_id,$ship_xml);

				
			foreach($roomsForLoad as $cabinTypeName => $decks)
			{
				foreach($decks as $deckName => $rooms)
				{
					// тут определяем тип кабины 
					$cabin = $em->getRepository("CruiseBundle:ShipCabin")->findOneBy([
									'ship'=>$ship,
									'type'=>$cabinsTypeAll[$cabinTypeName],
									'deck'=>$decksAll[$deckName ]
								]);
					if(null === $cabin)
					{
						$cabin = new ShipCabin();
						$cabin
							->setShip($ship)
							->setType($cabinsTypeAll[$cabinTypeName])
							->setDeck($decksAll[$deckName ])
						;
						$em->persist($cabin);						
					}

					$ship->addCabin($cabin);
					
					foreach($rooms as $room)
					{
						$roomNew = $em->getRepository("CruiseBundle:ShipRoom")->findOneBy([
									'cabin'=>$cabin,
									'number'=>$room['name']
								]);
						if(null === $roomNew)
						{
							$roomNew = new ShipRoom();
							$roomNew
								->setCabin($cabin)
								->setNumber($room['name'])
								->setCountPass((int)$room['places'])
							;
							$em->persist($roomNew);
							$cabin->addRoom($roomNew);							
						}
					}
				}
			}
			$em->persist($ship);
			$em->flush();
		}

		//dump($ship);
		
		
		// получим все каюты теплохода 
		$roomAll = [];
		foreach($ship->getCabin() as $cabin)
		{
			foreach($cabin->getRooms() as $room)
			$roomAll[$room->getNumber()] = $room;
		}
		
		// типы питания 
		$mealsAll = array();
		$cruiseMeals = $em->getRepository("CruiseBundle:Meals")->findAll();  
		foreach($cruiseMeals as $meals)
		{
			$mealsAll[$meals->getId()]  = $meals;
		}	
		
		// тарифы 
		$tariffAll = array();
		$cruiseTariff = $em->getRepository("CruiseBundle:Tariff")->findAll();  
		foreach($cruiseTariff as $tariff)
		{
			$tariffAll[$tariff->getId()]  = $tariff;
		}				
		//dump($tariffAll);
		
		$xml = $this->URL2XML($this->path_cruises);
		
		foreach($xml->navigations->navigation as $ways)
		{
			if($ways->attributes()['ship_iid'] ==  $ship_id )
			{
				//dump($ways);
				
				foreach($ways->ways->way as $way)
				{
					//dump($way);
					// создаём круизы
					
					$cruise = $em->getRepository("CruiseBundle:Cruise")->findOneById($way->attributes()['iid'] + 3000000);
					if(null === $cruise)
					{
						$cruise = new Cruise();
						$cruise
							->setId((int)$way->attributes()['iid'] + 3000000)
							->setShip($ship)
							->setTurOperator($turOperator)
							;

							$em->persist($cruise);
							
					}
					$ship->addCruise($cruise);
					
					
					$dateStart = new \DateTime(substr((string)$way->attributes()['STS'] , 0, 10));
					$dateEnd = new \DateTime(substr((string)$way->attributes()['FTS'] , 0, 10));
					
					$dayCount = $dateStart->diff( $dateEnd )->format("%d") + 1 ;
					
					//dump([$dateStart,$dateEnd,$dayCount]);
					
					$cruise
							->setName((string)$way->attributes()['Way'])
							->setStartDate($dateStart)
							->setEndDate($dateEnd)
							->setDayCount($dayCount)
							;						
					// круиз готов, теперь программы и прайсы
					
					$xml_cruise = $this->URL2XML($this->path_cruise.(int)$way->attributes()['iid']);
					//dump($xml_cruise);
					foreach($xml_cruise->cabins->cabin as $cabin_xml)
					{
						//dump($cabin_xml);
						
						$room = $roomAll[(string)$cabin_xml->attributes()['name']];
						$cab = $room->getCabin();
						
						foreach($cabin_xml->cost as $pricePlace)
						{
							$rp_id = $placesCount = $pricePlace->attributes()['inCabin'];
							//dump($rp_id);
							//dump($placesAll);
							
							$roomPlace = $placesAll[(int)$rp_id];
							// взрослый без питания
							if(isset($pricePlace->attributes()['std0']))
							{
								$meal = $mealsAll[5];
								$tariff = $tariffAll[1];
								$priceValue = 1 * $pricePlace->attributes()['std0'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
									
								}
								$em->flush();
								//dump($price);
							}
							// взрослый с обедом и ужином
							if(isset($pricePlace->attributes()['std2']))
							{
								$meal = $mealsAll[6];
								$tariff = $tariffAll[1];
								$priceValue = 1 * $pricePlace->attributes()['std2'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
								}
								$em->flush();
								//dump($price);
							}
							// взрослый трёхразовое
							if(isset($pricePlace->attributes()['std3']))
							{
								$meal = $mealsAll[2];
								$tariff = $tariffAll[1];
								$priceValue = 1 * $pricePlace->attributes()['std3'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
								}
								$em->flush();
								//dump($price);
							}
							
							
							// детский без питания
							if(isset($pricePlace->attributes()['child0']))
							{
								$meal = $mealsAll[5];
								$tariff = $tariffAll[2];
								$priceValue = 1 * $pricePlace->attributes()['child0'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
								}
								$em->flush();
								//dump($price);
							}
							// детский с обедом и ужином
							if(isset($pricePlace->attributes()['child2']))
							{
								$meal = $mealsAll[6];
								$tariff = $tariffAll[2];
								$priceValue = 1 * $pricePlace->attributes()['child2'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
								}
								$em->flush();
								//dump($price);
							}
							// детский трёхразовое
							if(isset($pricePlace->attributes()['child3']))
							{
								$meal = $mealsAll[2];
								$tariff = $tariffAll[2];
								$priceValue = 1 * $pricePlace->attributes()['child3'];
								
								$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tariff,
												'cruise' => $cruise,
											]);	
								if($price === null)
								{
									$price = new Price();
									$price
										->setPlace($roomPlace)
										->setCabin($cab)
										->setMeals($meal)
										->setTariff($tariff)
										->setCruise($cruise)
										->setPrice($priceValue)
										;
									$em->persist($price);
								}
								$em->flush();
								//dump($price);
							}
							
							
							/*$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tarriff,
												'cruise' => $cruise,
											]);								
							*/
						}
						

									
						
					}
					
					
					// удаляем все программы и грузим заново 
					$cruise->removeAllProgram();
					$programm_del = $em->getRepository("CruiseBundle:ProgramItem")->findByCruise($cruise);
					foreach($programm_del as $p_del)
					{
						$em->remove($p_del);
					}
					
					$em->persist($cruise);
					$em->flush();

					
					foreach($xml_cruise->path->point as $point)
					{
						//dump($point->attributes());
						
						$name = (string)$point->attributes()['town_name'];
						
						
						
						
						$placePort = null;
						if(isset($placesPortsAll[$name]))
						{
							$placePort = $placesPortsAll[$name];
						}
						
						
						
						$dateStart = new \DateTime($point->attributes()['STS']);
						$dateStop  = new \DateTime($point->attributes()['ETS']);
						
						$programItem = new ProgramItem();
						$programItem
								->setCruise($cruise)
								->setPlace($placePort)
								->setDateStart($dateStart)
								->setDateStop($dateStop)
								->setDescription("")
								->setPlaceTitle($name)
								;
						$em->persist($programItem);		
						$cruise->addProgram($programItem);		
						
					}
					
					
					
					
				}
				
			}

		}
		$em->flush();
		
		//dump($ship);
		//dump($roomAll);
		
		
	}
	

	public function ShipPageCreate($ship_id,$shipXML)
	{
		$shipImage = "http:".(string)$shipXML->attributes()['photo.0'];
		$shipImageDeck = "http:".(string)$shipXML->attributes()['photo.1'];
		$shipName = (string)$shipXML->attributes()['name'];
		$shipCode = Helper\Convert::translit($shipName);

		$shipBody = "";
		
		
		//dump($shipName);
		
		//$shipPhotos = $shipXML->answer->shipimages->item;
		
		
		// Копируем фотографии
		
		$dir = (__DIR__).'/../../../web'.self::PATH_IMG.$shipCode;

		if(!is_dir($dir)) mkdir($dir,0777,true) ;
		$img_main = $shipImage;
		$newfile = $dir.'/'.$shipCode.'-main.jpg';
		$file_content = $this->curl_get_file_contents($img_main);
		$fp = fopen($newfile, "w");
		$test = fwrite($fp, $file_content); // Запись в файл
		//if ($test) echo 'Данные в файл успешно занесены.';
		//else echo 'Ошибка при записи в файл.';
		fclose($fp); //Закрытие файла	

		$img_decks = $shipImageDeck;
		$newfile = $dir.'/'.$shipCode.'-decks.gif';
		$file_content = $this->curl_get_file_contents($img_decks);
		$fp = fopen($newfile, "w");
		$test = fwrite($fp, $file_content); // Запись в файл
		//if ($test) echo 'Данные в файл успешно занесены.';
		//else echo 'Ошибка при записи в файл.';
		fclose($fp); //Закрытие файла	




		


		
		// Страница
		$shipContent = $this->templating->render('LoadBundle:LoadVodohod:shipPage.html.twig',array(
				'img_main'=>self::PATH_IMG.$shipCode.'/'.$shipCode.'-main.jpg',
				'ship_description' => $shipBody,
				'img_deck' => self::PATH_IMG.$shipCode.'/'.$shipCode.'-decks.gif',
				'ship_id' => $ship_id + 3000,
				'ship_name' => $shipName,
				));
		
		$em = $this->em;
		
		$pageParent = $this->doctrine->getRepository('BaseBundle:Page')->findOneByFullUrl("ships");
		if($pageParent == null)
		{
			$pageRoot = $this->doctrine->getRepository('BaseBundle:Page')->findOneByFullUrl("");
			$pageParent =  new Page();
			
			$pageParent 
					->setParent($pageRoot)
					->setName("Теплоходы")
					->setTitle("Теплоходы")
					->setH1("Теплоходы")					
					->setSort(1)
					->setActive(1)
					->setIsMenu(0)
					->setIsFolder(0)
					->setFullUrl("ships")
					->setLocalUrl("ships")
					->setParentUrl("")					

				;
			$em->persist($pageParent);
			
		}
		

		$page = $this->doctrine->getRepository('BaseBundle:Page')->findOneByFullUrl("ships/".$shipCode);
		
		if ($page != null) {
			$images = $page->getFile();
			foreach($images as $image)
			{
				// проверку на существование
				if(file_exists($dir.'/'.$image->getFilename()))
				unlink($dir.'/'.$image->getFilename());
				$em->remove($image);
			}
			//$em->remove($page);
			$em->flush();
		}
		if($page == null) 	$page = new Page();
		
		$page 
				->setParent($pageParent)
				->setName($shipName)
				->setTitle("Теплоход ".$shipName.": цены, маршруты, фото, отзывы, расписание на 2018 год")
				->setH1($shipName)
				->setSort(1)
				->setActive(1)
				->setIsMenu(0)
				->setIsFolder(0)
				->setFullUrl("ships/".$shipCode)
				->setLocalUrl($shipCode)
				->setParentUrl("ships")
				->setBody($shipContent)
			;
			
			/* ФОТОГРАФИИ */
			/*
			$page->removeAllFile();
			$em->flush();			
						
			$sort = 1;
			foreach($shipPhotos as $element) 
			{
								
				// получаем адрес фото
				$photo_url =  "https://".$element->image;
				$photo_title =$element->desc;
				
				// получаем название файла 
				$photo_name = $shipCode."-".$sort.".jpg";
				
				// сохраняем файл на диск 
				$newfile = $dir.'/'.$photo_name;
				$file_content = $this->curl_get_file_contents($photo_url);
				$fp = fopen($newfile, "w");
				$test = fwrite($fp, $file_content); // Запись в файл
				//if ($test) echo 'Данные в файл успешно занесены.';
				//else echo 'Ошибка при записи в файл.';
				fclose($fp); //Закрытие файла	
				
				
				$image = new Image();
				$image
					->setTitle($photo_title)
					->setFilename('ship/'.$shipCode."/".$photo_name)
					->setSort($sort)
				;
				$sort++;
				
				
				$em->persist($image);
				
				$page->addFile($image);
			}

			
			/* КОНЕЦ ФОТОГРАФИИ */			
			
		$em->persist($page);
		
		$em->flush();

	}
	
	
}