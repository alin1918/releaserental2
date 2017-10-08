<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Observer;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Event\ObserverInterface;

class PrepareOptionsObserver implements ObserverInterface
{

    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $calendarHelper;
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @param \SalesIgniter\Rental\Helper\Data                $helperRental
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\App\RequestInterface         $request
     * @param \SalesIgniter\Rental\Helper\Calendar            $calendarHelper
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        ProductRepositoryInterface $productRepository,
        RequestInterface $request,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper
    ) {
        $this->_helperRental = $helperRental;
        $this->calendarHelper = $calendarHelper;
        $this->productRepository = $productRepository;
        $this->request = $request;
    }

    /**
     * Function used to complete the custokm options.
     * Because Start date and Endate are custom options not required
     * we hide them on product view/admin order create/etc but they
     * need to be reconstructed before adding to cart from the form data posted.
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /* @var $product \Magento\Catalog\Model\Product */
        $product = $this->productRepository->getById($observer->getEvent()->getProduct()->getId());
        $buyRequest = $observer->getEvent()->getBuyRequest();
        $transport = $observer->getEvent()->getTransport();
        /** @var array $calendarSelector */
        $calendarSelector = $buyRequest->getCalendarSelector();
        if ($this->_helperRental->isRentalType($product)) {
            if ($calendarSelector === null && $this->request->getParam('related_product')) {
                $calendarSelector = $this->request->getParam('calendar_selector');
            }
            $optionsArr = [];
            $optionsInit = $buyRequest->getOptions();
            foreach ($product->getOptions() as $option) {
                if (is_object($option)) {
                    if (isset($optionsInit[$option->getId()])) {
                        $optionsArr[$option->getId()] = $optionsInit[$option->getId()];
                    } else {
                        $optionsArr[$option->getId()] = '';
                    }
                }
            }
            $buyRequest->setOptions($optionsArr);
        }
        if ($calendarSelector !== null && $this->_helperRental->isRentalType($product)) {
            $options = $buyRequest->getOptions();

            $hasTimes = $this->calendarHelper->useTimes($product);
            if ($buyRequest->getCalendarUseTimes()) {
                $hasTimes = $buyRequest->getCalendarUseTimes() === '1';
            }
            try {
                /** @var \DateTime $startDate */
                $startDate = $this->calendarHelper->convertDateToUTC($calendarSelector['from'], $hasTimes, $calendarSelector['locale']);
                /** @var \DateTime $endDate */
                $endDate = $this->calendarHelper->convertDateToUTC($calendarSelector['to'], $hasTimes, $calendarSelector['locale']);
                if (!$hasTimes && $this->calendarHelper->getHotelMode($product) === 0) {
                    $endDate = $endDate->add(new \DateInterval('PT23H59M'));
                }
                /** @var array $optionsTemp */
                $optionsTemp = [];
                if (is_array($options) && ($calendarSelector !== null || $buyRequest->getIsBuyout())) {
                    foreach ($options as $optionId => $optionData) {
                        $option = $product->getOptionById($optionId);
                        if (is_object($option)) {
                            if (!$buyRequest->getIsBuyout()) {
                                if ($option->getTitle() == 'Start Date:') {
                                    $optionData = $this->setOptionData($startDate, $optionData);
                                }
                                if ($option->getTitle() == 'End Date:') {
                                    $optionData = $this->setOptionData($endDate, $optionData);
                                }
                            } else {
                                if ($option->getTitle() == 'Rental Buyout:') {
                                    $optionData = 'Rental Buyout';
                                }
                            }
                        }
                        if (isset($optionsInit[$optionId])) {
                            $optionsTemp[$optionId] = $optionData;
                        }
                    }
                    if (count($optionsTemp) > 0) {
                        $buyRequest->setOptions($optionsTemp);
                        foreach ($product->getOptions() as $option) {
                            /* @var $option \Magento\Catalog\Model\Product\Option */
                            $group = $option->groupFactory($option->getType())
                                ->setOption($option)
                                ->setProduct($product)
                                ->setRequest($buyRequest)
                                ->setProcessMode('full')
                                ->validateUserValue($buyRequest->getOptions());

                            $preparedValue = $group->prepareForCart();
                            if ($preparedValue !== null) {
                                $transport->options[$option->getId()] = $preparedValue;
                            }
                        }
                    }
                }
            } catch (Exception $e) {
            }
        }

        return $this;
    }

    /**
     * @param \DateTime $startDate
     * @param array     $optionData
     *
     * @return mixed
     */
    private function setOptionData($startDate, $optionData)
    {
        $optionData['day'] = $startDate->format('d');
        $optionData['month'] = $startDate->format('m');
        $optionData['year'] = $startDate->format('Y');
        $optionData['minute'] = $startDate->format('i');
        $optionData['date_internal'] = $startDate->format('Y-m-d H:i:s');
        if ($this->calendarHelper->timeTypeAmpm()) {
            $optionData['hour'] = $startDate->format('h');
            $optionData['day_part'] = $startDate->format('a');
            return $optionData;
        } else {
            $optionData['hour'] = $startDate->format('H');
            return $optionData;
        }
    }
}
