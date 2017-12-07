<?php
namespace CruiseBundle\Service;

//use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query\ResultSetMapping;
//use Doctrine\ORM\EntityManager;
//use Symfony\Component\HttpFoundation\Response;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
//use Symfony\Component\Config\Definition\Exception\Exception;
//use Symfony\Component\DependencyInjection\Container;

class CruiseSearch
{

    private $doctrine;


    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }
	
	public function searchCruise($parameters = array())
	{
		$em = $this->doctrine->getManager();
		$rsm = new ResultSetMapping;
		$rsm->addEntityResult('CruiseBundle:Cruise', 'c');
		$rsm->addFieldResult('c', 'c_id', 'id');
		$rsm->addMetaResult('c', 'c_ship', 'ship');
		$rsm->addFieldResult('c', 'c_startdate', 'startDate');
		$rsm->addFieldResult('c', 'c_enddate', 'endDate');
		$rsm->addFieldResult('c', 'c_daycount', 'dayCount');
		$rsm->addFieldResult('c', 'c_name', 'name');
		//$rsm->addMetaResult('c', 'c_code', 'code');
		$rsm->addMetaResult('c', 'c_tur_operator', 'turOperator');
		$rsm->addJoinedEntityResult('CruiseBundle:TurOperator', 'to','c', 'turOperator');
		$rsm->addFieldResult('to', 'to_id', 'id');
		$rsm->addFieldResult('to', 'to_name', 'name');
		$rsm->addFieldResult('to', 'to_inSale', 'inSale');
		$rsm->addMetaResult('c', 'c_type_discount', 'typeDiscount');
		$rsm->addJoinedEntityResult('CruiseBundle:TypeDiscount', 'td','c', 'typeDiscount');
		$rsm->addFieldResult('td', 'td_id', 'id');
		$rsm->addFieldResult('td', 'td_name', 'name');
		$rsm->addFieldResult('td', 'td_value', 'value');
		$rsm->addJoinedEntityResult('CruiseBundle:Ship', 's','c', 'ship');
		$rsm->addFieldResult('s', 's_id', 'id');
		$rsm->addFieldResult('s', 's_name', 'name');
		$rsm->addFieldResult('s', 's_code', 'code');
		$rsm->addFieldResult('s', 's_m_id', 'shipId');
		$rsm->addJoinedEntityResult('CruiseBundle:Price', 'p','c', 'prices');
		$rsm->addFieldResult('p', 'p_id', 'id');
		$rsm->addFieldResult('p', 'p_price', 'price');

		$where = "";
		$join = "";
		
		// даты unix окончание - последняя дата начала // для моиска по месяцам
		if(isset($parameters['startdate']))
		{
			$where .= "
			AND c.startdate >= ".$parameters['startdate'];
		}		
		if(isset($parameters['enddate']))
		{
			$where .= "
			AND c.startdate <= ".$parameters['enddate'];
		}	

		// даты человеческие
		if(isset($parameters['startDate']))
		{
			$where .= "
			AND c.startDate >= '".($parameters['startDate'])."'";
		}		
		if(isset($parameters['endDate']))
		{
			$where .= "
			AND c.endDate <= '".($parameters['endDate'])."'";
		}
		if(isset($parameters['ship']) && ($parameters['ship'] > 0) )
		{
			$where .= "
			AND s.shipId = ".$parameters['ship'];
		}
		
		/*
		if(isset($parameters['specialoffer']) && isset($parameters['burningCruise']))
		{
			$where .= "
			AND ((code.specialOffer = 1) OR (code.burningCruise = 1)) ";	
		}
		else
		{
			if(isset($parameters['specialoffer']))
			{
				$where .= "
				AND code.specialOffer = 1";			
			}
			if(isset($parameters['burningCruise']))
			{
				$where .= "
				AND code.burningCruise = 1";			
			}		
		}
		*/
		if(isset($parameters['places']))
		{
			$join .= "
			LEFT JOIN program_item pi ON pi.cruise_id = c.id
			LEFT JOIN place cp ON pi.place_id = cp.id
			";
			$where .= "
			AND cp.place_id IN (".implode(',',$parameters['places']).")";	
			
		}
		
		if(isset($parameters['days']))
		{
			list($mindays,$maxdays) = explode(',',$parameters['days']);
			$where .= "
			AND c.daycount >=".$mindays;
			$where .= "
			AND c.daycount <=".$maxdays;			
		}	

		if(isset($parameters['placeStart']) && ($parameters['placeStart'] != "all" ) )
		{
			$where .= "
			AND c.name LIKE '".$parameters['placeStart']."%'";
		}
		
		$sql = "
		SELECT 
			c.id c_id , c.ship_id c_ship, c.startDate c_startdate, c.endDate c_enddate, c.dayCount c_daycount,  c.name c_name, c.tur_operator_id c_tur_operator
			,
			s.id s_id, s.name s_name, s.code s_code, s.shipId s_m_id 
			,
			t.id to_id, t.name to_name, t.inSale to_inSale
			,
			td.id td_id, td.name td_name, td.value td_value
			,
			p.id p_id, p.price p_price

		FROM cruise c
		
		LEFT JOIN tur_operator t ON c.tur_operator_id = t.id
		LEFT JOIN type_discount td ON c.type_discount_id = td.id
		
		".$join."
		LEFT JOIN ship s ON c.ship_id = s.id
		
		LEFT JOIN 
		
			(
				SELECT p2.id , MIN(p2.price) price, p2.cruise_id
				FROM (SELECT * FROM price ORDER BY price) p2
				LEFT JOIN tariff ON tariff.id = p2.tariff_id
				WHERE tariff.name LIKE '%взрослый%'
				GROUP BY p2.cruise_id
			) p ON c.id = p.cruise_id

		WHERE 1
		"
		.$where.
		"
		ORDER BY c.startDate
		";
		
		$query = $em->createNativeQuery($sql, $rsm);
		
		
		//$query->setParameter(1, 'romanb');
		
		$result = $query->getResult();
		return $result;
	}	
	
	
}