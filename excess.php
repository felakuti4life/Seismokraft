/*uncomment for Quakeml library
include_once dirname(__FILE__) . '/Quakeml/QuakemlAutoload.php';
define('QUAKEML_WSDL_URL','http://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_hour.quakeml');
define('QUAKEML_USER_LOGIN','');
define('QUAKEML_USER_PASSWORD','');

 //Wsdl instanciation infos

$wsdl = array();
$wsdl[QuakemlWsdlClass::WSDL_URL] = QUAKEML_WSDL_URL;
$wsdl[QuakemlWsdlClass::WSDL_CACHE_WSDL] = WSDL_CACHE_NONE;
$wsdl[QuakemlWsdlClass::WSDL_TRACE] = true;
if(QUAKEML_USER_LOGIN !== '')
	$wsdl[QuakemlWsdlClass::WSDL_LOGIN] = QUAKEML_USER_LOGIN;
if(QUAKEML_USER_PASSWORD !== '')
	$wsdl[QuakemlWsdlClass::WSDL_PASSWD] = QUAKEML_USER_PASSWORD;
	
	
$quakemlServiceLatest = new QuakemlServiceLatest($wsdl);


if($quakemlServiceLatest->latestEvents(new QuakemlStructLatestEvents()))

	echo $quakemlServiceLatest->getResult();

else

	echo $quakemlServiceLatest->getLastError();

end comment */
/*
$url = "http://earthquake.usgs.gov/earthquakes/feed/v1.0/summary/2.5_day.quakeml";
$xml = file_get_contents($url);
$quake_table = new SimpleXMLElement($xml);
class Location {
	var $depth;
	var $lng;
	var $lat;
	
	function Location(){
		$this->depth = float;
		$this->lng = float;
		$this->lat = float;
	}
}

class SeismicEvent {
	var $location;
	var $date;
	var $magnitude;
	
	function SeismicEvent(){
		$this->location = new Location;
		$this->date = new date("Y-m-dTh:m:s");
		$this->magnitude = float;
	}
}

$eventOne = new SeismicEvent;
/*
$eventOne->location->depth =  $quake_table->q:quakeml->eventParameters->event[0]->origin->depth->value;
$eventOne->location->lng = $quake_table->q:quakeml->eventParameters->event[0]->origin->longitude->value;
$eventOne->location->lat = $quake_table->q:quakeml->eventParameters->event[0]->origin->latitude->value;
$eventOne->date = $quake_table->q:quakeml->eventParameters->event[0]->origin->time->value;
$eventOne->magnitude = $quake_table->q:quakeml->eventParameters->event[0]->magnitude->mag->value;

echo $quake_table;*/