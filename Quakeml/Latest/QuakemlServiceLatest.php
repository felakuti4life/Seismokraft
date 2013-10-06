<?php
/**
 * File for class QuakemlServiceLatest
 * @package Quakeml
 * @subpackage Services
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
/**
 * This class stands for QuakemlServiceLatest originally named Latest
 * @package Quakeml
 * @subpackage Services
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
class QuakemlServiceLatest extends QuakemlWsdlClass
{
	/**
	 * Method to call the operation originally named latestEvents
	 * @uses QuakemlWsdlClass::getSoapClient()
	 * @uses QuakemlWsdlClass::setResult()
	 * @uses QuakemlWsdlClass::getResult()
	 * @uses QuakemlWsdlClass::saveLastError()
	 * @uses QuakemlStructLatestEvents::getComplete()
	 * @uses QuakemlStructLatestEvents::getNum()
	 * @param QuakemlStructLatestEvents $_quakemlStructLatestEvents
	 * @return QuakemlStructLatestEventsResponse
	 */
	public function latestEvents(QuakemlStructLatestEvents $_quakemlStructLatestEvents)
	{
		try
		{
			$this->setResult(new QuakemlStructLatestEventsResponse(self::getSoapClient()->latestEvents(array('complete'=>$_quakemlStructLatestEvents->getComplete(),'num'=>$_quakemlStructLatestEvents->getNum()))));
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
	 * @return QuakemlStructLatestEventsResponse
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