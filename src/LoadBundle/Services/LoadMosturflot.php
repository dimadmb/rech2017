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


class LoadMosturflot  extends Controller
{
	const PATH_IMG = "/files/ship/";	
	const BASE_URL_KEY = "https://booking.mosturflot.ru/api?userhash=60b5fe8b827586ece92f85865c186513ed3e7bfa&section=rivercruises&";
	
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

	// ПОЛУЧИТЬ СПИСОК ТЕПЛОХОДОВ
	public function getShips()
	{
		$url = self::BASE_URL_KEY."request=ships";
		foreach($this->URL2XML( $url )->answer->item as $item)
		{
			$array[] = $item;
		}
		return $array;
	}	
	
	// ПОЛУЧИТЬ ИНФОРМАЦИЮ О ТЕПЛОХОДЕ
	public function getShip($ship_id)
	{
		$url = self::BASE_URL_KEY."request=ship&shipid=".$ship_id."&images=true&cabins=true";
		return $this->URL2XML( $url );
	}
	
	// ПОЛУЧИТЬ СПИСОК КРУИЗОВ ТЕПЛОХОДА
	public function getCruises($ship_id)
	{
		$url = self::BASE_URL_KEY."request=tours&routedetail=true&shipid=".$ship_id."&tariffs=true";
		return $this->URL2XML( $url );
	}	
	
	// ПОЛУЧИТЬ СПИСОК ИНФОРМАЦИЮ О КРУИЗЕ
	public function getCruiseDetail($cruise_id)
	{
		$url = self::BASE_URL_KEY."request=tour&tourid=".$cruise_id."&routedetail=1";
		return $this->URL2XML( $url );
	}
	
	public function load($ship_id, $update = false)
	{
		$em = $this->doctrine->getManager();

		$cruiseRepos = $this->doctrine->getRepository('CruiseBundle:Cruise');		
		$shipRepos = $this->doctrine->getRepository('CruiseBundle:Ship');		
		$placeRepos = $this->doctrine->getRepository('CruiseBundle:Place');
		$tariffRepos = $this->doctrine->getRepository('CruiseBundle:Tariff');
		$mealsRepos = $this->doctrine->getRepository('CruiseBundle:Meals');
		$decksRepos = $this->doctrine->getRepository('CruiseBundle:ShipDeck');		
		$cabinTypeRepos = $this->doctrine->getRepository('CruiseBundle:ShipCabinType');
		$cabinPlaceRepos = $this->doctrine->getRepository('CruiseBundle:ShipCabinPlace');		
		$categoryRepos = $this->doctrine->getRepository('CruiseBundle:Category');
		
		
		// категория выходного дня  kruizy-vyhodnyh-dney
		$cat_week_end = $categoryRepos->findOneByCode("kruizy-vyhodnyh-dney");
		
		
		$turOperator = $this->doctrine->getRepository('CruiseBundle:TurOperator')->findOneById(2);
		
		$shipXML = $this->getShip($ship_id);
		$shipName = (string)$shipXML->answer->shipname;
		$shipCode = Helper\Convert::translit($shipName);

		$cruisesXML = $this->getCruises($ship_id);		

		
		
			// ПОЛУЧИМ СПИСОК ПАЛУБ ИЗ БД
			
			$decksAll = $decksRepos->findAll();
			foreach($decksAll as $deck)
			{
				$decksByName[$deck->getName()] = $deck;
				$decksById[$deck->getDeckId()] = $deck;
			}
			// ПОЛУЧИМ СПИСОК ТИПОВ КАЮТ ИЗ БД
			
			$room_types_all = $cabinTypeRepos->findAll();
			foreach($room_types_all as $room_type)
			{
				$room_types[$room_type->getComment()] = $room_type;
				$room_typesById[$room_type->getId()] = $room_type;
			}
			
			
			// СОЗДАДИМ МАССИВ УНИКАЛЬНЫХ ТИПОВ КАЮТ
			$cabinsArray = array();
			foreach($shipXML->answer->shipcabins->item as $item)
			{
				$deck = "Средняя";
				if(strpos((string)$item->cabindesc, "средней")) $deck = "Средняя";
				if(strpos((string)$item->cabindesc, "нижней")) $deck = "Нижняя";
				if(strpos((string)$item->cabindesc, "главной")) $deck = "Главная";
				if(strpos((string)$item->cabindesc, "солнечной")) $deck = "Солнечная";
				if(strpos((string)$item->cabindesc, "шлюпочной")) $deck = "Шлюпочная";
				if(strpos((string)$item->cabindesc, "Нева")) $deck = "Шлюпочная";
				if(!(
					isset($cabinsArray[$deck][(string)$item->cabincategoryname]) &&
					$cabinsArray[$deck][(string)$item->cabincategoryname]["place_count"] >= (int)$item->cabinmainpass ))
				{
					$cabinsArray[$deck][(string)$item->cabincategoryname] = array(
						"place_count" => (int)$item->cabinmainpass,
					);
				}
				
				
				// тут подготовить массив с комнатами
				$rooms[$deck][(string)$item->cabincategoryname][] = $item;
			}		
		
		
		$ship = $shipRepos->findOneByCode($shipCode);	
		if($ship == null  || $update )
		{
			$this->ShipPageCreate($ship_id,$shipXML);
			
			if ($ship === null) {
				$ship = new Ship();
			}
			
			$ship
				->setId(1000 + $ship_id)
				->setShipId(1000 + $ship_id)
				->setTurOperator($turOperator)
				->setName($shipName)
				->setCode($shipCode)
				;
			$em->persist($ship);
			//$em->flush();	


			
			foreach($cabinsArray as $deckName => $cabinInDeck)
			{
				foreach($cabinInDeck as $cabinTypeName => $item)
				{
					// ДОБАВИМ ТИПЫ КАЮТ В БАЗУ, ЕСЛИ ОНИ ОТСУТСТВУЮТ
					if(!isset($room_types[$cabinTypeName]))
					{
						$cabinType = new ShipCabinType();
						$cabinType 
							->setName($cabinTypeName)
							->setComment($cabinTypeName)
							->setPlaceCountMax($item["place_count"])
						;
						$em->persist($cabinType);
						$em->flush();
						$room_types[$cabinType->getComment()] = $cabinType;
						$room_typesById[$cabinType->getId()] = $cabinType;	
					}
					

					$cabinType = $cabinTypeRepos->findOneByComment($cabinTypeName);
					
					
					
					$deck = $decksRepos->findOneByName($deckName);
					
					$cabin = null;
					foreach($ship->getCabin() as $cabinTemp)
					{
						if(($cabinTemp->getType() == $cabinType ) && ($cabinTemp->getDeck() == $deck) )
						{
							$cabin = $cabinTemp;
						}
					}
					if(null == $cabin)
					{
						$cabin = new ShipCabin();
						$cabin
							->setDeck($deck)
							->setType($cabinType)
							->setShip($ship)
						;
						$ship->addCabin($cabin);					
					}
					
					;
					
					
					//dump($rooms);
					//dump($cabinTypeName);
					
					// НОВОЕ добавим каюты в теплоход					
					foreach($rooms[$deckName][$cabinTypeName] as $roomItem)
					{
						$room = null;
						
						foreach($ship->getCabin() as $cabinTemp)
						{
							foreach($cabinTemp->getRooms() as $roomTemp )
							{
								if($roomTemp->getNumber() == $roomItem->cabinnumber )
								{
									$room = $roomTemp;
								}
							}
						}	

						//dump($room); 

						if(null == $room)
						{
							$room = new ShipRoom();
							$room
								->setNumber((string) $roomItem->cabinnumber)
								->setCabin($cabin)
								->setCountPass((int) $roomItem->cabinmainpass)
								->setCountPassMax((int) $roomItem->cabinmaxpass)								
							;
							$cabin->addRoom($room);
							$em->persist($room);						
						}
						else
						{
							$room
								->setCabin($cabin)
								->setCountPass((int) $roomItem->cabinmainpass)
								->setCountPassMax((int) $roomItem->cabinmaxpass)
								;
						}
						

					}


					$em->persist($cabin);
				}
			
			}
			
			

			$em->persist($ship);
			$em->flush();

		} /*  if($ship == null  || $update ) */
			
		
			/// теперь надо пройтись по круизам и ценам
			
			
			$cabins_all = $ship->getCabin();
			foreach($cabins_all as $cabin)
			{
				$cabins[$cabin->getType()->getId()][$cabin->getDeck()->getDeckId()] = $cabin;
			}
			

			
			/*
			$cruiseSpecsAll = $cruiseSpecRepos->findAll();
			foreach($cruiseSpecsAll as $cruiseSpecItem)
			{
				$cruiseSpec[$cruiseSpecItem->getCode()] = $cruiseSpecItem;
			}
			*/
			$room_places = array();
			$room_places_all = $cabinPlaceRepos->findAll();
			foreach($room_places_all as $room_place)
			{
				$room_places[$room_place->getRpName()] = $room_place;
				$room_places_count[$room_place->getId()] = $room_place;
			}	


			$tariffs = array();
			$cruiseTariffs = $tariffRepos->findAll();  
			foreach($cruiseTariffs as $tariff)
			{
				$tariffs[$tariff->getName()]  = $tariff;
			}
			
			$meals = array();
			$cruiseMeals = $mealsRepos->findAll();  
			foreach($cruiseMeals as $meals)
			{
				$mealss[$meals->getName()]  = $meals;
			}				
			
			$placesAll = $placeRepos->findAll();
			foreach($placesAll as $placesAllItem)
			{
				$places[$placesAllItem->getName()] = $placesAllItem;
			}

		// удаляем все круизы данного теплохода 
		// нужно оптимизировать в один запрос
		/*
		$cruises_remove = $cruiseRepos->findBy(['ship' => $ship]);
		foreach ($cruises_remove as $cr) {
			$em->remove($cr);
		}
		$em->flush();
		*/

		
		/// теперь КРУИЗЫ
		
		
		
		foreach($cruisesXML->answer->item as $cruiseItem)
		{
			$code_mos = (int)$cruiseItem->tourid;
			$id  = (int)(1000000 + $code_mos);
			
			
			//dump($cruiseItem);
			
			// есть ли уже такой круиз?
			$cruise = $cruiseRepos->findOneById($id);
			if($cruise === null)
			{
				$cruise = new Cruise();
				//$cruise->addCategory();
			}
			else
			{
				$cruise->removeAllCategory();
				$em->flush();
			}
			
			if((int)$cruiseItem->tourid == 1)
			{
				//dump("Выходной"); 
				$cruise->addCategory($cat_week_end);
			}
			
			$route = (string)$cruiseItem->tourroute;
			$startDate = new \DateTime((string)$cruiseItem->tourstart);
			$endDate = new \DateTime((string)$cruiseItem->tourfinish);
			$dayCount = (int)$cruiseItem->tourdays;

			
			
			$cruise->setId($id);
			//$cruise->setCode($id);			
			$cruise->setShip($ship);			
			$cruise->setName($route);
			$cruise->setStartDate($startDate);
			$cruise->setEndDate($endDate);
			$cruise->setDayCount($dayCount);
			$cruise->setTurOperator($turOperator);			
			$cruise->setActive(true);			
			$em->persist($cruise);
			$ship->addCruise($cruise);
			
			// ЦЕНЫ 
			foreach($cruiseItem->tourtariffs->item as $tourtariffsItem)
			{

				$categoryname = (string)$tourtariffsItem->categoryname;
				// проверим есть ли такой тип каюты, нет - добавим
				
				//dump($room_types);
				
				if(!isset($room_types[$categoryname]))
				{
					$cabinType = new ShipCabinType();
					$cabinType 
						->setName($categoryname)
						->setComment($categoryname)
						->setPlaceCountMax($item["place_count"])
					;
					$em->persist($cabinType);
					$em->flush();
					$room_types[$cabinType->getComment()] = $cabinType;
					$room_typesById[$cabinType->getId()] = $cabinType;					
				}
				
				$rt_name = $room_types[$categoryname];
				
				if(isset($cabins[$rt_name->getId()]))
				{
					$cabin = $cabins[$rt_name->getId()];
				}
				else 
				{
					continue;
				}
				$rp_id = $room_places_count[$rt_name->getPlaceCountMax()];	
				

				
				foreach($tourtariffsItem->categorytariffs->item as $tarriffType)
				{

					if(isset($tariffs[(string)$tarriffType->tariffname]))
					{
						$cruiseTariff = $tariffs[(string)$tarriffType->tariffname];
					}
					else
					{
						$cruiseTariff = new Tariff();
						$cruiseTariff
							->setName((string)$tarriffType->tariffname)
							;
						$em->persist($cruiseTariff);
						$em->flush();
						$tariffs[(string)$tarriffType->tariffname] = $cruiseTariff; 
					};
					
					$tarriff = $tariffs[(string)$tarriffType->tariffname];
					
					if($tarriff->getName() == "Доплата за свободное место") continue;
					
					foreach($tarriffType->meals->item as $meals)
					{
						if(isset($mealss[(string)$meals->mealname]))
						{
							$meal = $mealss[(string)$meals->mealname];
						}
						else
						{
							$meal = new Meals();
							$meal
								->setName((string)$meals->mealname)
							;
							$em->persist($meal);
							$em->flush();
							$mealss[(string)$meals->mealname] = $meal;
							
						}
						
						// теперь грузим сами цены
						foreach($cabin as $cab)
						{
														
							$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
												'place' => $rp_id,
												'cabin' => $cab,
												'meals' => $meal,
												'tariff' => $tarriff,
												'cruise' => $cruise,
											]);
							
							if(null == $price)
							{
								
								$price = new Price();						
							}
							
							$price	
									->setPlace($rp_id)
									->setTariff( $tarriff )
									->setCruise($cruise)
									->setCabin($cab)    
									->setPrice($meals->mainprice)
									->setMeals($meal)
							;
							$em->persist($price);
						}

					}

				}

			} // КОНЕЦ ЦЕН
			$em->flush();
			
			// ПОГРАММЫ КРУИЗОВ
			$cruiseDetailXML = $this->getCruiseDetail($code_mos);
			
			// удаляем все программы и грузим заново 
			$cruise->removeAllProgram();
			$programm_del = $em->getRepository("CruiseBundle:ProgramItem")->findByCruise($cruise);
			foreach($programm_del as $p_del)
			{
				$em->remove($p_del);
			}
			
			$em->persist($cruise);
			$em->flush();			
			
			foreach($cruiseDetailXML->answer->tourroutedetail->item as $tourProgrammItem)
			{
				// сделать проверку на прогрузку программы
				$cruise_program_item = new ProgramItem();
				
				$startDate = (string)$tourProgrammItem->arrival == "" ? new \DateTime($tourProgrammItem->date) : new \DateTime($tourProgrammItem->arrival);
				
				$endDate = (string)$tourProgrammItem->departure == "" ? new \DateTime($tourProgrammItem->date) : new \DateTime($tourProgrammItem->departure);
				
				$description = $tourProgrammItem->note == "" ? "" : $tourProgrammItem->note;
				
									
				if(isset($tourProgrammItem->excursions['items']) && $tourProgrammItem->excursions['items'] > 1  )
				{
					foreach($tourProgrammItem->excursions->item as $excursion)
					{
						$desc = preg_replace("!<a[^>]*>(.*)</a>!isU","<b>\$1</b>", (string)$excursion->desc);
						$description .= (int)$excursion->type == 1 ? "<b>Дополнительная экскурсия</b>" : "";
						$description .= $desc."<br>";
						
						
						//$description .= (string)$excursion->desc;
					}
				}
				elseif(isset($tourProgrammItem->excursions['items']) && $tourProgrammItem->excursions['items'] = 1 && isset($tourProgrammItem->excursions->item->desc) )
				{
					
					$desc = preg_replace("!<a[^>]*>(.*)</a>!isU","<b>\$1</b>", $tourProgrammItem->excursions->item->desc);
					$description .= (int)$tourProgrammItem->excursions->item->type == 1 ? "<b>Дополнительная экскурсия</b>" : "";
					$description .= $desc."";
				}
				
				$placeName = (string)$tourProgrammItem->cityname;

				$port = isset($places[$placeName]) ? $places[$placeName] : null;

				

				
				$cruise_program_item
								->setCruise($cruise)
								->setPlace($port)
								->setDateStart($startDate)
								->setDateStop($endDate)
								->setDescription($description)
								->setPlaceTitle($placeName)
								
					;	
				$em->persist($cruise_program_item);
			}			
			
			
			
		}
		
		
		$em->flush();
		

		$em->persist($ship);
		$em->flush();
		
		
		//dump($ship);
		
		$ship->getCruises()->setInitialized(true);
		$cruises1 = $ship->getCruises()->toArray();
		$ship2 = $em->createQueryBuilder()
				->select('s,c')
				->from("CruiseBundle:Ship",'s')
				->leftJoin('s.cruises','c')
				->where('s.id ='.$ship->getId())
				->getQuery()
				->getOneOrNullResult()
			;	
		$ship2->getCruises()->setInitialized(false);
		$cruises2 = $ship2->getCruises()->toArray();
		
		//dump($cruises1);
		//dump($cruises2);
		
		foreach($cruises2 as $cruise)
		{
			if(!in_array($cruise , $cruises1))
			{
				$cruise->setActive(false);
			}
		}
		$em->flush();		

		return ['ship'=>$ship->getName()];
		
	}
	
	public function ShipPageCreate($ship_id,$shipXML)
	{
		$shipImage = "https://".(string)$shipXML->answer->shiptitleimage;
		$shipImageDeck = "https://".(string)$shipXML->answer->shipdeckplan;
		$shipName = (string)$shipXML->answer->shipname;
		$shipCode = Helper\Convert::translit($shipName);
		$shipBodyOrigin = (string)$shipXML->answer->shipdesc;
		$shipBody =  preg_replace('#<a href=(?:.*)(?=</a>)#Usi', '', $shipBodyOrigin);
		$shipBody =  strip_tags($shipBody,'<strong><div><p><b><br>');
		if(false !== strpos($shipBodyOrigin,"Теплоход-ПАНСИОНАТ")) $shipBody = "<b>Теплоход-ПАНСИОНАТ</b>".$shipBody;		
		$shipPhotos = $shipXML->answer->shipimages->item;
		
		
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
				'ship_id' => $ship_id + 1000,
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