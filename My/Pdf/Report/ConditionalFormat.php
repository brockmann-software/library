<?php
class My_Pdf_Report_ConditionalFormat
{
	// how to compare the value
	protected $_operator;

	// on which to compare
	protected $_checkValue;
	
	// how to format the value
	protected $_formatPattern = null;
	
	// style of the cell
	protected $_style = null;
	
	// will be returnd in case condition is true
	protected $_conditionalValue;

	public function __construct($operator, $value, $formatPattern = null, $style = null, $conditionalValue = null)
	{
		if ($operator===null) throw new Zend_Pdf_Exception('No operator given');
		$this->_operator = $operator;
		
		if ($value===null) throw new Zend_Pdf_Exception('No value given');
		$this->_checkValue = $value;
		
		if ($formatPattern!==null) $this->_formatPattern = $formatPattern;
		
		if ($style!==null) $this->_style = new My_Pdf_Table_Column_Style($style);
		
		if ($conditionalValue!==null) $this->_conditionalValue = $conditionalValue;
	}
	
	private function checkCondition($value)
	{
		$result = false;
		switch ($this->_operator) {
			case My_Pdf_Report::SMALLER:			$result = ($value<$this->_checkValue);
													break;
			case My_Pdf_Report::SMALLER_EQUAL:		$result = ($value<=$this->_checkValue);
													break;
			case My_Pdf_Report::EQUAL:				$result = ($value=$this->_checkValue);
													break;
			case My_Pdf_Report::NOT_EQUAL:			$result = ($value!=$this->_checkValue);
													break;
			case My_Pdf_Report::BETWEEN:			$result = ($value>$this-_checkValue && $value<$this->_checkValue);
													break;
			case My_Pdf_Report::BETWEEN_INCL:		$result = ($value>=$this-_checkValue && $value<=$this->_checkValue);
													break;
			case My_Pdf_Report::GREATER_EQUAL:	$result = ($value>=$this->_checkValue);
													break;
			case My_Pdf_Report::GREATER:			$result = ($value>$this->_checkValue);
													break;
		}
		return $result;
	}
	
	public function setStyle($style)
	{
		if ($this->_style===null) $this->_style = new My_Pdf_Table_Column_Style($style);
		else $this->_style->setStyle($style);
	}
	
	public function getStyle()
	{
		return $this->_style;
	}
	
	public function formatCell($value)
	{
		$result = false;
		if ($this->checkCondition($value)) {
			$result['value'] = ($this->_conditionalValue!==null) ? $this->_conditionalValue : $value;
			if ($this->_formatPattern!==null) {
				switch ($this->_formatPattern[0]) {
					case 'date': $result['value'] = date($this->_formatPattern[1], strtotime($result['value']));
								break;
					case 'boolean' : $result['value'] = ($result['value']) ? $this->_formatPattern()[1] : $this->_formatPattern()[2];
								break;
				}
			}
			$result['style'] = $this->_style;
		}
		return $result;
	}
}