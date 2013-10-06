<?php
/**
 * File for class QuakemlStructEvents
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
/**
 * This class stands for QuakemlStructEvents originally named events
 * @package Quakeml
 * @subpackage Structs
 * @author Mikaël DELSOL <contact@wsdltophp.com>
 * @date 2013-03-04
 */
class QuakemlStructEvents extends QuakemlWsdlClass
{
	/**
	 * The uri
	 * Meta informations extracted from the WSDL
	 * - nillable : true
	 * @var string
	 */
	public $uri;
	/**
	 * The dateMin
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var dateTime
	 */
	public $dateMin;
	/**
	 * The dateMax
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var dateTime
	 */
	public $dateMax;
	/**
	 * The latMin
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $latMin;
	/**
	 * The latMax
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $latMax;
	/**
	 * The lonMin
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $lonMin;
	/**
	 * The lonMax
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $lonMax;
	/**
	 * The depthMin
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $depthMin;
	/**
	 * The depthMax
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $depthMax;
	/**
	 * The netMagMin
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $netMagMin;
	/**
	 * The netMagMax
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var float
	 */
	public $netMagMax;
	/**
	 * The netMagType
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var string
	 */
	public $netMagType;
	/**
	 * The author
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var string
	 */
	public $author;
	/**
	 * The startRow
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var int
	 */
	public $startRow;
	/**
	 * The limit
	 * Meta informations extracted from the WSDL
	 * - minOccurs : 0
	 * - nillable : true
	 * @var string
	 */
	public $limit;
	/**
	 * Constructor method for events
	 * @see parent::__construct()
	 * @param string $_uri
	 * @param dateTime $_dateMin
	 * @param dateTime $_dateMax
	 * @param float $_latMin
	 * @param float $_latMax
	 * @param float $_lonMin
	 * @param float $_lonMax
	 * @param float $_depthMin
	 * @param float $_depthMax
	 * @param float $_netMagMin
	 * @param float $_netMagMax
	 * @param string $_netMagType
	 * @param string $_author
	 * @param int $_startRow
	 * @param string $_limit
	 * @return QuakemlStructEvents
	 */
	public function __construct($_uri = NULL,$_dateMin = NULL,$_dateMax = NULL,$_latMin = NULL,$_latMax = NULL,$_lonMin = NULL,$_lonMax = NULL,$_depthMin = NULL,$_depthMax = NULL,$_netMagMin = NULL,$_netMagMax = NULL,$_netMagType = NULL,$_author = NULL,$_startRow = NULL,$_limit = NULL)
	{
		parent::__construct(array('uri'=>$_uri,'dateMin'=>$_dateMin,'dateMax'=>$_dateMax,'latMin'=>$_latMin,'latMax'=>$_latMax,'lonMin'=>$_lonMin,'lonMax'=>$_lonMax,'depthMin'=>$_depthMin,'depthMax'=>$_depthMax,'netMagMin'=>$_netMagMin,'netMagMax'=>$_netMagMax,'netMagType'=>$_netMagType,'author'=>$_author,'startRow'=>$_startRow,'limit'=>$_limit));
	}
	/**
	 * Get uri value
	 * @return string|null
	 */
	public function getUri()
	{
		return $this->uri;
	}
	/**
	 * Set uri value
	 * @param string the uri
	 * @return string
	 */
	public function setUri($_uri)
	{
		return ($this->uri = $_uri);
	}
	/**
	 * Get dateMin value
	 * @return dateTime|null
	 */
	public function getDateMin()
	{
		return $this->dateMin;
	}
	/**
	 * Set dateMin value
	 * @param dateTime the dateMin
	 * @return dateTime
	 */
	public function setDateMin($_dateMin)
	{
		return ($this->dateMin = $_dateMin);
	}
	/**
	 * Get dateMax value
	 * @return dateTime|null
	 */
	public function getDateMax()
	{
		return $this->dateMax;
	}
	/**
	 * Set dateMax value
	 * @param dateTime the dateMax
	 * @return dateTime
	 */
	public function setDateMax($_dateMax)
	{
		return ($this->dateMax = $_dateMax);
	}
	/**
	 * Get latMin value
	 * @return float|null
	 */
	public function getLatMin()
	{
		return $this->latMin;
	}
	/**
	 * Set latMin value
	 * @param float the latMin
	 * @return float
	 */
	public function setLatMin($_latMin)
	{
		return ($this->latMin = $_latMin);
	}
	/**
	 * Get latMax value
	 * @return float|null
	 */
	public function getLatMax()
	{
		return $this->latMax;
	}
	/**
	 * Set latMax value
	 * @param float the latMax
	 * @return float
	 */
	public function setLatMax($_latMax)
	{
		return ($this->latMax = $_latMax);
	}
	/**
	 * Get lonMin value
	 * @return float|null
	 */
	public function getLonMin()
	{
		return $this->lonMin;
	}
	/**
	 * Set lonMin value
	 * @param float the lonMin
	 * @return float
	 */
	public function setLonMin($_lonMin)
	{
		return ($this->lonMin = $_lonMin);
	}
	/**
	 * Get lonMax value
	 * @return float|null
	 */
	public function getLonMax()
	{
		return $this->lonMax;
	}
	/**
	 * Set lonMax value
	 * @param float the lonMax
	 * @return float
	 */
	public function setLonMax($_lonMax)
	{
		return ($this->lonMax = $_lonMax);
	}
	/**
	 * Get depthMin value
	 * @return float|null
	 */
	public function getDepthMin()
	{
		return $this->depthMin;
	}
	/**
	 * Set depthMin value
	 * @param float the depthMin
	 * @return float
	 */
	public function setDepthMin($_depthMin)
	{
		return ($this->depthMin = $_depthMin);
	}
	/**
	 * Get depthMax value
	 * @return float|null
	 */
	public function getDepthMax()
	{
		return $this->depthMax;
	}
	/**
	 * Set depthMax value
	 * @param float the depthMax
	 * @return float
	 */
	public function setDepthMax($_depthMax)
	{
		return ($this->depthMax = $_depthMax);
	}
	/**
	 * Get netMagMin value
	 * @return float|null
	 */
	public function getNetMagMin()
	{
		return $this->netMagMin;
	}
	/**
	 * Set netMagMin value
	 * @param float the netMagMin
	 * @return float
	 */
	public function setNetMagMin($_netMagMin)
	{
		return ($this->netMagMin = $_netMagMin);
	}
	/**
	 * Get netMagMax value
	 * @return float|null
	 */
	public function getNetMagMax()
	{
		return $this->netMagMax;
	}
	/**
	 * Set netMagMax value
	 * @param float the netMagMax
	 * @return float
	 */
	public function setNetMagMax($_netMagMax)
	{
		return ($this->netMagMax = $_netMagMax);
	}
	/**
	 * Get netMagType value
	 * @return string|null
	 */
	public function getNetMagType()
	{
		return $this->netMagType;
	}
	/**
	 * Set netMagType value
	 * @param string the netMagType
	 * @return string
	 */
	public function setNetMagType($_netMagType)
	{
		return ($this->netMagType = $_netMagType);
	}
	/**
	 * Get author value
	 * @return string|null
	 */
	public function getAuthor()
	{
		return $this->author;
	}
	/**
	 * Set author value
	 * @param string the author
	 * @return string
	 */
	public function setAuthor($_author)
	{
		return ($this->author = $_author);
	}
	/**
	 * Get startRow value
	 * @return int|null
	 */
	public function getStartRow()
	{
		return $this->startRow;
	}
	/**
	 * Set startRow value
	 * @param int the startRow
	 * @return int
	 */
	public function setStartRow($_startRow)
	{
		return ($this->startRow = $_startRow);
	}
	/**
	 * Get limit value
	 * @return string|null
	 */
	public function getLimit()
	{
		return $this->limit;
	}
	/**
	 * Set limit value
	 * @param string the limit
	 * @return string
	 */
	public function setLimit($_limit)
	{
		return ($this->limit = $_limit);
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