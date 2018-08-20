<?php

namespace SalesIgniter\Rental\Plugin\Helper\Calendar;

class StoreHours
{
    protected $request;

    protected $objectFactory;

    protected $calendarHelper;
    
    protected $productRepository;
    
    protected $registry;

    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Framework\DataObject\Factory $objectFactory,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Registry $registry
    ) {
        $this->request              = $request;
        $this->objectFactory        = $objectFactory;        
        $this->calendarHelper       = $calendarHelper;        
        $this->productRepository    = $productRepository;        
        $this->registry             = $registry;        
    }

    public function aroundGetNextDayHour(
        \SalesIgniter\Rental\Helper\Calendar $subject, 
        callable $proceed, 
        $productId 
    ) {
        
        $product = null;
        if ($productId instanceof \Magento\Catalog\Model\Product) {
            $product = $productId;
        } else if (is_numeric($productId)) {
            $product = $this->productRepository->getById($productId);
        }        
        
        if ($product
            && $product->getData('sirent_hour_next_day')
        ) {
            $data = $product->getData('sirent_hour_next_day');      
            $dataArray = explode(':', $data);
            return $dataArray;
        }        
        
        $return =  $proceed($product);
        
        return $return;
    }
    
    public function aroundStoreHoursStart(
        \SalesIgniter\Rental\Helper\Calendar $subject, 
        callable $proceed    
    ) {
        
        $product = $this->registry->registry('product');
        if ($product
            && $product->getData('sirent_store_open_time')
        ) {
           $data = $product->getData('sirent_store_open_time'); 
           $data = implode(',', explode(':', $data));
           return $data;
        }
        $return =  $proceed();
        
        return $return;        
    }

    public function aroundStoreHoursEnd(
        \SalesIgniter\Rental\Helper\Calendar $subject, 
        callable $proceed,
        $timeIncrement
    ) {
        $product = $this->registry->registry('product');
        if ($product
            && $product->getData('sirent_store_close_time')
        ) {
           $data = $product->getData('sirent_store_close_time'); 
           $data = implode(',', explode(':', $data));
           return $data;
        }
        $return =  $proceed($timeIncrement);
        
        return $return;                
    }    
    
    public function aroundStoreHoursPerDay(
        \SalesIgniter\Rental\Helper\Calendar $subject, 
        callable $proceed,
        $type, 
        $day, 
        $hoursStart, 
        $hoursEnd
    ) {
        
        if ($day == 'monday') {
            $test = '';
        }

        $product = $this->registry->registry('product');
        if ($product
            && $product->getData("sirent_store_{$type}_{$day}")
        ) {
           $data = $product->getData("sirent_store_{$type}_{$day}"); 
           $data = implode(',', explode(':', $data));
           return $data;
        }
        $return =  $proceed($type, $day, $hoursStart, $hoursEnd);
        
        return $return;                        
    }    
    
}

