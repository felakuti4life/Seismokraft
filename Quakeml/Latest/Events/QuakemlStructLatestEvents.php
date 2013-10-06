<?php
/**
 * File for class QuakemlStructLatestEvents
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
/**
 * This class stands for QuakemlStructLatestEvents originally named latestEvents
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
class QuakemlStructLatestEvents extends QuakemlWsdlClass
{
	/**
	 * The complete
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var int
	 */
	public $complete;
	/**
	 * The num
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var int
	 */
	public $num;
	/**
	 * Constructor method for latestEvents
	 * @see parent::__construct()
	 * @param int $_complete
	 * @param int $_num
	 * @return QuakemlStructLatestEvents
	 */
	public function __construct($_complete = NULL,$_num = NULL)
	{
		parent::__construct(array('complete'=>$_complete,'num'=>$_num));
	}
	/**
	 * Get complete value
	 * @return int|null
	 */
	public function getComplete()
	{
		return $this->complete;
	}
	/**
	 * Set complete value
	 * @param int the complete
	 * @return int
	 */
	public function setComplete($_complete)
	{
		return ($this->complete = $_complete);
	}
	/**
	 * Get num value
	 * @return int|null
	 */
	public function getNum()
	{
		return $this->num;
	}
	/**
	 * Set num value
	 * @param int the num
	 * @return int
	 */
	public function setNum($_num)
	{
		return ($this->num = $_num);
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