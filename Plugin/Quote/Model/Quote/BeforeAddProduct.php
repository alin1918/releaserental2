<?php

namespace SalesIgniter\Rental\Plugin\Quote\Model\Quote;

class BeforeAddProduct
{
    protected $request;
    
    protected $objectFactory;
    
    protected $calendarHelper;
    
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper
    ) {
        $this->request          = $request;
        $this->objectFactory    = $objectFactory;        
        $this->calendarHelper   = $calendarHelper;        
    }
    
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $subject, 
        $product, 
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL)
    {
        $related = $this->request->getParam('related_product');
        if (is_null($request)
            && !empty($related)
            && $product->getTypeId() == \SalesIgniter\Rental\Model\Product\Type\Sirent::TYPE_RENTAL
        ) {
            $relatedArr         = explode(',', $related);
            $calendarSelector   = $this->request->getParam('calendar_selector');    
            
            if(in_array($product->getId(), $relatedArr)) {
                
                $options = [];
                foreach ($product->getOptions() as $option) {

                    $data;
                    if ($option->getType() ==  'date_time') {

                        $data =  [
                            'month'     => '',	
                            'day'       => '',	
                            'year'      => '',	
                            'hour'      => '',	
                            'minute'	=> '',	
                            'day_part'	=> 'am',	
                        ];
                    } else if ($option->getType() ==  'field') {
                        $data = '';
                    }

                    $options[$option->getOptionId()] = $data;
                }                    
                
                $request = $this->objectFactory->create([
                    'qty'               => 1,
                    'sirent_product_id' => $product->getId(),
                    'calendar_selector' => $this->request->getParam('calendar_selector'),
                    'options'           => $options,
                ]);                
            }            
        } 
        
        return [$product, $request, $processMode];
    }
}

