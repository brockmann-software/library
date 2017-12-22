<?php
class My_Pdf_Report_Column
{
	private $_columnName;
	private $_columnLabel;
	private $_dataSourceColumn;
	private $_width;
	private $_formatPattern; // Formatting pattern of the column in 
	private $_headerStyle = null;
	private $_bodyStyle = null;

	// Array of formatting conditions to imfluence the format depending of the value; Will work from $key 0 to the end,
	private $_formatConditions = null;
	
	public function __construct($field, $label=null, $width=null, $pattern=null, $dsColumn='', $formatConditions = null)
	{
		$this->_columnName = $field;
		if ($label===null) $this->_columnLabel = $field; else $this->_columnLabel = $label;
		if ($width===null) $this->_width = null; else $this->setWidth($width);
		$this->_formatPattern = $pattern;
		$this->_dataSourceColumn = ($dsColumn=='') ? $field : $dsColumn;
		if ($formatConditions!==null) $this->_formatConditions = $formatConditions;
	}
	
	public function setColumnName($field)
	{
		$this->_columnName = $field;
	}
	
	public function getColumnName()
	{
		return $this->_columnName;
	}
	
	public function setColumnLabel($label)
	{
		$this->_columnLabel = $label;
	}
	
	public function getColumnLabel()
	{
		return $this->_columnLabel;
	}
	
	public function setDataSourceColumn($dsColumn)
	{
		$this->_dataSourceColumn = $dsColumn;
	}
	
	public function getDataSourceColumn()
	{
		return $this->_dataSourceColumn;
	}
	
	public function setWidth($value)
	{
		if (is_numeric($value)) $this->_width=$value;
		elseif (is_array($value)) {
			if (!is_numeric($value[0])) throw new Zend_Pdf_Exception('First param of width has to be numeric');
			switch (strtoupper($value[1])) {
				case 'CM': $this->_width = ($value[0]*72/2.54);
					break;
				case 'IN': $this->_width = ($value[0]*72);
					break;
				case 'MM': $this->_width = ($value[0]*72/25.40);
					break;
				case 'PX': $this->_width = $value[0];
					break;
				default: $this->_width = $value[0];
			}
		}
	}
	
	public function getWidth()
	{
		return $this->_width;
	}
		
	public function setHeaderStyle(My_Pdf_Table_Column_Style $style)
	{
		if ($this->_headerStyle===null) $this->_headerStyle = new My_Pdf_Table_Column_Style();
		$this->_headerStyle->setStyle($style);
	}
	
	public function getHeaderStyle()
	{
		return $this->_headerStyle;
	}
	
	public function setBodyStyle(My_Pdf_Table_Column_Style $style)
	{
		if ($this->_bodyStyle===null) $this->_bodyStyle = new My_Pdf_Table_Column_Style();
		$this->_bodyStyle->setStyle($style);
	}
	
	public function getBodyStyle()
	{
		return $this->_bodyStyle;
	}
	
	public function setHeaderBorder($border)
	{
		if (!is_Array($border)) throw new Zend_Exception('Param must be an array!');
		$this->_headerStyle->setBorder($borders);
	}
	
	public function getHeaderBorder()
	{
		return $this->_headerStyle->getBorder();
	}

	public function setBodyBorder($border)
	{
		if (!is_Array($border)) throw new Zend_Exception('Param must be an array!');
		$this->_bodyStyle->setBorder($border);
	}
	
	public function getBodyBorder()
	{
		return $this->_bodyStyle->getBorder();
	}
	
	public function setLabelAlign($orientation)
	{
		$this->_headerStyle->setTextAlign($orientation);
	}
	
	public function getLabelAlign()
	{
		return $this->_headerStyle->getTextAlign();
	}

	public function setColumnAlign($orientation)
	{
		$this->_bodyStyle->setTextAlign($orientation);
	}
	
	public function getColumnAlign()
	{
		return $this->_bodyStyle->getTextAlign();
	}
	
	public function getFormatPattern()
	{
		return $this->_formatPattern;
	}
	
	public function formatCell($dataArray)
	{
		$result = false;
		$condResult = false;
		if (isset($dataArray[$this->_dataSourceColumn])) {
			$value = $dataArray[$this->_dataSourceColumn];
			if (is_array($this->_formatConditions)) {
				foreach ($this->_formatConditions as $condition) {
					if ($result=$condition->formatCell($value)) $condResult = $result;
				}
				if ($condResult) $value = $condResult;
			}
			if (!is_array($value)) {
				$result['value'] = $value;
				if ($this->_formatPattern!==null) {
					switch ($this->_formatPattern[0]) {
						case 'date': $result['value'] = date($this->_formatPattern[1], strtotime($result['value']));
									break;
						case 'boolean' : $result['value'] = ($result['value']) ? $this->_formatPattern[1] : $this->_formatPattern[2];
									break;
					}
				}
				$result['style'] = $this->_bodyStyle;
			} else {
				$result = $value;
			}
		} elseif ($this->_dataSourceColumn=='@empty') {
			$result['value'] = '';
			$result['style'] = $this->_bodyStyle;
		}
		return $result;
	}
}