<?php
class My_Pdf_Report_Group_Column
{
	//@var string: name of the column in report
	private $_columnName;
	
	//@var int type of the column (compute = 0 or group = 1)
	private $_type;

	//@var int: how to compute (My_Pdf_Report_Group::COMP_MIN, COMP_MAX, COMP_SUM, COMP_AVG, COMP_AVG_WHGT 
	private $_compute;

	//@var string: name of the column in report which is the source of dividor for COMP_AVG or COMP_AVG_WHGT
	private $_countColumn;
	
	//@var mixed: if type == compute it holds the current compute value, if type of group it holds the current group value
	private $_groupValue;
	
	//@var float: Dividor for COMP_AVG or COMP_AVG_WHGT
	private $_countValue;
	
	//@boolean: wether be printed oh header
	private $_onHeader=false;
	
	//@boolean: wether be printed oh footer
	private $_onFooter=false;
	
	//@style of this column on header
	private $_headerStyle;

	//style of this column on footer
	private $_footerStyle;
	
	//reference to the report
	private $_report;
	
	private $_headerColSpan = 1;
	
	private $_footerColSpan = 1;
	
	public function __construct($column, $onHeader=false, $onFooter=true, $type=My_Pdf_Report_Group::GROUP, $compute=My_Pdf_Report_Group::COMP_SUM, $countColumn='')
{
		if ($column=='') throw new Zend_Exception('No column given');
		$this->_columnName = $column;
		$this->_type = $type;
		$this->_onHeader = $onHeader;
		$this->_onFooter = $onFooter;
		$this->_compute = $compute;
		$this->_countColumn = $countColumn;
		$this->_groupValue = 0;
		$this->_countValue = 0;
		$_headerStyle = new My_Pdf_Table_Column_Style();
		$_footerStyle = new My_Pdf_Table_Column_Style();
	}
	
	public function getColumnName()
	{
		return $this->_columnName;
	}
	
	public function getOnFooter()
	{
		return $this->_onFooter;
	}
	
	public function getOnHeader()
	{
		return $this->_onHeader;
	}
	
	public function setHeaderColSpan($colSpan)
	{
		$this->_headerColSpan = $colSpan;
	}
	
	public function getHeaderColSpan()
	{
		return $this->_headerColSpan;
	}
	
	public function setFooterColSpan($colSpan)
	{
		$this->_footerColSpan = $colSpan;
	}
	
	public function getFooterColSoan()
	{ 
		return $this->_footerColSpan;
	}
	
	public function compute($dataset)
	{
		$result = true;
		$curValue = (isset($dataset[$this->_report->getDataSourceColumnByName($this->_columnName)])) ? $dataset[$this->_report->getDataSourceColumnByName($this->_columnName)] : 0;
//		echo "Type: {$this->_type} Operation: {$this->_compute} Compute {$this->_columnName}! CurValue: {$curValue} GroupValue: {$this->_groupValue}<br />";
		if ($this->_type == My_Pdf_Report_Group::COMPUTE) {
			$curCount = ($this->_compute==My_Pdf_Report_Group::COMP_AVG && isset($dataset[$this->_report->getDataSourceColumnByName($this->_countColumn)])) ? $dataset[$this->_report->getDataSourceColumnByName($this->_countColumn)] : 1;
			switch ($this->_compute) {
				case My_Pdf_Report_Group::COMP_SUM: $this->_groupValue = floatval($curValue) + floatval($this->_groupValue);
												break;
				case My_Pdf_Report_Group::COMP_MIN: if ($this->_groupValue>$curvalue) $this->_groupValue = floatval($curValue);
												break;
				case My_Pdf_Report_Group::COMP_MAX: if ($this->_groupValue>$curvalue) $this->_groupValue = floatval($curValue);
												break;
				default:							$this->_groupValue=floatval($this->_groupValue)+floatval($curValue);
													$this->_countValue=floatval($this->_countValue)+floatval($countValue);
												break;
			}
//		} elseif ($this->_type == My_Pdf_Report_Group::GROUP) {
//			$result = $this->isEnd($dataset);
//			$this->_groupValue = $curValue;
		}
//		echo "Compute {$this->_columnName}! CurValue: {$curValue} GroupValue: {$this->_groupValue}<br />";
		return $result;
	}
	
	public function resetColumn($dataset)
	{
		$this->_groupValue = ($this->_type==My_Pdf_Report_Group::COMPUTE) ? 0 : $dataset[$this->_report->getDataSourceColumnByName($this->_columnName)];
		$this->_countValue = 0;
//		echo "RESET {$this->_columnName} GroupValue: {$this->_groupValue}";
	}
	
	public function getResult()
	{
		if ($this->_compute==My_Pdf_Report_Group::COMP_AVG || $this->_compute==My_Pdf_Report_Group::COMP_AVG_WHGT) {
			if ($this->_countValue<>0) $result = $this->_groupValue / $this->_countValue; else $result = null;
		} else {
			$result = $this->_groupValue;
		}
		return $result;
	}
	
	public function fetchResult()
	{
		$result = $this->getResult();
		$this->resetColumn();
		return $result;
	}
	
	public function setReport($report)
	{
		$this->_report = $report;
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
	
	public function setFooterStyle(My_Pdf_Table_Column_Style $style)
	{
		if ($this->_footerStyle===null) $this->_footerStyle = new My_Pdf_Table_Column_Style();
		$this->_footerStyle->setStyle($style);
	}
	
	public function getFooterStyle()
	{
		return $this->_footerStyle;
	}
	
	public function isEqual($dataset)
	{
		$result = $this->_type==My_Pdf_Report_Group::COMPUTE || ($this->_type==My_Pdf_Report_Group::GROUP && $dataset[$this->_report->getDataSourceColumnByName($this->_columnName)] === $this->_groupValue);
		return $result;
	}
	
	public function getGroupValue()
	{
		$this->_groupValue;
	}
}