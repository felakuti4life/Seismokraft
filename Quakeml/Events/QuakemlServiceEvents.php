<?php
/**
 * File for class QuakemlServiceEvents
 * @package Quakeml
 * @subpackage Services
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
/**
 * This class stands for QuakemlServiceEvents originally named Events
 * @package Quakeml
 * @subpackage Services
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
class QuakemlServiceEvents extends QuakemlWsdlClass
{
	/**
	 * Method to call the operation originally named events
	 * @uses QuakemlWsdlClass::getSoapClient()
	 * @uses QuakemlWsdlClass::setResult()
	 * @uses QuakemlWsdlClass::getResult()
	 * @uses QuakemlWsdlClass::saveLastError()
	 * @uses QuakemlStructEvents::getUri()
	 * @uses QuakemlStructEvents::getDateMin()
	 * @uses QuakemlStructEvents::getDateMax()
	 * @uses QuakemlStructEvents::getLatMin()
	 * @uses QuakemlStructEvents::getLatMax()
	 * @uses QuakemlStructEvents::getLonMin()
	 * @uses QuakemlStructEvents::getLonMax()
	 * @uses QuakemlStructEvents::getDepthMin()
	 * @uses QuakemlStructEvents::getDepthMax()
	 * @uses QuakemlStructEvents::getNetMagMin()
	 * @uses QuakemlStructEvents::getNetMagMax()
	 * @uses QuakemlStructEvents::getNetMagType()
	 * @uses QuakemlStructEvents::getAuthor()
	 * @uses QuakemlStructEvents::getStartRow()
	 * @uses QuakemlStructEvents::getLimit()
	 * @param QuakemlStructEvents $_quakemlStructEvents
	 * @return QuakemlStructEventsResponse
	 */
	public function events(QuakemlStructEvents $_quakemlStructEvents)
	{
		try
		{
			$this->setResult(new QuakemlStructEventsResponse(self::getSoapClient()->events(array('uri'=>$_quakemlStructEvents->getUri(),'dateMin'=>$_quakemlStructEvents->getDateMin(),'dateMax'=>$_quakemlStructEvents->getDateMax(),'latMin'=>$_quakemlStructEvents->getLatMin(),'latMax'=>$_quakemlStructEvents->getLatMax(),'lonMin'=>$_quakemlStructEvents->getLonMin(),'lonMax'=>$_quakemlStructEvents->getLonMax(),'depthMin'=>$_quakemlStructEvents->getDepthMin(),'depthMax'=>$_quakemlStructEvents->getDepthMax(),'netMagMin'=>$_quakemlStructEvents->getNetMagMin(),'netMagMax'=>$_quakemlStructEvents->getNetMagMax(),'netMagType'=>$_quakemlStructEvents->getNetMagType(),'author'=>$_quakemlStructEvents->getAuthor(),'startRow'=>$_quakemlStructEvents->getStartRow(),'limit'=>$_quakemlStructEvents->getLimit()))));
		}
		catch(SoapFault $soapFault)
		{
			return !$this->saveLastError(__METHOD__,$soapFault);
		}
		return $this->getResult();
	}
	/**
	 * Returns the result
	 * @see QuakemlWsdlClass::getResult()
	 * @return QuakemlStructEventsResponse
	 */
	public function getResult()
	{
		return parent::getResult();
	}
	/**
	 * Method returning the class name
	 * @return string __CLASS__
	 */
	public function __toString()
	{
		return __CLASS__;
	}
}
?>