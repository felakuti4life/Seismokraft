<?php
/**
 * File for class QuakemlStructEventsResponse
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
/**
 * This class stands for QuakemlStructEventsResponse originally named eventsResponse
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
class QuakemlStructEventsResponse extends QuakemlWsdlClass
{
	/**
	 * The quakeml
	 * @var anyType
	 */
	public $quakeml;
	/**
	 * The error
	 * @var anyType
	 */
	public $error;
	/**
	 * Constructor method for eventsResponse
	 * @see parent::__construct()
	 * @param anyType $_quakeml
	 * @param anyType $_error
	 * @return QuakemlStructEventsResponse
	 */
	public function __construct($_quakeml = NULL,$_error = NULL)
	{
		parent::__construct(array('quakeml'=>$_quakeml,'error'=>$_error));
	}
	/**
	 * Get quakeml value
	 * @return anyType|null
	 */
	public function getQuakeml()
	{
		return $this->quakeml;
	}
	/**
	 * Set quakeml value
	 * @param anyType the quakeml
	 * @return anyType
	 */
	public function setQuakeml($_quakeml)
	{
		return ($this->quakeml = $_quakeml);
	}
	/**
	 * Get error value
	 * @return anyType|null
	 */
	public function getError()
	{
		return $this->error;
	}
	/**
	 * Set error value
	 * @param anyType the error
	 * @return anyType
	 */
	public function setError($_error)
	{
		return ($this->error = $_error);
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