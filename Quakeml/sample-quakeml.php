<?php
/**
 * Test with Quakeml
 * @package Quakeml
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 04/03/2013
 */
ini_set('memory_limit','512M');
ini_set('display_errors', true);
error_reporting(-1);
/**
 * Load autoload
 */
require_once dirname(__FILE__) . '/QuakemlAutoload.php';
/**
 * Quakeml Informations
 */
define('QUAKEML_WSDL_URL','http://www.seismicportal.eu/services/ws/quakeml?wsdl');
define('QUAKEML_USER_LOGIN','');
define('QUAKEML_USER_PASSWORD','');
/**
 * Wsdl instanciation infos
 */
$wsdl = array();
$wsdl[QuakemlWsdlClass::WSDL_URL] = QUAKEML_WSDL_URL;
$wsdl[QuakemlWsdlClass::WSDL_CACHE_WSDL] = WSDL_CACHE_NONE;
$wsdl[QuakemlWsdlClass::WSDL_TRACE] = true;
if(QUAKEML_USER_LOGIN !== '')
	$wsdl[QuakemlWsdlClass::WSDL_LOGIN] = QUAKEML_USER_LOGIN;
if(QUAKEML_USER_PASSWORD !== '')
	$wsdl[QuakemlWsdlClass::WSDL_PASSWD] = QUAKEML_USER_PASSWORD;
// etc....
/**
 * Examples
 */


/**********************************
 * Example for QuakemlServiceEvents
 */
$quakemlServiceEvents = new QuakemlServiceEvents($wsdl);
// sample call for QuakemlServiceEvents::events()
if($quakemlServiceEvents->events(new QuakemlStructEvents(/*** update parameters list ***/)))
	print_r($quakemlServiceEvents->getResult());
else
	print_r($quakemlServiceEvents->getLastError());

/**********************************
 * Example for QuakemlServiceLatest
 */
$quakemlServiceLatest = new QuakemlServiceLatest($wsdl);
// sample call for QuakemlServiceLatest::latestEvents()
if($quakemlServiceLatest->latestEvents(new QuakemlStructLatestEvents(/*** update parameters list ***/)))
	print_r($quakemlServiceLatest->getResult());
else
	print_r($quakemlServiceLatest->getLastError());
?>