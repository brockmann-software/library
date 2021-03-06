<?php
class My_Pdf_Page extends Zend_Pdf_Page {
	
	/*
	 * If page contains pagebreaks, pages are stored here
	 */
	private $_pages=array();
	private $_margin;
	private $_defaultStyle;
	private $_charEncoding;

	/**
	 * Get Default Page Style
	 *
	 * @return Zend_Pdf_Style
	 */
	public function getDefaultStyle(){
		return $this->_defaultStyle;
	}
	
	/**
	 * Get all pages for this page (page overflows)
	 *
	 * @return array pages
	 */
	public function getPages(){
		if(count($this->_pages)>0){
			return array_merge(array($this),$this->_pages);
		}
		else{
			return false;
		}
	}
	
	/**
	 * Set page margins 
	 * 
	 * @param array(TOP,RIGHT,BOTTOM,LEFT)
	 */
	public function setMargins($margin=array()){
		foreach ($margin as $key => &$value) $value = $this->scaleToPixel($value);
		unset($value);
		$this->_margin=$margin;
	}
	
	/**
	 * Get Page Width
	 *
	 * @param bool $intContentArea
	 * @return int
	 */
	public function getWidth($intContentArea=false){
		$width=parent::getWidth();
		if($intContentArea){
			$width-=$this->_margin[My_Pdf::LEFT];
			$width-=$this->_margin[My_Pdf::RIGHT];
		}
		
		return $width;
	}
	
	/**
	 * Get a Page margin
	 *
	 * @param My_Pdf::Position $position
	 * @return int margin
	 */
	public function getMargin($position){
		return $this->_margin[$position];
	}
	
	/**
	 * Get Page Margins
	 *
	 * @return array(TOP,RIGHT,BOTTOM,LEFT)
	 */
	public function getMargins(){
		return $this->_margin;
	}
	
	/**
	 * Set Page Font
	 *
	 * @param Zend_Pdf_Resource_Font $font
	 * @param int $fontSize
	 */
	public function setFont(Zend_Pdf_Resource_Font $font, $fontSize=10){
		$this->_font=$font;
		$this->_fontSize=$fontSize;
		parent::setFont($font,$fontSize);
	}
	
	public function setCharEncoding($charEncoding)
	{
		$this->_charEncoding = $charEncoding;
	}
	
	public function __construct($param1, $param2 = null, $param3 = null, $param4 = null){
		if ($param4!==null) {
			if (is_string($param4)) $this->_charEncoding = $param4; else throw new Zend_Pdf_Exception('Wrong format for 4th param!');
		} 
		if ($param3!==null && is_string($param3)) {
			$this->_charEncoding = $param3;
			$param3 = null;
		} 
		if ($param2!==null && is_string($param2)) {
			$this->_charEncoding = $param2;
			$param2 = null;
		}
		parent::__construct ( $param1, $param2, $param3 );
		
		$style=new Zend_Pdf_Style();
		$style->setLineColor(new Zend_Pdf_Color_Html("#000000"));
		$style->setFillColor(new Zend_Pdf_Color_Html("#000000"));
		$style->setLineWidth(0.5);
		
		$font = Zend_Pdf_Font::fontWithName ( Zend_Pdf_Font::FONT_COURIER ); 		
		$style->setFont($font,10);
		
		$style->setLineDashingPattern(Zend_Pdf_Page::LINE_DASHING_SOLID);
		
		$this->_defaultStyle=$style;
		$this->setStyle($style);
		if ($param1 instanceof My_Pdf_Page) $this->_charEncoding = $param1->_charEncoding;
	}
	
	/**
	 * Add a table to a page
	 *
	 * @param My_Pdf_Table $table
	 * @param int $posX
	 * @param int $posY
	 */
	public function addTable(My_Pdf_Table $table,$posX,$posY,$inContentArea=true){
		//render table --> check for new pages
		$posX = $this->scaleToPixel($posX);
		$posY = $this->scaleToPixel($posY);
		$pages=$table->render($this,$posX,$posY,$inContentArea);
		if(is_array($pages))
			$this->_pages+=$pages;
	}
	
	/**
	 * Get text properties (width, height, [#lines using $max Width]), and warps lines
	 *
	 * @param string $text
	 * @param int $posX
	 * @param int $posY
	 * @param int $maxWidth
	 */
	public function getTextProperties($text, $maxWidth=null) {
		
		$lines=$this->_textLines($text, $maxWidth);
		
		return array (
			'text_width' => $lines['text_width'],
			'max_width'=> $lines['max_width'],
			'height'=>($this->getFontHeight()*count ( $lines['lines'] )), 
			'lines' => $lines['lines'] 
		);
	}
	
	/**
	 * Draw Line
	 *
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param bool $inContentArea
	 */
	public function drawLine($x1,$y1,$x2,$y2,$inContentArea=true){
		$y1 = $this->scaleToPixel($y1);
		$x1 = $this->scaleToPixel($x1);
		$y2 = $this->scaleToPixel($y2);
		$x2 = $this->scaleToPixel($x2);
		
		//move origin
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(My_Pdf::TOP);
			$y2 = $this->getHeight()- $y2  - $this->getMargin(My_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(My_Pdf::LEFT);
			$x2 = $x2  + $this->getMargin(My_Pdf::LEFT);
		}
		
		parent::drawLine($x1,$y1,$x2,$y2);
	}
	
	private function scaleToPixel($value)
	{
		$saclePixel = 0;
		if (is_numeric($value)) $saclePixel = $value;
		elseif (is_array($value)) {
			if (!is_numeric($value[0])) throw new Zend_Pdf_Exception('First param of height has to be numeric');
			switch (strtoupper($value[1])) {
				case 'CM': $saclePixel = ($value[0]*72/2.54);
					break;
				case 'IN': $saclePixel = ($value[0]*72);
					break;
				case 'MM': $saclePixel = ($value[0]*72/25.40);
					break;
				case 'PX': $saclePixel = $value[0];
				default: throw new Zend_Pdf_Exception('This scale unit is not supported!');
			}
		}
		return $saclePixel;
	}
	
	/**
	 * Draw Text
	 *
	 * @param string $text
	 * @param int $x1
	 * @param int $y1
	 * @param string $charEncoding
	 * @param bool $inContentArea
	 * @param int $alignment
	 */
	public function drawText($text,$x1,$y1,$charEncoding="",$inContentArea=true, $alignment=My_Pdf::LEFT){
		//move origin
		$y1 = $this->scaleToPixel($y1);
		$x1 = $this->scaleToPixel($x1);
		if ($charEncoding==='') $charEncoding = $this->_charEncoding;
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(My_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(My_Pdf::LEFT);
			$maxLength = $this->getWidth()-$this->getMargin(My_Pdf::RIGHT);
			$textProperties = $this->getTextProperties($text, $maxLength);
			$text = isset($textProperties['lines'][0]) ? $textProperties['lines'][0] : $text;
		}
		
		$textProperties = $this->getTextProperties($text);
		switch ($alignment) {
			case My_Pdf::RIGHT:	$x1 = $x1-$textProperties['max_width'];
				break;
			case My_Pdf::CENTER: $x1 = $x1-round($textProperties['max_width']/2);
		}
		parent::drawText($text,$x1,$y1,$charEncoding);
	}
	
	
	/**
	 * Draw Rectangle
	 *
	 * @param int $x1
	 * @param int $y1
	 * @param int $x2
	 * @param int $y2
	 * @param string $filltype
	 * @param bool $inContentArea
	 */
	public function drawRectangle($x1,$y1,$x2,$y2,$filltype=null,$inContentArea=true){
		$y1 = $this->scaleToPixel($y1);
		$x1 = $this->scaleToPixel($x1);
		$y2 = $this->scaleToPixel($y2);
		$x2 = $this->scaleToPixel($x2);
		//move origin
		if($inContentArea){
			$y1 = $this->getHeight()- $y1  - $this->getMargin(My_Pdf::TOP);
			$y2 = $this->getHeight()- $y2  - $this->getMargin(My_Pdf::TOP);
			$x1 = $x1  + $this->getMargin(My_Pdf::LEFT);
			$x2 = $x2  + $this->getMargin(My_Pdf::LEFT);
		}
		
		parent::drawRectangle($x1,$y1,$x2,$y2,$filltype);
	}
	
	public function drawImage(Zend_Pdf_Resource_Image $image,$x1,$y1,$width=null,$height=null,$inContentArea=true){
		$y1 = $this->scaleToPixel($y1);
		$x1 = $this->scaleToPixel($x1);
		if ($width!==null) $recommendedWidth = $this->scaleToPixel($width);
		else $recommendedWidth = $image->getPixelWidth();
		if ($height!==null) $recommendedHeight = $this->scaleToPixel($height);
		else $recommendedHeight = $image->getPixelHeight();
		if ($height!==null && $width===null) {
			$recommendedWidth=$this->scaleToPixel($height)/$image->getPixelHeight()*$image->getPixelWidth();
		} elseif ($width!==null && $height===null) {
			$recommendedHeight=$this->scaleToPixel($width)/$image->getPixelWidth()*$image->getPixelHeight();
		}
//		Zend_Registry::get('logger')->info("Bildhöhe: {$image->getPixelHeight()} Bildbreite: {$image->getPixelWidth()} Höhe: {$this->scaleToPixel($height)} Breite: {$this->scaleToPixel($width)} Neue Höhe: $recommendedHeight neue Breite: $recommendedWidth");
		if($inContentArea){
			
			$y1 = $this->getHeight()- $y1  - $this->getMargin(My_Pdf::TOP)-$recommendedHeight;
			$x1 = $x1  + $this->getMargin(My_Pdf::LEFT);
			
			$y2=$y1+$recommendedHeight;
			$x2=$x1+$recommendedWidth;
		}
		parent::drawImage($image,$x1,$y1,$x2,$y2);
	}
	
	/**
	 * Get Font Height
	 *
	 * @return int
	 */
	public function getFontHeight(){
		$line_height=$this->getFont()->getLineHeight();
		$line_gap=$this->getFont()->getLineGap();
		$em=$this->getFont()->getUnitsPerEm();
		$size=$this->getFontSize();
		return ($line_height-$line_gap)/$em*$size;
	}
	
		/**
	 * Returns the with of the text
	 *
	 * @param string $text
	 * @return int $width
	 */
	private function _getTextWidth($text)
	{
//		$drawing_text = iconv('', 'UTF-8', $text);
		$characters    = array();
		for ($i = 0; $i < strlen($text); $i++) {
			$characters[] = ord ($text[$i]);
		}
		$glyphs        = $this->_font->glyphNumbersForCharacters($characters);
		$widths        = $this->_font->widthsForGlyphs($glyphs);
		$text_width   = (array_sum($widths) / $this->_font->getUnitsPerEm()) * $this->_fontSize;
		return $text_width;
	}

	private function _oldTextWidth($text) {
		
		$glyphs = array ();
		$em = $this->_font->getUnitsPerEm ();
		
		//get glyph for each character
		foreach ( range ( 0, strlen ( $text ) - 1 ) as $i ) {
			$glyphs [] = @ord ( $text [$i] );
		}
		
		$width = array_sum ( $this->_font->widthsForGlyphs ( $glyphs ) ) / $em * $this->_fontSize;
		
		return $width;
	}
	
	/**
	 * Wrap text according to max width
	 *
	 * @param string $text
	 * @param int $maxWidth
	 * @return array lines
	 */
	private function _wrapText($text,$maxWidth){
		$x_inc = 0;
		$curr_line = '';
		$words = explode ( ' ', trim ( $text ) );
		$space_width = $this->_getTextWidth ( ' ' );
		foreach ( $words as $word ) {
			//no new line found
			$width = $this->_getTextWidth ( $word );
			
			if (isset ( $maxWidth ) && ($x_inc + $width) <= $maxWidth) {
				//add word to current line
				$curr_line .= ' '.$word;
				$x_inc += $width + $space_width;
			} else {
				//store current line
				if (strlen( trim($curr_line,"\n") )>0)
					$lines [] = trim($curr_line);

				//new line
				$x_inc = 0; //reset position
				$curr_line = array (); //reset curr line
				//add word
				$curr_line = $word;
				$x_inc += $width + $space_width;
			}
		}
		
		//last line
		if (strlen( trim($curr_line,"\n") )>0) {
			$lines [] = trim($curr_line);	
		}
		
		return $lines;
	}
	
	/**
	 * Enter description here...
	 *
	 * @param string $text
	 * @param int $maxWidth (optional, if not set (auto width) the max width is set by reference)
	 * @return array line(text);
	 */
	private function _textLines($text,$maxWidth=null){
		$trimmed_lines=array();
		$textWidth=0;
		$line_width=0;
		
		$lines=explode("\n",$text);
		$max_line_width=0;
		foreach ( $lines as $line ) {
			if(strlen($line)<=0) continue;
			$line_width=$this->_getTextWidth($line);
			if($maxWidth>0 && $line_width>$maxWidth){
				$new_lines=$this->_wrapText($line,$maxWidth);
				$trimmed_lines+=$new_lines;
				
				foreach ($new_lines as $nline) {
					$line_width=$this->_getTextWidth($nline);
					if($line_width>$max_line_width)
						$max_line_width=$line_width;
				}
			}
			else{
				$trimmed_lines[]=$line;
			}
			if($line_width>$max_line_width)
				$max_line_width=$line_width;
		}
		
		//set actual width of line
		if(is_null($maxWidth))
			$maxWidth=$max_line_width;
		
		$textWidth=$max_line_width;
		
		
		
		return array('lines'=>$trimmed_lines,'text_width'=>$textWidth,'max_width'=>$maxWidth);
	}
	
	
//	
//	private function getWordWidth($word) {
//		$font = $this->getFont ();
//		$font_size = $this->getFontSize ();
//		$em = $font->getUnitsPerEm ();
//		
//		$glyphs = array ();
//		//get glyph for each character
//		
//		foreach ( range ( 0, strlen ( $word ) - 1 ) as $i ) {
//			$glyphs [] = @ord ( $word [$i] );
//		}
//		
//		$width = array_sum ( $font->widthsForGlyphs ( $glyphs ) ) / $em * $font_size;
//		return $width;
//	}
//	
//	
//	
//	public function getFontHeightInPixel() {
//		$font = $this->getFont ();
//		$lineheight = ($font->getLineHeight ()) / $font->getUnitsPerEm () * $this->getFontSize ();
//		return $lineheight;
//	}
//	
//	public function getFontLineGapInPixel() {
//		$font = $this->getFont ();
//		$linegap = ($font->getLineGap ()) / $font->getUnitsPerEm () * $this->getFontSize ();
//		return $linegap;
//	}
//	
//	}
}

?>
