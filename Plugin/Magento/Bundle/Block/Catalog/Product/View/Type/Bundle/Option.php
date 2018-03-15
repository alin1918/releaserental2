<?php

namespace SalesIgniter\Rental\Plugin\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle;


class Option {
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	private $helperRental;
	/**
	 * @var \Magento\Framework\Registry
	 */
	private $registry;
	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	private $helperCalendar;
	/**
	 * @var \SalesIgniter\Rental\Model\Product\PriceCalculations
	 */
	private $priceCalculations;

	/**
	 * @param \SalesIgniter\Rental\Helper\Data                     $helperRental
	 * @param \SalesIgniter\Rental\Helper\Calendar                 $helperCalendar
	 * @param \SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations
	 * @param \Magento\Framework\Registry                          $registry
	 */
	public function __construct(
		\SalesIgniter\Rental\Helper\Data $helperRental,
		\SalesIgniter\Rental\Helper\Calendar $helperCalendar,
		\SalesIgniter\Rental\Model\Product\PriceCalculations $priceCalculations,
		\Magento\Framework\Registry $registry

	) {
		$this->helperRental      = $helperRental;
		$this->registry          = $registry;
		$this->helperCalendar    = $helperCalendar;
		$this->priceCalculations = $priceCalculations;
	}

	//function beforeMETHOD($subject, $arg1, $arg2){}
	//function aroundMETHOD($subject, $procede, $arg1, $arg2){return $proceed($arg1, $arg2);}
	//function afterMETHOD($subject, $result){return $result;}
	/**
	 * @param \Magento\Bundle\Helper\Catalog\Product\Configuration $subject
	 * @param \Closure                                             $proceed
	 * @param ItemInterface                                        $item
	 * @param \Magento\Catalog\Model\Product                       $selectionProduct
	 *
	 * @return float
	 *
	 * @throws \LogicException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \RuntimeException
	 */
	public function aroundGetSelectionQtyTitlePrice(
		\Magento\Bundle\Block\Catalog\Product\View\Type\Bundle\Option $subject,
		\Closure $proceed,
		$selection,
		$includeContainer = true
	) {

		$returnValue = $proceed( $selection, $includeContainer );
		if ( $this->helperRental->isRentalType( $selection ) ) {
			$returnValue = '<div class="rental_bundle_option">' . $returnValue . '</div>';

		}

		return $returnValue;
	}
}
