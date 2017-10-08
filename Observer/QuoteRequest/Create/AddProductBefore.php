<?php
/**
 * Copyright © 2015 SalesIgniter. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Observer\QuoteRequest\Create;

class AddProductBefore
	implements \Magento\Framework\Event\ObserverInterface
{

	public function execute(\Magento\Framework\Event\Observer $Observer)
	{
		$PostedData = $Observer->getPostedData();
		$BuyRequest = $Observer->getBuyRequest();

		if (isset($PostedData['sirent_product_id'])){
			$BuyRequest->setSirentProductId($PostedData['sirent_product_id']);
		}

		if (isset($PostedData['calendar_selector'])){
			$BuyRequest->setCalendarSelector($PostedData['calendar_selector']);
		}

		return $this;
	}
}
