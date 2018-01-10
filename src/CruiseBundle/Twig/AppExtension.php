<?php

namespace CruiseBundle\Twig;

class AppExtension extends \Twig_Extension
{
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('numeral', array($this, 'numeralFilter')),
            new \Twig_SimpleFilter('ports', array($this, 'portsFilter')),
            new \Twig_SimpleFilter('nl2js', array($this, 'nl2jsFilter')),
            new \Twig_SimpleFilter('nl2p', array($this, 'nl2pFilter')),
            new \Twig_SimpleFilter('nospace', array($this, 'nospaceFilter')),
        );
    }

    public function numeralFilter($kol, $one, $two, $five)
    {

		if (($kol%100 >10 ) and ($kol%100 < 20)) {$ok = $five;}
		else {
		$ost = $kol%10;
		if ($ost == 1) {$ok = $one;}
		elseif (($ost > 1) and ($ost < 5)) {$ok = $two;}
		else {$ok = $five;};
		};
		return $ok;
    }
	
    public function portsFilter($port_name)
    {
		$port_name=str_replace("Городец (Галанино)", "Городец", $port_name);
		$port_name=str_replace("Москва (Северный речной вокзал)", "Москва (СРВ)", $port_name);
		$port_name=str_replace("Санкт-Петербург (причал \"Уткина заводь\")", "Санкт-Петербург (причал «Уткина заводь»)", $port_name);
		return $port_name;
    }	
	
    public function nl2jsFilter($text)
    {
		
		return str_replace(PHP_EOL,'<br>',$text);
    }	
	
	
    public function nl2pFilter($text)
    {
		$arr = explode(PHP_EOL,$text);
		return '<p>'.implode('</p><p>',$arr).'</p>';
    }
	
	public function nospaceFilter($text)
	{
		return str_replace(" ", "", $text);
	}
	
	
	
}