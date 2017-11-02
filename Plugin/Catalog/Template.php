<?php

namespace SalesIgniter\Rental\Plugin\Catalog;

use Magento\Framework\View\Page\Config\Reader\Html;
use SalesIgniter\Rental\Model\Config\GlobalDatesPricingOnListing;

/**
 * Class Template.
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 */
class Template
{
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $_helperRental;
    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    private $layout;

    /**
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    private $helperCalendar;

    /**
     * @param \SalesIgniter\Rental\Helper\Data        $helperRental
     * @param \SalesIgniter\Rental\Helper\Calendar    $helperCalendar
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\Registry             $coreRegistry
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental,
        \SalesIgniter\Rental\Helper\Calendar $helperCalendar,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\Registry $coreRegistry
    ) {
        $this->_helperRental = $helperRental;
        $this->layout = $layout;
        $this->coreRegistry = $coreRegistry;
        $this->helperCalendar = $helperCalendar;
    }

    private function removeHtmlTags($html)
    {
        //$html = preg_replace("/<html[^>]+\>/i", '', $html);
        $html = str_replace('<html>', '', $html);
        $html = str_replace('</html>', '', $html);
        // $html = str_replace('<!DOCTYPE html>', '', $html);
        // $html = str_replace('<br></br>', '<br />', $html);
        return $html;
    }

    /**
     * @param $domHtml
     * @param $isChanged
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     *
     * @internal param string $dom
     */
    private function _renameButtons(&$domHtml, &$isChanged)
    {
        $this->renameButtonsOnProductPage($domHtml, $isChanged);
        $this->renameButtonsOnListing($domHtml, $isChanged);
    }

    /**
     * Function which add the pricing blocks and styles.
     *
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _addPricingJs(&$dom)
    {
        if ($this->helperCalendar->globalDatesPricingOnListing() !== GlobalDatesPricingOnListing::NORMAL) {
            /** @var \SalesIgniter\Rental\Block\Footer\Pricingppr $block */
            $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Footer\Pricingppr');
            $dom->append($this->cleanHtml($block->toHtml()));
        }
    }

    /**
     * Function which gets the calendar widget instance.
     *
     * @param string $area
     *
     * @return string
     */
    public function getCalendar($area = \Magento\Framework\App\Area::AREA_FRONTEND)
    {
        /***
         * Because of Magento caching mechanism is impossible to create a widget with dynamic data without using ajax
         * even the fact that a block is non-cacheable means is loaded by ajax. So the only solution is to load
         * the block by ajax, I think is better we do it then let magento because I've seen some strange behaviour.
         */

        /** @var \SalesIgniter\Rental\Block\Widget\CalendarWidget $block */
        $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Widget\CalendarWidget');
        $block->setArea($area);
        $block->setTemplate('SalesIgniter_Rental::widgets/calendar.phtml');

        return $block->toHtml();
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     * @param                     $isChanged
     *
     * @return string
     *
     * @throws \InvalidArgumentException
     */
    private function _hideStartEndCustomOptions(&$dom, &$isChanged)
    {
        $hasStartEndDate = false;
        $nodes = $dom->find('.field');
        foreach ($nodes as $node) {
            $legendText = '';
            $nodeLabels = $node->find('.label, .legend');
            foreach ($nodeLabels as $nodeLabel) {
                $legendText = $nodeLabel->text();
                break;
            }
            if (strpos($legendText, 'End Date:') !== false || strpos($legendText, 'Start Date:') !== false) {
                $node->addClass('hiddenDates');
                $node->attr('style', 'display:none');
                $hasStartEndDate = true;
                $isChanged = true;
            }
            if (strpos($legendText, 'Rental Buyout:') !== false || strpos($legendText, 'Damage Waiver:') !== false) {
                $node->addClass('hiddenDates');
                $node->attr('style', 'display:none');
                $isChanged = true;
            }
        }

        $isBundle = $dom->find('.fieldset-bundle-options')->first();
        if ($hasStartEndDate && $isBundle->length === 0) {
            $html = $this->cleanHtml($this->getCalendar());
            $dom->find('.date')->first()->parent()->append($html);
            $isChanged = true;
        }
        if (!$hasStartEndDate && $isBundle->length === 0) {
            $bundleFields = $dom->find('.bundle-info')->first();
            if (is_object($bundleFields)) {
                $html = $this->cleanHtml($this->getCalendar());
                $bundleFields->append($html);
                $isChanged = true;
            }
        }
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     * @param                     $isChanged
     *
     * @return string
     */
    private function _hideStartEndCustomOptionsAdmin(&$dom, &$isChanged)
    {
        $nodes = $dom->find('.field');
        foreach ($nodes as $node) {
            $legendText = '';
            $nodeLabels = $node->find('.label');
            foreach ($nodeLabels as $nodeLabel) {
                $legendText = $nodeLabel->text();
                break;
            }

            if (strpos($legendText, 'End Date:') !== false || strpos($legendText, 'Start Date:') !== false || strpos($legendText, 'Rental Buyout:') !== false || strpos($legendText, 'Damage Waiver:') !== false) {
                $node->addClass('hiddenDates');
                $isChanged = true;
            }
        }
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _appendCalendarAdmin(&$dom)
    {
        $html = $this->cleanHtml($this->getCalendar(\Magento\Framework\App\Area::AREA_ADMINHTML));
        $dom->prepend($html);
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _appendAdminCreateOrderUpdate(&$dom)
    {
        $html = '<script>
            require(["sirentcreateorder"], function(){
                
            });
            </script>';
        $html = $this->cleanHtml($html);
        $dom->append($html);
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _appendFrontendGeneralStyles(&$dom)
    {
        $html = '<script>
            require(["css!css/general/styles.min"], function(){
                
            });
            </script>';
        $html = $this->cleanHtml($html);
        $dom->append($html);
    }

    /**
     * Function to hide custom options.
     *
     * @param $subject
     * @param $domHtml
     * @param $isChanged
     *
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function hideCustomOptions($subject, &$domHtml, &$isChanged)
    {
        if (($subject->getNameInLayout() === 'product.info.options.wrapper' &&
                $this->_helperRental->isFrontend() &&
                $this->_helperRental->isRentalType($this->coreRegistry->registry('current_product'))) ||
            ($subject->getNameInLayout() === 'bundle.summary' &&
                $this->_helperRental->isFrontend() &&
                $this->_helperRental->isRentalType($this->coreRegistry->registry('current_product')))
        ) {
            $this->_hideStartEndCustomOptions($domHtml, $isChanged);
        }
        if ($this->_helperRental->isBackend() && strpos($subject->getNameInLayout(), 'product.composite.fieldset') !== false) {
            $this->_hideStartEndCustomOptionsAdmin($domHtml, $isChanged);
        }
    }

    /**
     * Function to add pricing and stylesheets.
     *
     * @param $subject
     * @param $domHtml
     * @param $isChanged
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addPricingAndStylesheets($subject, &$domHtml, &$isChanged)
    {
        if ($subject->getNameInLayout() === 'absolute_footer' && $this->_helperRental->isFrontend()) {
            $this->_addPricingJs($domHtml);
            $this->_appendFrontendGeneralStyles($domHtml);
            $isChanged = true;
        }
    }

    private function cleanHtml($html)
    {
        $htmlCleaned = html5qp('<div class="si_generated_div">'.$html.'</div>');

        return $htmlCleaned->find('div.si_generated_div')->first()->innerHTML();
    }

    /**
     * Function to take care of removing Dates in emails and add the dates block.
     *
     * @param $subject
     * @param $fileName
     * @param $domHtml
     * @param $isChanged
     *
     * @return mixed|string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     */
    private function addDates($subject, $fileName, &$domHtml, &$isChanged)
    {
        if ($subject->getNameInLayout() === 'items' && strpos($fileName, 'email') !== false) {
            $this->_addDatesInfoEmail($domHtml, $subject->getOrder());
            $this->_removeStartEndDatesPerItem($domHtml, $isChanged);
        }

        if ($subject->getNameInLayout() === 'order_info' && $this->_helperRental->isBackend()) {
            $this->_addDatesInfo($domHtml);
            $isChanged = true;
        }

        if ($this->_helperRental->isBackend() &&
            $this->helperCalendar->isSameDayOrder() &&
            ($subject->getNameInLayout() === 'order_items' ||
                $subject->getNameInLayout() === 'shipment_items' ||
                $subject->getNameInLayout() === 'creditmemo_items')
        ) {
            $this->_removeStartEndDatesPerItem($domHtml, $isChanged);
        }
    }

    /**
     * Retrieve block view from file (template).
     *
     * @param \Magento\Framework\View\Element\Template $subject
     * @param \Closure                                 $proceed
     * @param string                                   $fileName
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundFetchView(
        \Magento\Framework\View\Element\Template $subject,
        \Closure $proceed,
        $fileName
    ) {
        $html = $proceed($fileName);
        if ($this->_helperRental->isPaymentResponse()) {
            return $html;
        }
        $domHtmlModified = html5qp('<div class="si_generated_local">'.$html.'</div>');
        $isChanged = false;
        $domHtml = $domHtmlModified->find('div.si_generated_local')->first();
        $this->renameButtonsOnListingAndProductPage($fileName, $domHtml, $isChanged);

        $this->hideCustomOptions($subject, $domHtml, $isChanged);
        $this->addPricingAndStylesheets($subject, $domHtml, $isChanged);

        $this->addCalendarAdmin($subject, $domHtml, $isChanged);
        $this->addDates($subject, $fileName, $domHtml, $isChanged);

        $this->orderViewUpdate($subject, $domHtml, $isChanged);
        $this->modifyShipPage($subject, $domHtml, $isChanged);

        //todo for shipment view show serials. check regex https://simple-regex.com/build/580477fa34714
        if ($isChanged) {
            return $domHtml->innerHTML();
        } else {
            return $html;
        }
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     * @param                     $isChanged
     *
     * @return mixed
     *
     * @throws \RuntimeException
     */
    private function _removeStartEndDatesPerItem($dom, &$isChanged)
    {
        $dateField = $dom->find('dl')->first();
        if (is_object($dateField) && $dateField->hasClass('item-options')) {
            $dateFields = $dateField->find('dt');

            /** @var \QueryPath\DOMQuery $dateField */
            foreach ($dateFields as $dateField) {
                $legendText = $dateField->text();
                if ($legendText == 'End Date::' || $legendText == 'Start Date::' || $legendText == 'End Date:' || $legendText == 'Start Date:') {
                    $dateField->next()->remove();
                    $dateField->remove();
                    $isChanged = true;
                }
            }
        }
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     *
     * @return string
     */
    private function _addDatesInfo($dom)
    {
        if ($this->helperCalendar->isSameDayOrder()) {
            /** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info $block */
            $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info');
            $html = $this->cleanHtml($block->toHtml());
            $dom->append($html);
        }
    }

    /**
     * @param $dom
     *
     * @return string
     */
    private function _addReturnGridPanel($dom)
    {

        /** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel $block */
        $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel');
        $html = $this->cleanHtml($block->toHtml());
        $dom->prepend($html);
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     * @param null                $order
     *
     * @return string
     */
    private function _addDatesInfoEmail($dom, $order = null)
    {
        if ($this->helperCalendar->isSameDayOrder($order)) {
            /** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail $block */
            $block = $this->layout->createBlock('\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail', '', ['data' => ['hasOrder' => $order]]);
            $html = $this->cleanHtml($block->toHtml());
            $dom->prepend($html);
        }
    }

    /**
     * @param \QueryPath\DOMQuery $node
     * @param                     $isChanged
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _addSerialsInput($node, &$isChanged)
    {
        /*
         * Because is using PHP 7 we will wait on implementing this. But still is easier to use and should be adopted
         * link to SRL: https://simple-regex.com/build/57f64c613e77f
         */
        /* @var array $matches */
        preg_match('/(?:shipment\[items]\[)(?<orderitem>[0-9]+)(?:])/', $node->attr('name'), $matches);

        /** @var array $productsData */
        $productsData = [];
        if (array_key_exists('orderitem', $matches)) {
            $orderItem = $matches['orderitem'];
            if ($this->_helperRental->isRentalType($this->_helperRental->getProductIdFromOrderItem($orderItem))) {
                $productsData = $this->_helperRental->getProductIdsFromOrderItem($orderItem);
            }
        }
        /** @var array $product */
        foreach ($productsData as $product) {
            if ($this->_helperRental->isSerialEnabledForProduct($product['product_id'])) {
                /** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment\Serials $block */
                $block = $this->layout->createBlock(
                    '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment\Serials',
                    'serial_select_'.$product['order_item_id'],
                    ['data' => ['product_id' => $product['product_id'], 'item_id' => $product['order_item_id'], 'qty_value' => $product['qty']]]
                );
                $html = $this->cleanHtml($block->toHtml());
                $node->parents()->eq(0)->append($html);
                $isChanged = true;
            }
        }

        //return $dom->html();
    }

    /**
     * @param $fileName
     * @param $domHtml
     * @param $isChanged
     *
     * @return string
     *
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \InvalidArgumentException
     *
     * @internal param $html
     */
    protected function renameButtonsOnListingAndProductPage($fileName, $domHtml, &$isChanged)
    {
        if ($this->_helperRental->isFrontend() &&
            (strpos($fileName, 'addtocart.phtml') !== false ||
                strpos($fileName, 'list.phtml') !== false)
        ) {
            $this->_renameButtons($domHtml, $isChanged);
        }
    }

    /**
     * @param \QueryPath\DOMQuery $dom
     * @param                     $isChanged
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \InvalidArgumentException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function renameButtonsOnProductPage($dom, &$isChanged)
    {
        if ($this->coreRegistry->registry('current_product')) {
            $product = $this->coreRegistry->registry('current_product');
            if ($this->_helperRental->isRentalType($product->getId())) {
                $buttons = $dom->find('.tocart');

                /** @var \QueryPath\DOMQuery $button */
                foreach ($buttons as $button) {
                    $dataType = $this->_helperRental->isBuyout($product->getId()) ? 'rental-buyout' : '';
                    /** @var \QueryPath\DOMQuery $span */
                    $span = $button->find('span')->first();
                    $span->text(__('Rent'));

                    if ($dataType == 'rental-buyout') {
                        $newButton = clone $button;
                        /* @var \QueryPath\DOMQuery $newButton */
                        $newButton->attr('title', 'Buyout');
                        $button->removeAttr('xmlns');
                        $newButton->attr('name', 'is_buyout');
                        $newButton->addClass('rental-buyout');
                        if ($newButton->find('span')->first()->length > 0) {
                            $newButton->find('span')->first()->text(__('Buyout'));
                        } else {
                            $newButton->text(__('Buyout'));
                        }

                        $button->parent()->append($newButton);
                    }
                    $isChanged = true;
                }
            }
        }
    }

    /**
     * @param $dom
     * @param $isChanged
     */
    private function renameButtonsOnListing($dom, &$isChanged)
    {
        /** @var \QueryPath\DOMQuery $priceBoxs */
        $priceBoxs = $dom->find('.pricing-ppr');

        /** @var \QueryPath\DOMQuery $priceBox */
        foreach ($priceBoxs as $priceBox) {
            /** @var string $dataType */
            $dataType = $priceBox->attr('data-type');

            /** @var \QueryPath\DOMQuery $button */
            $button = $priceBox->parent()->parent()->parent()->find('.tocart')->first();
            $span = $button->find('span')->first();

            $span->text(__('Rent'));
            if ($dataType == 'rental-buyout') {
                $button->attr('style', 'margin-top: 15px');
                $button->parent()->append($button);
                $newButton = $button->next();
                $newButton->attr('title', 'Buyout');
                $newButton->attr('style', 'margin-top: 15px;');
                $newButton->addClass('rental-buyout');
                $newButton->find('span')->first()->text(__('Buyout'));
            }
            $isChanged = true;
        }
    }

    /**
     * @param                     $subject
     * @param \QueryPath\DOMQuery $domHtml
     * @param                     $isChanged
     *
     * @return string
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function addCalendarAdmin($subject, $domHtml, &$isChanged)
    {
        if (($this->_helperRental->isBackendAdminOrderEdit() &&
                $subject->getNameInLayout() === 'items') ||
            ($this->_helperRental->isBackend() &&
                $subject->getNameInLayout() === 'product.composite.fieldset.options.js' &&
                $this->_helperRental->isRentalType($this->coreRegistry->registry('current_product')))
        ) {
            $this->_appendCalendarAdmin($domHtml);
            $isChanged = true;
        }

        if ($this->_helperRental->isBackendAdminOrderEdit() && $subject->getNameInLayout() === 'items_grid') {
            $this->_appendAdminCreateOrderUpdate($domHtml);
            $isChanged = true;
        }
    }

    /**
     * Function to update order view with return grid panel.
     *
     * @param $subject
     * @param $domHtml
     * @param $isChanged
     */
    private function orderViewUpdate($subject, $domHtml, &$isChanged)
    {
        if ($subject->getNameInLayout() === 'sales_order_edit-return_order-button' && $this->_helperRental->isBackend()) {
            $this->_addReturnGridPanel($domHtml);
            $isChanged = true;
        }
    }

    /**
     * @param $subject
     * @param $domHtml
     * @param $isChanged
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function modifyShipPage($subject, $domHtml, &$isChanged)
    {
        if ($subject->getNameInLayout() === 'order_items' && $this->_helperRental->isBackend()) {
            $nodes = $domHtml->find('.col-qty input');
            foreach ($nodes as $node) {
                $this->_addSerialsInput($node, $isChanged);
            }
        }
    }
}
