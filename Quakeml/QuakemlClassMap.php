<?php
/**
 * File for the class which returns the class map definition
 * @package Quakeml
 * @date 2013-03-04
 */
/**
 * Class which returns the class map definition by the static method QuakemlClassMap::classMap()
 * @package Quakeml
 * @date 2013-03-04
 */
class QuakemlClassMap
{
	/**
	 * This method returns the array containing the mapping between WSDL structs and generated classes
	 * This array is sent to the SoapClient when calling the WS
	 * @return array
	 */
	final public static function classMap()
	{
		return array (
  'events' => 'QuakemlStructEvents',
  'eventsResponse' => 'QuakemlStructEventsResponse',
  'latestEvents' => 'QuakemlStructLatestEvents',
  'latestEventsResponse' => 'QuakemlStructLatestEventsResponse',
);
	}
}
?>