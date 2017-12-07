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
use LoadBundle\Controller\Helper;


class LoadVodohod  extends Controller
{
	const PATH_IMG = "/files/ship/";	
	
	private $doctrine;
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

		
	public function __construct($doctrine, $templating, $simple_html_dom)
	{
		$this->doctrine = $doctrine;
		$this->templating = $templating;
		$this->simple_html_dom = $simple_html_dom;
	}
	
	public function load($ship_id, $update = false)
	{
		$dump = [];
		$base_url = "https://vodohod.spb.ru/api/";
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
		
		$turOperator = $this->doctrine->getRepository('CruiseBundle:TurOperator')->findOneById(1);
		//$ship = $shipRepos->findOneById($ship_id);
		
		$qb = $em->createQueryBuilder()
				->select('s,cab,rooms')
				->from('CruiseBundle:Ship', 's')
				->leftJoin('s.cabin','cab')
				->leftJoin('cab.rooms','rooms')
				->where('s.id = '.$ship_id)

		;
		
		$ship = $qb->getQuery()->getOneOrNullResult();

		dump($ship);

		/// Загружается с питерского сайта
		$this->getSPB();		
		

		/* Подготовка массивов */
		
		$placesAll = $placeRepos->findAll();
		foreach($placesAll as $placesAllItem)
		{
			$places[$placesAllItem->getName()] = $placesAllItem;
		}
		
		$tariffs = array();
		$cruiseTariffs = $tariffRepos->findAll();  
		foreach($cruiseTariffs as $tariff)
		{
			$tariffs[$tariff->getName()]  = $tariff;
		}	
		
		$mealss = array();
		$cruiseMeals = $mealsRepos->findAll();  
		foreach($cruiseMeals as $meals)
		{
			$mealss[$meals->getName()]  = $meals;
		}
		
		$decks = array();
		$decksAll = $decksRepos->findAll();
		foreach($decksAll as $deck)
		{
			$decks[$deck->getName()] = $deck;
			$decksById[$deck->getDeckId()] = $deck;
		}
		
 		
		$room_types = array();
		$room_types_all = $cabinTypeRepos->findAll();
		foreach($room_types_all as $room_type)
		{
			$room_types[$room_type->getComment()] = $room_type;
			$room_typesById[$room_type->getRtId()] = $room_type;
		} 	
		
		$room_places = array();
		$room_places_all = $cabinPlaceRepos->findAll();
		foreach($room_places_all as $room_place)
		{
			$room_places[$room_place->getRpName()] = $room_place;
		}
			

		$categoryCruisesAll = array();
		$crCategory_all = $categoryRepos->findAll();
		foreach($crCategory_all as  $categoryCruises)
		{
			$categoryCruisesAll[$categoryCruises->getName()] = $categoryCruises;
		}
		/* Конец подготовки   */


		
		// добавить возможность принудительного обновления (добавили)
		if($ship == null  || $update )
		{
			
			$dump = $motorship = $this->getMotorship($ship_id);
			$this->ShipPageCreate($ship_id);
		
			/*
			
			if ($ship != null) {
				$em->remove($ship);
				$em->flush();
			}
			$ship = new Ship();
			*/
			
			if($ship == null)
			{
				$ship = new Ship();
			}
			
			
			$ship
				->setId($ship_id)
				->setShipId($ship_id)
				->setTurOperator($turOperator)
				->setName($motorship["name"])
				->setCode($motorship["code"])
				;
				
				
			/* Cabins */
			// ROOM 

			// получаем все кабины и заносим их $ship->addRoom($num,$cabinClass,$deck,$side)
			$room_url = $base_url."rooms.php?motorship_id=".$ship_id;
			$room_json = $this->curl_get_file_contents($room_url);
			$rooms_v = json_decode($room_json,true);


			foreach($rooms_v as $room_v)
			{
				$rooms[$room_v['deck_id']][$room_v['rt_id']][]	 = $room_v;
			}
			
			
			$cabin_url = $base_url."rooms_motorship.php?motorship_id=".$ship_id;
			$cabin_json = $this->curl_get_file_contents($cabin_url);
			$cabins_v = json_decode($cabin_json,true);
			
			//dump($ship);
			
			foreach($cabins_v as $cabin_v)
			{
				//$cabin = new ShipCabin();
				
				$cabinType = $cabinTypeRepos->findOneByRtId($cabin_v['rt_id']);  //  используется id водохода
				$deck = $decksRepos->findOneByDeckId((int)$cabin_v['deck_id']);
				
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

				
				foreach($rooms[$cabin_v['deck_id']][$cabin_v['rt_id']] as $roomItem)
				{
					$room = null;

					foreach($ship->getCabin() as $cabinTemp)
					{
						foreach($cabinTemp->getRooms() as $roomTemp )
						{
							if($roomTemp->getNumber() == $roomItem['room_number'] )
							{
								$room = $roomTemp;
							}
						}
					}					
					
					if(null == $room)
					{
						$room = new ShipRoom();
						$room
							->setNumber($roomItem['room_number'])
							->setCabin($cabin)
						;
						$cabin->addRoom($room);
						$em->persist($room);						
					}

				}

				$em->persist($cabin);
			}
			

			/* Конец Cabins */				
				
			$em->persist($ship);
			$em->flush();
		}
		
		
		$cabins = array();
		$cabins_all = $ship->getCabin();
		foreach($cabins_all as $cabin)
		{
			$cabins[$cabin->getType()->getRtId()][$cabin->getDeck()->getDeckId()] = $cabin;
		}

			
			
		
		
		
		
		/// Загружаем круизы 				
		$url_cruises = "http://cruises.vodohod.com/agency/json-cruises.htm?pauth=jnrehASKDLJcdakljdx";
		$cruises_json = $this->curl_get_file_contents($url_cruises);
		$cruises_v = json_decode($cruises_json,true);
		
		foreach($cruises_v as $key => $cruises_v_item)
		{
			if($cruises_v_item["motorship_id"] !=  $ship_id )
			{
				unset($cruises_v[$key]);
			}
		}
		
		$cruisesArray = [];
		$cruises_ship = $cruiseRepos->findBy(['ship' => $ship]);
		foreach ($cruises_ship as $cr) {
			$cruisesArray[$cr->getId()] = $cr;
 		}


		foreach($cruises_v as $id => $cruise_v)
		{
			if(isset($cruisesArray[$id]))
			{
				$cruise = $cruisesArray[$id];
			}
			else
			{
				$cruise = new Cruise();
			}

			$cruise->setId($id);
			$cruise->setShip($ship);
			$cruise->setName($cruise_v["name"]);
			$cruise->setStartDate(new \DateTime($cruise_v["date_start"]));
			$cruise->setEndDate(new \DateTime($cruise_v["date_stop"]));
			$cruise->setDayCount($cruise_v["days"]);
			$cruise->setTurOperator($turOperator);
			
			// удаляем связи с категориями
			$cruise->removeAllCategory();
			// и программы круиза
			$cruise->removeAllProgram();
			$programm_del = $em->getRepository("CruiseBundle:ProgramItem")->findByCruise($cruise);
			foreach($programm_del as $p_del)
			{
				$em->remove($p_del);
			}
			
			$em->persist($cruise);
			$em->flush();
			
			
			foreach($cruise_v["directions"] as $direct)
			{
				if(!isset($categoryCruisesAll[$direct]))
				{
					$category = new Category();
					$category 
						->setCode(Helper\Convert::translit($direct))
						->setName($direct)
					;
					$em->persist($category);
					$categoryCruisesAll[$direct] = $category;
				}
				$cruise->addCategory($categoryCruisesAll[$direct]);
			}
			
			
			$startDate = strtotime($cruise_v["date_start"]);
			// стоянки
			
			$program_item_url = "http://cruises.vodohod.com/agency/json-days.htm?pauth=jnrehASKDLJcdakljdx&cruise=".$id;
			$program_item_json = $this->curl_get_file_contents($program_item_url);
			$program_items_v = json_decode($program_item_json,true);
			foreach($program_items_v as $program_item_v)
			{
				$cruise_program_item = new ProgramItem();
				$date_item_start = new \DateTime( date("d-m-Y ",$startDate+($program_item_v['day']-1)*60*60*24).$program_item_v['time_start']);//($program_item_v['day']-1);
				$date_item_stop = new \DateTime( date("d-m-Y ",$startDate+($program_item_v['day']-1)*60*60*24).$program_item_v['time_stop']);//($program_item_v['day']-1);
				$cruise_program_item
							
							->setCruise($cruise)
							->setPlace($places[$program_item_v['port']])
							->setDateStart($date_item_start)
							->setDateStop($date_item_stop)
							->setDescription(nl2br($program_item_v['excursion']))
							->setPlaceTitle($program_item_v['port'])
							
				;	
				$em->persist($cruise_program_item);
			}			
			
			
			
			/// Теперь в каждом круизе можно прогрузить прайсы
			
			$url_prices  = "http://cruises.vodohod.com/agency/json-prices.htm?pauth=jnrehASKDLJcdakljdx&cruise=".$id;
			$prices_json = $this->curl_get_file_contents($url_prices);
			$prices_v = json_decode($prices_json,true);
				
			foreach($prices_v['tariffs'] as $p_t)
			{
				
				
				if(isset($tariffs[$p_t['tariff_name']]))
				{
					$cruiseTariff = $tariffs[$p_t['tariff_name']];
				}
				else
				{
					$cruiseTariff = new Tariff();
					$cruiseTariff
						->setName($p_t['tariff_name'])
						;
					$em->persist($cruiseTariff);
					$em->flush();
					$tariffs[$p_t['tariff_name']] = $cruiseTariff; 
				}

				
				foreach($p_t['prices'] as $key => $prices)
				{
					
					
					$deck = $decks[$prices['deck_name']];
					$rt_name = $room_types[$prices['rt_name']];
					$rp_id = $room_places[$prices['rp_name']];
					$price_value = $prices['price_value'];
					
					if($price_value <= 0) continue;

					// запишем это всё в price

					 
						if(isset($cabins[$rt_name->getRtId()][$deck->getDeckId()]))
						{
							$cabin = $cabins[$rt_name->getRtId()][$deck->getDeckId()];
						}
						else 
						{
							continue;
						}	
					
					$price = $em->getRepository("CruiseBundle:Price")->findOneBy([
										'place' => $rp_id,
										'cabin' => $cabin,
										'meals' => $mealss[""],
										'tariff' => $cruiseTariff,
										'cruise' => $cruise,
									]);
					if(null == $price)
					{
						$price = new Price();						
					}
					

					
					$price	
							->setPlace($rp_id)  // а тут можно разрешить запись значения вместо объекта ( -1 запрос) 
							->setTariff($cruiseTariff)
							->setCruise($cruise)
							->setCabin($cabin)
							->setPrice($price_value)
							->setMeals($mealss[""])
					;
					$em->persist($price);
					
				}
				
			}
			

			$em->persist($cruise);
			$ship->addCruise($cruise);
		}
		
		
		$em->persist($ship);
		$em->flush();

			
		return ['ship'=>$ship->getName()];
	}


	
	
	
	
	
	
	
	
	
	
	
	

	
	public function getMotorship($id)
	{
		$url_motorships = "http://cruises.vodohod.com/agency/json-motorships.htm?pauth=jnrehASKDLJcdakljdx";
		$motorships_json = $this->curl_get_file_contents($url_motorships);
		$motorships = json_decode($motorships_json,true);
		return $motorships[$id];
		
	}
	
	/// Данную функцию можно вызвать лишь раз для инициализации или при изменениях у Водохода
	public function getSPB()
	{
		$base_url = "https://vodohod.spb.ru/api/";	
		$em = $this->doctrine->getManager();
		
		// палубы 
		
		$decksRepos = $this->doctrine->getRepository('CruiseBundle:ShipDeck');
		$cabinTypeRepos = $this->doctrine->getRepository('CruiseBundle:ShipCabinType');
		$cabinPlaceRepos = $this->doctrine->getRepository('CruiseBundle:ShipCabinPlace');
		$placeRepos = $this->doctrine->getRepository('CruiseBundle:Place');

		
		$decks_url = $base_url."decks.php";
		$decks_json = $this->curl_get_file_contents($decks_url);
		$decks_v = json_decode($decks_json,true);

		foreach($decks_v as $deck_v)
		{
			$deck = $decksRepos->findOneByDeckId($deck_v['deck_id']);
			if ($deck != null) {
				continue;
			}
			$deck = new ShipDeck();
			$deck
				->setName($deck_v['deck_name'])
				->setDeckId($deck_v['deck_id'])
			;
			$em->persist($deck);
		}
		$em->flush();
		// палубы загрузили	

		// типерь загрузим типы кают
		$room_types_url = $base_url."room_types.php";
		$room_types_json = $this->curl_get_file_contents($room_types_url);
		$room_types_v = json_decode($room_types_json,true);
			
		foreach($room_types_v as $room_type_v)
		{
			$room_type = $cabinTypeRepos->findOneByRtId($room_type_v['rt_id']);
			if ($room_type != null) {
				continue;
			}
			$room_type = new ShipCabinType();
			$room_type
				->setRtId($room_type_v['rt_id'])
				->setName($room_type_v['rt_name'])
				->setComment($room_type_v['rt_comment'])
			;
			$em->persist($room_type);
		}
		$em->flush();

		// теперь загрузим типы размещения в каютах
		$room_placings_url = $base_url."room_placings.php";
		$room_placings_json = $this->curl_get_file_contents($room_placings_url);
		$room_placings_v = json_decode($room_placings_json,true);
				
		foreach($room_placings_v as $room_place_v)
		{
			$room_place = $cabinPlaceRepos->findOneByRpId($room_place_v['rp_id']);
			if ($room_place != null) {
				continue;
			}
			$room_place = new ShipCabinPlace();
			$room_place
				->setRpId($room_place_v['rp_id'])
				->setRpName($room_place_v['rp_name'])
			;
			$em->persist($room_place);
		}
		$em->flush();	
		
		
		// загрузим порты 
		
		$ports_url = $base_url."ports.php";
		$ports_json =  $this->curl_get_file_contents($ports_url);
		$ports_v = json_decode($ports_json,true);
		foreach($ports_v as $port_v)
		{
			$place = $placeRepos->findOneByPlaceId($port_v['port_id']);
			if($place != null)
			{
				continue;
			}
			
			$place = new Place();
			$place 
				->setPlaceId($port_v['port_id'])
				->setName($port_v['port_name'])
				->setUrl($port_v['port_code'])
			;
			$em->persist($place);
		}
		$em->flush(); 		
			
	}
	
	public function ShipPageCreate($ship_id)
	{
		
		
		$motorship = $this->getMotorship($ship_id);
			
			
		$shipCode = $motorship['code'];
		$shipName = $motorship['name'];
		$shipBody = $motorship['description'];
		
			
		// Копируем фотографии
		
		$dir = (__DIR__).'/../../../web'.self::PATH_IMG.$shipCode;

		if(!is_dir($dir)) mkdir($dir,0777,true) ;
		$img_main = "https://vodohod.com/cruises/vodohod/".$shipCode."/".$shipCode."-main.jpg";
		$newfile = $dir.'/'.$shipCode.'-main.jpg';
		$file_content = $this->curl_get_file_contents($img_main);
		$fp = fopen($newfile, "w");
		$test = fwrite($fp, $file_content); // Запись в файл
		//if ($test) echo 'Данные в файл успешно занесены.';
		//else echo 'Ошибка при записи в файл.';
		fclose($fp); //Закрытие файла	

		$img_decks = "https://vodohod.com/cruises/vodohod/".$shipCode."/".$shipCode."-decks.gif";
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
				"ship_id" => $ship_id,
				"ship_name" => $shipName,
				));
		
		$em = $this->doctrine->getManager();
		
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
				->setTitle($shipName)
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
			
			$base_url_vodohod = "https://vodohod.com";
			$parser = $this->simple_html_dom;
			$htmlFoto = $this->curl_get_file_contents("https://vodohod.com/cruises/vodohod/".$shipCode."/foto.htm");
			$parser->load($htmlFoto);
			
			if ( !$htmlFoto || isset($parser->getElementByTagName('h2')->attr['title']) && $parser->getElementByTagName('h2')->attr['title'] == "error 404")
			{
			$htmlFoto = $this->curl_get_file_contents("https://vodohod.com/cruises/vodohod/".$shipCode."/photo.htm");
				$parser->load($htmlFoto);				
			}
			
			$page->removeAllFile();
			$em->flush();
			
			$sort = 1;
			foreach($parser->find('.item a') as $element) 
			{
								
				// получаем адрес фото
				$photo_url =  $base_url_vodohod.$element->href;
				$photo_title = $element->title;
				
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