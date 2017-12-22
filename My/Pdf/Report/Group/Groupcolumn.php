<?php
// contailer class of the column to be grouped in report

class My_Pdf_Report_Group_Column extends My_Pdf_Report_Column
{
	//name of the column to group
	private $_column;
	
	//to print this column on header
	private $_onHeader;
	
	private My_Pdf_Report $_report;
	//to print this column on footer
	private $_onFooter;
	
	// style of this column on header
	private My_Pdf_Table_Column_Style $_headerStyle;

	// style of this column on footer
	private My_Pdf_Table_Column_Style $_footerStyle;
	
	private $_groupValue;
	
	
	public function __construct($column, $onHeader=false, $onFooter=true)
	{
		$this->_column = $column;
		$this->_onHeader = $_onHeader;
		$this->_onFooter = $_onFooter;
	}
	
	public function getOnHeader()
	{
		return $this->_onHeader;
	}
	
	public function getOnFooter()
	{
		return $this->_onFooter;
	}
	
	public function isEnd($dataset)
	{
		return (isset($dataset[$this->_column]) && $dataset[$this->_column]!=$_groupValue);
	}
	
	public function setHeaderStyle(My_Pdf_Table_Column_Style $style)
	{
		if ($this->_headerStyle===null) $this->_headerStyle = new My_Pdf_Table_Column_Style();
		$this->_headerStyle->setStyle($style);
	}
	
	public function getHeaderStyle()
	{
		return $this->_headerStyle->getStyle();
	}
	
	public function setFooterStyle(My_Pdf_Table_Column_Style $style)
	{
		if ($this->_headerStyle===null) $this->_headerStyle = new My_Pdf_Table_Column_Style();
		$this->_footerStyle->setStyle($style);
	}
	
	public function getFooterStyle()
	{
		return $this->_footerStyle->getStyle();
	}
	
	public function getColumnName()
	{
		return $this->_column;
	}
}