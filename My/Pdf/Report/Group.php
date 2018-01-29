<?php
/*
Class Group holds the information on which columns the report is grouped and what and how to compute the values;
This group is going to used on My_Report in order to group data of the report;
*/
class My_Pdf_Report_Group
{
	//constants to describe how to compute columns
	const COMP_SUM = 1;
	const COMP_COUNT = 2;
	const COMP_MIN = 3;
	const COMP_MAX = 4;
	const COMP_AVG = 5;
	const COMP_AVG_WHGT = 6;
	
	const GROUP = 1;
	const COMPUTE = 0;

	//array of My_Pdf_Report_Group_Column
	private $_groupColumns;

	//@var My_Pdf_Column_Style: style of the header cells
	private $_headerStyle = null;
	
	//@var My_Pdf_Column_Style: style of the footer columns
	private $_footerStyle = null;
	
	private $_forceNewPage = false;
	
	private $_emptyLinesBefore = 0;
	
	private $_emptyLinesAfter = 0;
	
	//@reference of the report
	private $_report;
	
	protected $countPrinted = 0;
	
	public function __construct($groupColumns, $headerStyle=null, $footerStyle=null)
	{
		$iniStyle = new My_Pdf_Table_Column_Style($headerStyle);
		$iniStyle->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER), 10);
		$iniStyle->setTextAlign(My_Pdf::LEFT);
		$iniStyle->setLineColor(new Zend_Pdf_Color_Html("#000000"));
		$iniStyle->setFillColor(new Zend_Pdf_Color_Html("#000000"));
		$iniStyle->setLineWidth(0.5);
		if (!is_array($groupColumns)) {
			new Zend_Exception('Group column list must be an array');
		} else {
			$this->_groupColumns = $groupColumns;
		}
		$this->_headerStyle = new My_Pdf_Table_Column_Style(($headerStyle!==null) ? $headerStyle : $iniStyle);
		$this->_footerStyle = new My_Pdf_Table_Column_Style(($footerStyle!==null) ? $footerStyle : $iniStyle);
		$countPrinted = 0;
	}
	
	//computes the row according to definition
	public function compute($dataset)
	{
		$result = true;
		foreach ($this->_groupColumns as $column) {
//			echo "{$column->getColumnName()} {$column->getGroupValue()}<br />";
			$result = ($column->compute($dataset) && $result);
		}
		return $result;
	}
	
	public function resetColumns($dataset)
	{
		foreach ($this->_groupColumns as $column) {
			$column->resetColumn($dataset);
		}
	}

	//checks if this dataset is not anymore in the group defined
	public function isEnd($dataset)
	{
		$result = true;
		reset($this->_groupColumns);
		$groupColumns_reverse = array_reverse($this->_groupColumns);
		foreach ($this->_groupColumns as $column) {
			$result = $column->isEqual($dataset) && $result;
		}
		if ($result==false) $this->countPrinted++;
		return !$result;
	}
	
	//sets the style for the entire header
	public function setHeaderStyle($style)
	{
		$this->_headerStyle->setStyle($style);
	}
	
	// sets the new Page Attribute. if true, this group will always be printed on a new page;
	
	public function setForceNewPage($newPage=true)
	{
		$this->_forceNewPage = $newPage;
	}
	
	// get the attribute forceNewPage
	public function getForceNewPage()
	{
	//	Zend_Registry::get('logger')->info("Neue Seite: {$this->_forceNewPage} Gedruckt: {$this->countPrinted}");
		return $this->_forceNewPage && ($this->countPrinted>1);
	}
	
	public function getEmptyLinesBefore()
	{
		return $this->_emptyLinesBefore;
	}
	
	public function getEmptyLinesAfter()
	{
		return $this->_emptyLinesAfter;
	}
	
	public function setEmptyLines($lines, $position)
	{
		if ($position==0) $this->_emptyLinesBefore = $lines;
		elseif ($position==1) $this->_emptyLinesAfter = $lines;
		else throw new Exception('position is not valid!');
	}
	
	//sets the style for the entire footer
	public function setFooterStyle($style)
	{
		$this->_footerStyle->setStyle($style);
	}

	// add a groupColumn to the list
	public function addGroupColumn(My_Pdf_Report_Group_Column $column)
	{
		if ($this->_report!==null) $column->setReport($this->_report);
		$this->_groupColumns[] = $column;
	}
	
	// sets the groupColumns. Overwrite columns already set
	public function setGroupColumns(array $columns)
	{
		$this->_groupColumns = $columns;
		if ($this->_report!==null) foreach ($this->_groupColumns as $column) $column->setReport($report);
	}
		
	// sets the report to wich the group belongs
	public function setReport(My_Pdf_Report $report)
	{
		$this->_report = $report;
		if (is_array($this->_groupColumns)) foreach ($this->_groupColumns as $column) $column->setReport($report);
	}
	
	//returns the groupColumn according to name else null
	public function getColumnByName($columnName)
	{
		$result = null;
		foreach ($this->_groupColumns as $column) {
			if ($column->getColumnName() == $columnName) {
				$result = $column;
				break;
			}
		}
		return $result;
	}
	
	//retuns true if columnname is groupColumn else false
	public function isGroupHeaderColumn($columnName)
	{
		return ($this->getColumnByName($columnName)!==null && $this->getColumnByName($columnName)->getOnHeader());
	}
	
	//returns style of column is exists else style defined for the group
	public function getColumnHeaderStyle($columnName)
	{
		if ($this->getColumnByName($columnName)!==null) {
			return ($this->getColumnByName($columnName)->getHeaderStyle()!==null) ? $this->getColumnByName($columnName)->getHeaderStyle() : $this->_headerStyle;
		} else {
			return $this->_headerStyle;
		}
	}
	//returns style of column is exists else style defined for the group
	public function getColumnFooterStyle($columnName)
	{
		if ($this->getColumnByName($columnName)!==null) {
			return ($this->getColumnByName($columnName)->getFooterStyle()!==null) ? $this->getColumnByName($columnName)->getFooterStyle() : $this->_footerStyle;
		} else {
			return $this->_footerStyle;
		}
	}
}