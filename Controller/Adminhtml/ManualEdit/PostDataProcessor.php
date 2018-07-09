<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\ManualEdit;

class PostDataProcessor
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    protected $dateFilter;

    /**
     * @var \Magento\Framework\View\Model\Layout\Update\ValidatorFactory
     */
    protected $validatorFactory;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\Filter\DateTime
     */
    private $datetimeFilter;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    private $rentalHelper;

    /**
     * @param \Magento\Framework\Stdlib\DateTime\Filter\DateTime           $datetimeFilter
     * @param \Magento\Framework\Stdlib\DateTime\Filter\Date               $dateFilter
     * @param \SalesIgniter\Rental\Helper\Data                             $rentalHelper
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager
     * @param \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\Filter\DateTime $datetimeFilter,
        \Magento\Framework\Stdlib\DateTime\Filter\Date $dateFilter,
        \SalesIgniter\Rental\Helper\Data $rentalHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\View\Model\Layout\Update\ValidatorFactory $validatorFactory
    ) {
        $this->dateFilter = $dateFilter;
        $this->messageManager = $messageManager;
        $this->validatorFactory = $validatorFactory;
        $this->datetimeFilter = $datetimeFilter;
        $this->rentalHelper = $rentalHelper;
    }

    /**
     * Check the permission to run it
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SalesIgniter_Rental::manualedit');
    }

    /**
     * Filtering posted data. Converting localized data if needed
     *
     * @param array $data
     *
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function filter($data)
    {
        $filterRules = [];
        $hasTimes = false;
        if (isset($data['product_id'])) {
            $product = $this->rentalHelper->getProductObjectFromId($data['product_id']);
            if ($product !== null) {
                $hasTimes = $product->getSirentUseTimes() > 0;
            }
        }        
        foreach (['start_date', 'end_date'] as $dateField) {
            if (!empty($data[$dateField]) && $data[$dateField] !== '0000-00-00 00:00:00') {
                if ($hasTimes) {
                    $filterRules[$dateField] = $this->datetimeFilter;
                } else {
                    $filterRules[$dateField] = $this->dateFilter;
                }
                $data[$dateField] = new \DateTime($data[$dateField]);                                   
            }
        }

        return (new \Zend_Filter_Input($filterRules, [], $data))->getUnescaped();
    }

    /**
     * Validate post data
     *
     * @param array $data
     *
     * @return bool     Return FALSE if someone item is invalid
     */
    public function validate($data)
    {
        $errorNo = true;
        if (new \DateTime($data['start_date']) > new \DateTime($data['end_date'])) {
            $errorNo = false;
        }
        return $errorNo;
    }

    /**
     * Check if required fields is not empty
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            /*'title' => __('Page Title'),*/
        ];
        $errorNo = true;
        foreach ($data as $field => $value) {
            if (in_array($field, array_keys($requiredFields)) && $value === '') {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredFields[$field])
                );
            }
        }
        return $errorNo;
    }
}
