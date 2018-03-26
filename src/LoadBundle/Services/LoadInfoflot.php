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

class LoadInfoflot  extends Controller
{
	private $doctrine;
	private $em;
	private $templating;
	private $simple_html_dom;
	
	const PATH_IMG = "/files/ship/";	
	const API_KEY = "68a3d0c23cf277febd26dc1fa459787522f32006";
	const BASE_URL = "http://api.infoflot.com/JSON/";
	const BASE_URL_KEY = "http://api.infoflot.com/JSON/68a3d0c23cf277febd26dc1fa459787522f32006";	
	
	public function __construct($doctrine, $templating, $simple_html_dom)
	{
		$this->doctrine = $doctrine;
		$this->em = $this->doctrine->getManager();
		$this->templating = $templating;
		$this->simple_html_dom = $simple_html_dom;
	}

	
	public function curl_get_file_contents($URL)
		{
			$c = curl_init();
			curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($c, CURLOPT_URL, $URL);
			curl_setopt($c, CURLOPT_TIMEOUT_MS, 5000);
			$contents = curl_exec($c);
			curl_close($c);

			if ($contents) return $contents;
				else return FALSE;
		}
	
	// ПОЛУЧИТЬ ЭКСКУРСИИ КРУИЗА
	public function getExcursions($cruise_id, $ship_id = 1)
	{
		$url = self::BASE_URL_KEY."/Excursions/".$ship_id."/".$cruise_id."/";
		$json = $this->curl_get_file_contents($url);
		
		return json_decode($json,true);
	}
	
	// ПОЛУЧИТЬ СПИСОК ТЕПЛОХОДОВ
	public function getShips()
	{
		$url = self::BASE_URL_KEY."/Ships/";
		$json = $this->curl_get_file_contents($url);
		
		return json_decode($json,true);
	}
	
	// ПОЛУЧИТЬ НАЗВАНИЕ ТЕПЛОХОДА
	public function getShipName($ship_id)
	{
		return $this->getShips()[$ship_id];
	}
	
	// ПОЛУЧИТЬ ГЛАВНУЮ КАРТИНКУ ТЕПЛОХОДА
	public function getShipImage($ship_id)
	{
		$url = self::BASE_URL_KEY."/ShipsImages/";
		$json = $this->curl_get_file_contents($url);
		
		return json_decode($json,true)[$ship_id];
	}	
	
	// ПОЛУЧИТЬ СХЕМУ ПАЛУБ ТЕПЛОХОДА
	public function getShipImageDeck($ship_id)
	{
		$url = self::BASE_URL_KEY."/ShipsSchemes/";
		$json = $this->curl_get_file_contents($url);
		
		return json_decode($json,true)[$ship_id];
	}	
	
	// ПОЛУЧИТЬ КАЮТЫ ТЕПЛОХОДА
	public function getShipCabins($ship_id)
	{
		$url = self::BASE_URL_KEY."/Cabins/".$ship_id."/";
		$json = $this->curl_get_file_contents($url);
		$array = json_decode($json,true);
		// сделать проверку на получение валидного массива
		foreach($array as $item)
		{
			$place_count = count($item["places"]);
			$rooms[$item["deck_name"]][$item["type"]][] = array("name"=>$item["name"],"count"=>$place_count );
		}
			
		return $rooms;
	}		
	// ПОЛУЧИТЬ КАЮТЫ ТЕПЛОХОДА test 
	public function getShipRooms($ship_id)
	{
		$url = self::BASE_URL_KEY."/Cabins/".$ship_id."/";
		$json = $this->curl_get_file_contents($url);
		$array = json_decode($json,true);
		

			
		return $array;
	}	
	
	// ПОЛУЧИТЬ ФОТОГРАФИИ ТЕПЛОХОДА
	public function getShipPhotos($ship_id)
	{
		$photos = [];
		
		$url = self::BASE_URL_KEY."/ShipsPhoto/".$ship_id."/";
		$json = $this->curl_get_file_contents($url);
		$array = json_decode($json,true);
		
		if(!is_array($array))
		{
			return null;
		}
		foreach($array as $item)
		{
			$photos[] = $item["full"];
		}
		if(!isset($photos)) 
		{
			return null;
		}
	
		return $photos;
	}

	// ПОЛУЧИТЬ СПИСОК КРУИЗОВ
	public function getShipCruises($ship_id)
	{
		$url = self::BASE_URL_KEY."/Tours/".$ship_id."/";
		$json = $this->curl_get_file_contents($url);
		return json_decode($json,true);
	}
	
	// ПОЛУЧИТЬ ОПИСАНИЕ ТЕПЛОХОДА
	public function getShipDescription($ship_id)
	{
		$url = self::BASE_URL_KEY."/ShipsDescription/".$ship_id."/";
		$json = $this->curl_get_file_contents($url);
		return json_decode($json,true);
	}
	
	
	public function LoadShipInfoflotData($ship_id)
	{
		$cpl = 3;
		for($i=0;$i<=$cpl;$i++)
		{
			$shipName = $this->getShipName($ship_id);
			if ($shipName != null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$shipImage = $this->getShipImage($ship_id);
			if ($shipImage != null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$shipImageDeck = $this->getShipImageDeck($ship_id);
			if ($shipImage != null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$shipPhotos = $this->getShipPhotos($ship_id);
			if ($shipPhotos !== null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$shipBody = nl2br($this->getShipDescription($ship_id));
			if ($shipBody != null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$cruises = $this->getShipCruises($ship_id);
			if ($cruises != null) break;
		}
		for($i=0;$i<=$cpl;$i++)
		{
			$shipCabins = $this->getShipCabins($ship_id);
			if ($shipCabins != null) 
			{
				foreach($shipCabins as $deckName => $cabins)
				{
					if($deckName == "Cредняя")
					{
						//var_dump($deckName);
						$shipCabins["Средняя"] = $shipCabins["Cредняя"]; // Замена первой Английской буквы
						unset($shipCabins["Cредняя"]);
					}
				}
				break;
			}
		}
		$shipCode = Helper\Convert::translit($shipName);
		
		// сделать проверку, что всё прогрузилось, только после этого удалять теплоход
		if($shipName == null)
		{
			return array('error' => "Ошибка загрузки имени теплохода");
		}	
		elseif($shipImage == null)
		{
			return array('error' => "Ошибка загрузки главного фото теплохода");
		}		
		elseif($shipImageDeck == null)
		{
			return array('error' => "Ошибка загрузки фото палуб теплохода");
		}		
		elseif($shipPhotos === null)
		{
			return array('error' => "Ошибка загрузки фотографий теплохода");
		}		
		elseif($shipBody == null)
		{
			return array('error' => "Ошибка загрузки описания теплохода");
		}		
		elseif($cruises == null)
		{
			return array('error' => "Ошибка загрузки круизов теплохода");
		}		
		elseif($shipCabins == null)
		{
			return array('error' => "Ошибка загрузки кают теплохода");
		}
		
		
		else 
		{
			return array('shipName'=>$shipName, 'shipCode' => $shipCode , 'shipImage' => $shipImage, 'shipImageDeck' => $shipImageDeck, 'shipBody' => $shipBody , 'cruises' => $cruises, 'shipPhotos' => $shipPhotos, 'shipCabins' => $shipCabins);
		}
	}
	

	public function ShipPageCreate($ship_id,$shipData)
	{
		foreach($shipData as $key => $value)
		{
			$$key = $value;
		}		
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
				'ship_id' => $ship_id + 2000,
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
				$photo_url =  $element;
				$photo_title ='';
				
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
	
	public function load($ship_id, $update = false)
	{
		$shipData = $this->LoadShipInfoflotData($ship_id);
	
		if(isset($shipData['error']))
		{
			return array($shipData['error']);
		}
		foreach($shipData as $key => $value)
		{
			$$key = $value;
		}		
		
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
		
		$turOperator = $this->doctrine->getRepository('CruiseBundle:TurOperator')->findOneById(3);		


		// категория выходного дня  kruizy-vyhodnyh-dney
		$cat_week_end = $categoryRepos->findOneByCode("kruizy-vyhodnyh-dney");
		
		
		// ПОЛУЧИМ СПИСОК ПАЛУБ ИЗ БД
		
		$decksAll = $decksRepos->findAll();
		foreach($decksAll as $deck)
		{
			$decksByName[$deck->getName()] = $deck;
			$decksById[$deck->getDeckId()] = $deck;
		}
		
		// ТИПЫ РАЗМЕЩЕНИЯ
		$room_places = array();
		$room_places_all = $cabinPlaceRepos->findAll();
		foreach($room_places_all as $room_place)
		{
			$room_places[$room_place->getRpName()] = $room_place;
			$room_places_count[$room_place->getId()] = $room_place;
		}		
		
		// ПОЛУЧИМ СПИСОК ТИПОВ КАЮТ ИЗ БД
		
		$room_types_all = $cabinTypeRepos->findAll();
		foreach($room_types_all as $room_type)
		{
			$room_types[$room_type->getComment()] = $room_type;
			$room_typesById[$room_type->getId()] = $room_type;
		}

		$qb = $em->createQueryBuilder()
				->select('s,cab,rooms')
				->from('CruiseBundle:Ship', 's')
				->leftJoin('s.cabin','cab')
				->leftJoin('cab.rooms','rooms')
				->where('s.id = '.($ship_id+2000))

		;
		
		$ship = $qb->getQuery()->getOneOrNullResult();			
			
		if($ship == null  || $update )
		{
			$this->ShipPageCreate($ship_id,$shipData);
			
			if($ship == null)
			{
				$ship = new Ship();
			}

			$ship
				->setId(2000 + $ship_id)
				->setShipId(2000 + $ship_id)
				->setTurOperator($turOperator)
				->setName($shipName)
				->setCode($shipCode)
				;
			$em->persist($ship);			

			
			foreach($shipCabins as $deckName => $cabinss )
			{
				// проверим есть ли такая палуба, нет - добавим
				if(!isset($decksByName[$deckName]))
				{
					$deck = new ShipDeck();
					$deck
						->setName($deckName)
						
					;	
					$em->persist($deck);
					$em->flush();	
					$decksByName[$deck->getName()] = $deck;
					$decksById[$deck->getDeckId()] = $deck;					
				}
				
				

				foreach($cabinss as $cabinName => $rooms )
				{
					
					$countPlace = 10;
					foreach($rooms as $room)
					{
						$countPlace = $room['count'] < $countPlace ? $room['count'] : $countPlace;
					}
					
					// проверим есть ли такой тип каюты, нет - добавим
					if(!isset($room_types[$cabinName]))
					{
						$cabinType = new ShipCabinType();
						$cabinType 
							->setName($cabinName)
							->setComment($cabinName)
							->setPlaceCountMax($countPlace)
						;
						$em->persist($cabinType);
						$em->flush();
						$room_types[$cabinType->getComment()] = $cabinType;
						$room_typesById[$cabinType->getId()] = $cabinType;					
					}
					

					$cabinType = $cabinTypeRepos->findOneByComment($cabinName);
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
						$em->persist($cabin);
						$ship->addCabin($cabin);					
					}
					$cabin->setPlaceCount($room_places_count[$countPlace]);


					foreach($rooms as $roomItem)
					{

						$room = null;
						
						foreach($ship->getCabin() as $cabinTemp)
						{
							foreach($cabinTemp->getRooms() as $roomTemp )
							{
								if($roomTemp->getNumber() == $roomItem['name'] )
								{
									$room = $roomTemp;
								}
							}
						}
					
					if(null == $room)
					{
						$room = new ShipRoom();
						$room
							->setNumber($roomItem['name'])
							->setCountPass($roomItem['count'])
							->setCabin($cabin)
						;
						$em->persist($room);
						$cabin->addRoom($room);
					}
					else
					{
						$room
							->setCabin($cabin)
							->setCountPass($roomItem['count'])
							//->setCountPassMax((int) $roomItem->cabinmaxpass)
							;
					}					

					}
					// 	И ДОБАВИМ ЭТИ КАЮТЫ В ТЕПЛОХОД
					
					

				}

			}			
			$em->persist($ship);
			
			//dump($ship);
			
			$em->flush();
			
		}
		/* ПОДГОТОВКА */
		$cabins_all = $ship->getCabin();
		
		//dump($ship);
		
		foreach($cabins_all as $cabin)
		{
			$cabins[$cabin->getType()->getId()][$cabin->getDeck()->getDeckId()] = $cabin;
		}

		$tariffs = array();
		$cruiseTariffs = $tariffRepos->findAll();  
		foreach($cruiseTariffs as $tariff)
		{
			$tariffs[$tariff->getId()]  = $tariff;
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

		
		/* КОНЕЦ ПОДГОТОВКИ */
		
		
		// удаляем все круизы данного теплохода 
		// нужно оптимизировать в один запрос
		/*
		$cruises_remove = $cruiseRepos->findBy(['ship' => $ship]);
		foreach ($cruises_remove as $cr) {
			$em->remove($cr);
		}
		$em->flush();
		*/
		// ТЕПЕРЬ ОБОЙДЁМ ВСЕ КРУИЗЫ ЭТОГО ТЕПЛОХОДА
		foreach($cruises as $code => $cruise_i)
		{
			$code_inf = $code;
			$code  = (int)('2000000' + $code);
			
			$route = $cruise_i['route'];
			$startDate = new \DateTime($cruise_i["date_start"].' '.$cruise_i["time_start"]);
			$endDate = new \DateTime($cruise_i["date_end"].' '.$cruise_i["time_end"]);
			$dayCount = $cruise_i["days"];			

			// есть ли уже такой круиз?
			$cruise = $cruiseRepos->findOneById($code);
			if($cruise === null)
			{
				$cruise = new Cruise();
			}			
			else
			{
				$cruise->removeAllCategory();
				$em->flush();
			}	
		
		
			//dump($cruise_i['weekend']);
			if($cruise_i['weekend'])
			{
				//dump("Выходной");
				$cruise->addCategory($cat_week_end); 
			}
			
			
			$cruise
				->setShip($ship)
				->setId($code)
				//->setCode($code)
				->setName($route)
				->setStartDate($startDate)
				->setEndDate($endDate)
				->setDayCount($dayCount)
				->setTurOperator($turOperator)
				->setActive(true)
			;
			$ship->addCruise($cruise);
			
			$em->persist($cruise);
			
			$em->flush();
			
			
			foreach($cruise_i['prices'] as $priceItem)
			{
				
				
				// проверим есть ли такой тип каюты, нет - добавим
				if(!isset($room_types[$priceItem['name']]))
				{
					$cabinType = new ShipCabinType();
					$cabinType 
						->setName($priceItem['name'])
						->setComment($priceItem['name'])
					;
					$em->persist($cabinType);
					$em->flush();
					$room_types[$cabinType->getComment()] = $cabinType;
					$room_typesById[$cabinType->getId()] = $cabinType;					
				}
				
				$rt_name = $room_types[$priceItem['name']];
				
				if(isset($cabins[$rt_name->getId()]))
				{
					$cabin = $cabins[$rt_name->getId()];
				}
				else 
				{
					continue;
				}	
				//dump($rt_name);
				$rp_id = $room_places_count[$rt_name->getPlaceCountMax()];

				foreach($cabin as $cab)
				{
					$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
										'place' => $cab->getPlaceCount(),
										'cabin' => $cab,
										'meals' => $mealss[""],
										'tariff' => $tariffs[1],
										'cruise' => $cruise,
									]);
					
					if(null == $price)
					{
						
						$price = new Price();						
					}
							
					$price	
							->setPlace($cab->getPlaceCount())  
							->setTariff( $tariffs[1] )
							->setCruise($cruise)
							->setCabin($cab)
							->setPrice($priceItem['price'])
							->setMeals($mealss[""])
					;
					$em->persist($price);
					
					if($cab->getPlaceCount()->getRpId() > 1)
					{
						$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
											'place' => $cab->getPlaceCount(),
											'cabin' => $cab,
											'meals' => $mealss[""],
											'tariff' => $tariffs[2],
											'cruise' => $cruise,
										]);
						
						if(null == $price)
						{
							
							$price = new Price();						
						}
						$price	
								->setPlace($cab->getPlaceCount())  // а тут можно разрешить запись значения вместо объекта ( -1 запрос) 
								->setTariff( $tariffs[2] )
								->setCruise($cruise)
								->setCabin($cab)
								->setPrice((int)$priceItem['price']*0.85)
								->setMeals($mealss[""])
						;
						$em->persist($price);
					}

				}

			}
			
			/// осталось загрузить программы круизов
			$i = 0;
			do 
			{
				$i++;
				$programm = $this->getExcursions($code_inf, $ship_id );
			} while (!is_array($programm) && count($programm)>0 && $i < 10);
			
			if(!is_array($programm))
			{
				return array('error' => "Программа не прогружается ");
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

			foreach($programm as $programmItem)
			{
				// сделать проверку на прогрузку программы
				$cruise_program_item = new ProgramItem();
				$startDate = new \DateTime($programmItem["date_start"].' '.$programmItem["time_start"]);
				$endDate = new \DateTime($programmItem["date_end"].' '.$programmItem["time_end"]);
				
				$port = isset($places[$programmItem['city']]) ? $places[$programmItem['city']] : null;
				
				$cruise_program_item
								->setCruise($cruise)
								->setPlace($port)
								->setDateStart($startDate)
								->setDateStop($endDate)
								->setDescription(strip_tags($programmItem['description'], '<p><br><div>'))
								->setPlaceTitle($programmItem['city'])
								
					;	
				$em->persist($cruise_program_item);
				
				
			}			
			
			
			$em->persist($cruise);
		}






	$em->flush();
	
		$em->persist($ship);
		$em->flush();
		
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
		foreach($cruises2 as $cruise)
		{
			if(!in_array($cruise , $cruises1))
			{
				$cruise->setActive(false);
			}
		}
		$em->flush();	
		
	return ["ship"=>$shipName];






		
	}
	
	
}