<?php

namespace LoadBundle\Controller\Helper;

class Convert
{
	public static function translit($str) {
		$str = trim($str);
		$expl = explode(" ", $str);
		/*
		$res = "";
		foreach ($expl as $i=>$part) {
			$part = trim($part);
			$res .= $part;
			if ($i < sizeof($expl) - 1) {
				$res += " ";
			}
		}
		*/
		$str = self::string_translit($str);
		$translit=array(" "=>"-", "."=>"_","/"=>"_" , );
		$str = strtr($str, $translit);
		return strtolower(trim($str));
	}


	public static function string_translit($str) {
		$transtable = array ('А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'Yo', 'Ж' => 'Zh', 'З' => 'Z', 'И' => 'I', 'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'Ts', 'Ч' => 'Ch', 'Ш' => 'Sh', 'Щ' => 'Shch', 'Ъ' => '', 'Ы' => 'I', 'Ь' => '', 'Э' => 'E', 'Ю' => 'Yu', 'Я' => 'Ya', 'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l', 'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts', 'ч' => 'ch', 'ш' => 'sh', 'щ' => 'shch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e', 'ю' => 'yu', 'я' => 'ya');
		$str = strtr ( $str, $transtable );
		return $str;
	}
	
	public static function month_ru($timestamp) {
		setlocale(LC_TIME, "ru_RU.UTF-8");
		//$res = mb_convert_encoding(strftime('%B %Y', $timestamp), "UTF-8", "WINDOWS-1251");
		$res = strftime('%B %Y', $timestamp);
		$months = array(
			"January" => "Январь",
			"February" => "Февраль",
			"March" => "Март",
			"April" => "Апрель",
			"May" => "Май",
			"June" => "Июнь",
			"July" => "Июль",
			"August" => "Август",
			"September" => "Сентябрь",
			"October" => "Октябрь",
			"November" => "Ноябрь",
			"December" => "Декабрь"
		);
		return str_replace(array_keys($months), array_values($months), $res);
	}
}