<?php
class My_Pdf_Table_Column_Style extends Zend_Pdf_Style
{
	// @var Zend_Pdf_Color
	private $_backgroundColor;
	
	// @var array (My_Pdf position=>Zend_Pdf_Style)
	private $_border = array();
	
	// @var int (spacing between text lines in px
	private $_textLineSpacing;
	
	// @var array of int (padding-top,padding-right,padding-bottom,padding-left)
	private $_padding = array();
	
	// @var int (My_Pdf::LEFT/CENTER/RIGHT)
	private $_textAlign;

	// @var int (My_Pdf::TOP//MIDDLE/BOTTOM)
	private $_verticalAlign;
	
	public function __construct($anotherstyle=null)
	{
		if ($anotherstyle instanceof My_Pdf_Table_Column_Style) {
			$this->_backgroundColor = $anotherstyle->_backgroundColor;
			$this->_border = $anotherstyle->_border;
			$this->_textLineSpacing = $anotherstyle->_textLineSpacing;
			$this->_padding = $anotherstyle->_padding;
			$this->_textAlign = $anotherstyle->_textAlign;
			$this->_verticalAlign = $anotherstyle->_verticalAlign;
		}
		if ($anotherstyle instanceof Zend_Pdf_Style) parent::__construct($anotherstyle);
	}
	
	public function setBackgroundColor($backgroundColor)
	{
		$this->_backgroundColor = $backgroundColor;
	}
	
	public function getBackgroundColor()
	{
		return $this->_backgroundColor;
	}
	
	public function setBorder($border)
	{
		$this->_border = $border;
	}
	
	public function getBorder()
	{
		return $this->_border;
	}
	
	public function setTextLineSpacing($textLineSpacing)
	{
		$this->_textLineSpacing = $textLineSpacing;
	}
	
	public function getTextLineSpacing()
	{
		return $this->_textLineSpacing;
	}
	
	public function setPadding($padding)
	{
		$this->_padding = $padding;
	}
	
	public function getPadding()
	{
		return $this->_padding;
	}
	
	public function setTextAlign($textAlign)
	{
		$this->_textAlign = $textAlign;
	}
	
	public function getTextAlign()
	{
		return $this->_textAlign;
	}
	
	public function setVerticalAlign($verticalAlign)
	{
		$this->_verticalAlign = $verticalAlign;
	}
	
	public function getVerticalAlign()
	{
		return $this->_verticalAlign;
	}
	
	public function setStyle($anotherstyle)
	{
		if ($anotherstyle instanceof My_Pdf_Table_Column_Style) {
			$this->_backgroundColor = $anotherstyle->_backgroundColor;
			$this->_border = $anotherstyle->_border;
			$this->_textLineSpacing = $anotherstyle->_textLineSpacing;
			$this->_padding = $anotherstyle->_padding;
			$this->_textAlign = $anotherstyle->_textAlign;
			$this->_verticalAlign = $anotherstyle->_verticalAlign;
		}
		if ($anotherstyle instanceof Zend_Pdf_Style) parent::setStyle($anotherstyle);
	}	
}