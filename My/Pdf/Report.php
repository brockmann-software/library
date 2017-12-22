<?php
class My_Pdf_Report extends My_Pdf_Document
{
	const SMALLER 		= 0;
	const SMALLER_EQUAL = 1;
	const EQUAL 		= 2;
	const BETWEEN		= 3;
	const BETWEEN_INCL	= 4;
	const GREATER_EQUAL	= 5;
	const GREATER		= 6;
	
	//array of assosiative array with column=>value paars;
	private $_rowset;
	
	//array of My_Pdf_Report_Column
	private $_columns;
	
	//Zend_Pdf_Page::SIZE
	private $_pageSize;

	//In case you would like to have the same style for the entire header, you can set it here;
	private $_headerStyle;
	
	//In case you would like to have the same style for th entire body, you can set it here;
	private $_bodyStyle;
	
	//array of My_Pdf_Report_Group
	private $_groups;

	
	public function __construct($filename, $path, array $dataset, $pageSize, $charEncoding){
		$this->_rowset = $dataset;
		$this->_pageSize = $pageSize;

		$borderStyle = new Zend_Pdf_Style();
		$borderStyle->setLineWidth(1);
		$borderStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
		$borderStyle->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
		
		$this->_headerStyle = new My_Pdf_Table_Column_Style();
		$this->_headerStyle->setLineColor(new Zend_Pdf_Color_HTML('black'));
		$this->_headerStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
		$this->_headerStyle->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD), 10);
		$this->_headerStyle->setLineWidth(1);
		$this->_headerStyle->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
		$this->_headerStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('black'));
		$this->_headerStyle->setBorder(array(My_Pdf::LEFT=>$borderStyle, My_Pdf::TOP=>$borderStyle, My_Pdf::RIGHT=>$borderStyle, My_Pdf::BOTTOM=>$borderStyle));
		
		$this->_bodyStyle = new My_Pdf_Table_Column_Style();
		$this->_bodyStyle->setLineColor(new Zend_Pdf_Color_HTML('black'));
		$this->_bodyStyle->setFillColor(new Zend_Pdf_Color_HTML('black'));
		$this->_bodyStyle->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_COURIER_BOLD), 10);
		$this->_bodyStyle->setLineWidth(1);
		$this->_bodyStyle->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
		$this->_bodyStyle->setBackgroundColor(new Zend_Pdf_Color_HTML('black'));
		$this->_bodyStyle->setBorder(array(My_Pdf::LEFT=>$borderStyle, My_Pdf::TOP=>$borderStyle, My_Pdf::RIGHT=>$borderStyle, My_Pdf::BOTTOM=>$borderStyle));

		parent::__construct($filename, $path);
		$this->setCharEncoding($charEncoding);
	}
	
	public function setColumns($columns)
	{
		$this->_columns = $columns;
	}
	
	public function addGroup($group)
	{
		$group->setReport($this);
		$this->_groups[] = $group;
	}
	
	public function setGroups(array $groups)
	{
		foreach ($groups as $group) $this->addGroup($group);
	}
	
	public function setHeaderStyle(Zend_Pdf_Style $style)
	{
		$this->_headerStyle = $style;
	}
	
	public function getHeaderStyle()
	{
		return $this->_headerStyle;
	}
	
	public function setBodyStyle(Zend_Pdf_Style $style)
	{
		$this->_bodyStyle = $style;
	}
	
	public function getBodyStyle()
	{
		return $this->_bodyStyle;
	}
	
	public function getDataSourceColumnByName($columnName)
	{
		$reult = null;
		if (is_array($this->_columns)) foreach ($this->_columns as $column) {
			if ($column->getColumnName()==$columnName) {
				$result = $column->getDataSourceColumn();
				break;
			}
		}
		return $result;
	}
	
	private function buildHeaderRow()
	{
		$columns = array();
		if (is_array($this->_columns)) {
			foreach ($this->_columns as $column) {
				if (array_key_exists($column->getColumnName(), $this->_rowset[0]) || ($column->getColumnName()=='@empty')) {
					$col = new My_Pdf_Table_Column();
					$col->setWidth($column->getWidth());
					$col->setText($column->getColumnLabel(), $column->getLabelAlign());
					($column->getHeaderStyle()!==null) ? $col->setStyle($column->getHeaderStyle()) : $col->setStyle($this->_headerStyle);
					$columns[] = $col;
				}
			}	
		} else {
			foreach ($this->_rowset[0] as $key => $val) {
				$col = new My_Pdf_Table_Column();
				$col->setText($key);
//				$col->setStyle($this->_headerStyle);
				$columns[] = $col;
			}
		}
		$headerRow = new My_Pdf_Table_Row();
		$headerRow->setColumns($columns);
		return $headerRow;
	}
	
	public function buildGroupHeaderRow($group, $dataArray)
	{
		$columns = array();
		if (is_array($this->_columns)) {
			$colSpan = 0;
			foreach ($this->_columns as $column) {
				if ($colSpan==0) {
					$col = new My_Pdf_Table_Column();
					$col->setWidth($column->getWidth());
					$groupColumn = $group->getColumnByName($column->getColumnName());
					if ($groupColumn!==null && $groupColumn->getOnHeader()) {
						$colSpan = $groupColumn->getHeaderColSpan();
						$col->setColspan($colSpan);
						$data = $this->formatText($column, $groupColumn->getResult());
					}else {
						$data = '';
					}
					$col->setText($data, $group->getColumnHeaderStyle($column->getColumnName())->getTextAlign());
					($group->getColumnHeaderStyle($column->getColumnName())!==null) ? $col->setStyle($group->getColumnHeaderStyle($column->getColumnName())) : $col->setStyle($this->_bodyStyle);
					$columns[] = $col;
				}
				if ($colSpan>0) $colSpan--;
			}
		} else {
			foreach ($dataArray as $key => $value) {
				$col = new My_Pdf_Table_Column();
				$groupColumn = $group->getColumn($key);
				$col->setText(($groupColumn!==null && $groupColumn->getOnHeader()) ? $groupColumn->getResult() : '');
				($group->getColumnHeaderStyle($column->getColumnName())!==null) ? $col->setStyle($group->getColumnHeaderStyle($column->getColumnName())) : $col->setStyle($this->_bodyStyle);
				$columns[] = $col;
			}
		}
		$headerRow = new My_Pdf_Table_Row();
		$headerRow->setColumns($columns);
		$headerRow->setPageBreak($group->getForceNewPage());
		return $headerRow;
	}
	
	public function buildGroupFooterRow($group, $dataArray)
	{
		$columns = array();
		if (is_array($this->_columns)) {
			$colSpan = 0;
			foreach ($this->_columns as $column) {
				if ($colSpan==0) {
					$col = new My_Pdf_Table_Column();
					$col->setWidth($column->getWidth());
					$groupColumn = $group->getColumnByName($column->getColumnName());
					if ($groupColumn!==null && $groupColumn->getOnFooter()) {
						$colSpan = $groupColumn->getHeaderColSpan();
						$data = $this->formatText($column, $groupColumn->getResult());
					}else {
						$data = '';
					}
					$col->setText($data, $group->getColumnFooterStyle($column->getColumnName())->getTextAlign());
					($group->getColumnFooterStyle($column->getColumnName())!==null) ? $col->setStyle($group->getColumnFooterStyle($column->getColumnName())) : $col->setStyle($this->_bodyStyle);
					$columns[] = $col;
				}
				if ($colSpan>0) $colSpan--;
			}
		} else {
			foreach ($dataArray as $key => $value) {
				$col = new My_Pdf_Table_Column();
				$col->setText($group->isHeaderColumn($column->getColumnName()) ? $val : '');
				($group->getColumnFooterStyle($column->getColumnName())!==null) ? $col->setStyle($group->getColumnFooterStyle($column->getColumnName())) : $col->setStyle($this->_bodyStyle);
				$columns[] = $col;
			}
		}
		$footerRow = new My_Pdf_Table_Row();
		$footerRow->setColumns($columns);
		return $footerRow;
	}
				
	
	private function buildBodyRow($dataArray)
	{
		$columns = array();
		if (is_array($this->_columns)) {
			foreach ($this->_columns as $column) {
				if ($data = $column->formatCell($dataArray)) {
					$col = new My_Pdf_Table_Column();
					$col->setWidth($column->getWidth());
					$col->setText($data['value'], $data['style']->getTextAlign());
					($data['style']!==null) ? $col->setStyle($data['style']) : $col->setStyle($this->_bodyStyle);
					$columns[] = $col;
				}
			}
		} else {
			foreach ($dataArray as $key => $val) {
				$col = new My_Pdf_Table_Column();
				$col->setText($val);
//				$col->setStyle($this->_bodyStyle);
				$columns[] = $col;
			}
		}
		$bodyRow = new My_Pdf_Table_Row();
		$bodyRow->setColumns($columns);
		return $bodyRow;
	}
	
	private function formatText($column, $data)
	{
		if (is_array($column->getFormatPattern())) {
			switch ($column->getFormatPattern()[0]) {
				case 'date': $data = date($column->getFormatPattern()[1], strtotime($data));
							break;
				case 'boolean' : $data = ($data) ? $column->getFormatPattern()[1] : $column->getFormatPattern()[2];
							break;
			}
		}
		return $data;
	}
	
	//@param array dataset, returns all groups which ended as array of groups
	private function getGroupsEnded($dataset)
	{
		$result = array();
		reset($this->_groups);
		foreach ($this->_groups as $group) {
			if ($group->isEnd($dataset)) $result[] = $group;
		}
		return $result;
	}
		
	private function buildTable()
	{
		$table = new My_Pdf_Table(2);
		$table->setHeader($this->buildHeaderRow());
		foreach ($this->_rowset as $row => $dataArray) {
			$groupsEnded = $this->getGroupsEnded($dataArray);
			if (count($groupsEnded)>0) {
				$groupsEndedReverse = array_reverse($groupsEnded, true);
				if ($row>0) foreach ($groupsEndedReverse as $key => $group)
					$table->addRow($this->buildGroupFooterRow($group, $dataArray));
				foreach ($groupsEnded as $group) {
					$group->resetColumns($dataArray);
					$table->addRow($this->buildGroupHeaderRow($group, $dataArray));
				}
			}
			foreach ($this->_groups as $group) $group->compute($dataArray);
			$table->addRow($this->buildBodyRow($dataArray));
		}
		$groupsEndedReverse = array_reverse($this->_groups);
		foreach ($groupsEndedReverse as $group)
			$table->addRow($this->buildGroupFooterRow($group, $dataArray));
		return $table;
	}
	
	public function save()
	{
		$page = $this->createPage($this->_pageSize);
		$page->addTable($this->buildTable(), 0, 0);
		$this->addPage($page);
		parent::save();
	}
	
	public function getColumn($columnName)
	{
		$result = null;
		foreach ($this->_columns as $column) {
			if ($column->getColumnName==$columnName) {
				$result = $column;
				break;
			}
		}
		return $result;
	}
}