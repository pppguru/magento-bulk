<?php

class MDN_AdvancedStock_Model_Pdf_BarcodeLabels extends MDN_AdvancedStock_Model_Pdf_Pdfhelper
{
	protected $_labelWidth = 0;
	protected $_labelHeight = 0;
	
	protected $_labelNumber = 0;
	
	protected $_topMargin = 0;
	protected $_leftMargin = 0;
	protected $_rightMargin = 0;
	protected $_bottomMargin = 0;

	protected $_verticalSpacing = 0;
	protected $_horizontalSpacing = 0;

	protected $_labelsPerRow = 0;
	protected $_rowCount = 0;
	
	protected $_defaultLabelHeight = 120;

	protected $maxNumber = 0;//to avoid blanck page

	protected $_currentPage;
	
	/**
	 * Main method
	 * Todo : change input parameter to take an array of product_id => qty
	 *
	 * @param unknown_type $orders
	 * @return unknown
	 */
	public function getPdf($products = array())
    {
    	//init environment
        $this->_beforeGetPdf();
        $this->_initRenderer('invoice');
		
		//set width and height
		$widthCm = mage::getStoreConfig('advancedstock/barcode_labels/paper_width');
		$heightCm = mage::getStoreConfig('advancedstock/barcode_labels/paper_height');
        
		$this->_PAGE_WIDTH = $this->cmToPixels($widthCm);
		$this->_PAGE_HEIGHT = $this->cmToPixels($heightCm);

		$this->calculateWidthAndMargin($products);        
        
        //init pdf
        if ($this->pdf == null)
	        $this->pdf = new Zend_Pdf();
	    else 
	    	$this->firstPageIndex = count($this->pdf->pages);
        
	    
        //add new page
        $titre = mage::helper('purchase')->__('Barcode labels');
        $settings = array();
        $settings['title'] = $titre;
        $settings['store_id'] = 0;
        $this->_currentPage = $this->NewPage($settings);
	    	
	    //draw labels
	    foreach ($products as $productId => $qty)
	    {
	    	$product = mage::getModel('catalog/product')->load($productId);
            $this->_maxNumber += $qty;
	    	for($i=0;$i<$qty;$i++)
	    	{
	    		$this->printLabel($product);
	    	}
	    }
	    	
        return $this->pdf;
    }
    
    /**
     * Draw current label
     *
     * @param unknown_type $stockMovement
     */
    protected function printLabel($product)
    {
    	//init vars
    	$barcode = mage::helper('AdvancedStock/Product_Barcode')->getBarcodeForProduct($product);
    	
    	//draw outline
    	$rectX = $this->getLabelX();
    	$rectY = $this->getLabelY();
    	$this->_currentPage->drawRectangle($rectX, $rectY, $rectX + $this->_labelWidth , $rectY - $this->_labelHeight, Zend_Pdf_Page::SHAPE_DRAW_STROKE);
    	
    	//draw product name
        $this->defineFont($this->_currentPage,12);
        $productName = $this->TruncateTextToWidth($this->_currentPage, $product->getname(), $this->_labelWidth - 15);
    	$this->drawTextInBlock($this->_currentPage, $productName, $rectX + 5, $rectY - 15, $this->_labelWidth, 50, 'l');
    	
    	//draw sku
        $this->defineFont($this->_currentPage,10,self::FONT_MODE_ITALIC);
        $sku = $this->TruncateTextToWidth($this->_currentPage, $product->getSku(), $this->_labelWidth);
    	$this->drawTextInBlock($this->_currentPage, $sku, $rectX + 5, $rectY - 30, $this->_labelWidth, 50, 'l');
    	
    	//draw barcode
    	if ($barcode)
    	{
            try{
                $picture = mage::helper('AdvancedStock/Product_Barcode')->getBarcodePicture($barcode);
                if ($picture)
                {
                    $zendPicture = $this->pngToZendImage($picture);            
					$barcodeWidth = $rectX + $this->_labelWidth - 10;
        		    $barcodeHeight = $this->_labelHeight - 50;        
                    $this->_currentPage->drawImage($zendPicture, $rectX + 5, $rectY - $this->_labelHeight + 5, $rectX + $this->_labelWidth - 10, $rectY - 40);
                }
            }catch(Exception $ex){
                mage::logException($ex);
            }

    	}
        
    	if($this->_labelNumber < $this->_maxNumber){
            $this->prepareForNextLabel();
        }
        
    }
    
    /**
     * Prepare for next label
     *
     */
    protected function prepareForNextLabel()
    {
    	$this->_labelNumber++;
    	
    	//if we exceed page bottom, create new page
		if (mage::getStoreConfig('advancedstock/barcode_labels/paper_height') > 0)
		{
            $reste = (int)$this->getLabelY() - (int)$this->_labelHeight;

			if (($reste < 0) && ($this->_labelNumber < $this->_maxNumber)){
				$this->newPage();
            }
		}
    }
    
    /**
     * Return current label X
     *
     * @return unknown
     */
    protected function getLabelX()
    {
    	$col = ($this->_labelNumber % $this->_labelsPerRow);
    	$x = $this->_leftMargin + ($col * ($this->_labelWidth + $this->_verticalSpacing));
    	return $x;
    }
    
    /**
     * Return current label Y
     *
     * @return unknown
     */
    protected function getLabelY()
    {
    	$row = (int)($this->_labelNumber / $this->_labelsPerRow);
    	$y = ($this->_PAGE_HEIGHT - $this->_topMargin)- ($row) * ($this->_labelHeight + $this->_verticalSpacing);
    	return $y;
    }
    
    /**
     * Calculate labels width & height
     *
     */
    protected function calculateWidthAndMargin($products)
    {
    	$this->_topMargin = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/top_margin'));
    	$this->_leftMargin = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/left_margin'));
    	$this->_rightMargin = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/right_margin'));
    	$this->_bottomMargin = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/bottom_margin'));
    	
    	$this->_verticalSpacing = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/vertical_inter_margin'));
    	$this->_horizontalSpacing = $this->cmToPixels(mage::getStoreConfig('advancedstock/barcode_labels/horizontal_inter_margin'));
    	
    	$this->_labelsPerRow = mage::getStoreConfig('advancedstock/barcode_labels/labels_per_row');
    	$this->_rowCount = mage::getStoreConfig('advancedstock/barcode_labels/row_count');

		//Calculate labels count
		$labelCount = 0;
		foreach($products as $id => $qty)
			$labelCount += $qty;
		
		//if height is not set, calculate final height for all labels
		if ($this->_PAGE_HEIGHT == 0)
		{
			$this->_rowCount = ceil($labelCount / $this->_labelsPerRow);
			$this->_PAGE_HEIGHT = $this->_rowCount * ($this->_defaultLabelHeight + $this->_topMargin + $this->_bottomMargin + $this->_verticalSpacing);
		}
    	
    	$usableWidth = ($this->_PAGE_WIDTH - $this->_leftMargin - $this->_rightMargin);
    	$usableHeight = ($this->_PAGE_HEIGHT - $this->_topMargin - $this->_bottomMargin);
    	
    	$this->_labelWidth = ($usableWidth - (($this->_labelsPerRow - 1) * $this->_horizontalSpacing)) / $this->_labelsPerRow;
        if($this->_rowCount>0){
          $this->_labelHeight = $usableHeight / ($this->_rowCount) - $this->_verticalSpacing;
        }else{
          $this->_labelHeight = $usableHeight - $this->_verticalSpacing;
        }
                
		
    }
    
    /**
     * Add a new page
     *
     * @param array $settings
     * @return unknown
     */
	public function newPage(array $settings = array())
	{
	 	$page = $this->pdf->newPage($this->_PAGE_WIDTH.':'.$this->_PAGE_HEIGHT.':');
        $this->pdf->pages[] = $page;
		$this->_currentPage = $page;
        $this->_labelNumber = 0;
        
        //retourne la page
        return $this->_currentPage;
	}
	
	/**
	 * Convert cm to pixels
	 *
	 * @param unknown_type $value
	 * @return unknown
	 */
	public function cmToPixels($value)
	{
		return $value * 28.33;
	}
}
