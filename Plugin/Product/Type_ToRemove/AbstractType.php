<?php
namespace SalesIgniter\Rental\Model\Plugin\Product\Type;

/*not used for the moment. Using event for now*/
class AbstractType
{
    
    /**
     * @var \SalesIgniter\Rental\Helper\Data $_helperRental
     */
    protected $_helperRental;

    /**
     * @param \SalesIgniter\Rental\Helper\Data $helperRental
     */
    public function __construct(
        \SalesIgniter\Rental\Helper\Data $helperRental
    ) {
        $this->_helperRental = $helperRental;
    }

    /**
     * If using dates simple, the custom option object does not have 00 as time in js, so I
     * don't validate the option and modify the buyRequest
     * @param $options
     * @param $product
     * @return array
     */

    private function _getUpdatedBuyRequestToDate($options, $product)
    {
        $optionsTemp = array();
        foreach ($options as $optionId => $optionData) {
            $option = $product->getOptionById($optionId);
            if ($option->getTitle() == 'Start Date:' || $option->getTitle() == 'End Date:') {
                if ($optionData['hour'] == '') {
                    $optionData['hour'] = '00';
                }
                if ($optionData['minute'] == '') {
                    $optionData['minute'] = '00';
                }
            }
            $optionsTemp[$optionId] = $optionData;
        }
        return $optionsTemp;
    }

    /**
     * Do not remove custom options for bundles with dynamic pricing (we make a backup)
     *
     * @see \Magento\Bundle\Model\Product\Type::beforeSave
     *
     * @param \Magento\Catalog\Model\Product\Type\AbstractType $subject
     * @param \Closure                                         $proceed
     * @param \Magento\Framework\DataObject                    $buyRequest
     * @param                                                  $product
     * @param null                                             $processMode
     *
     * @return null
     */
    public function aroundPrepareForCartAdvanced(
        \Magento\Catalog\Model\Product\Type\AbstractType $subject,
        \Closure $proceed,
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode = null
    ) {
        if ($this->_helperRental->isRentalType($product)) {
            $options = $buyRequest->getOptions();
            $optionsTemp = $this->_getUpdatedBuyRequestToDate($options, $product);
            $buyRequest->setOptions($optionsTemp);
        }
        $proceed($buyRequest, $product, $processMode);
    }
}
