<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\FixedDates;

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
     * Check the permission to run it.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('SalesIgniter_Rental::fixeddates');
    }

    /**
     * Filtering posted data. Converting localized data if needed.
     *
     * @param array $data
     *
     * @return array
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function filter($data)
    {
        $filterRules = [];
        if (isset($data['rental-fixeddate']) && is_array($data['rental-fixeddate']['fixeddates'])) {
            foreach ($data['rental-fixeddate']['fixeddates'] as $key => $fixDate) {
                foreach (['date_from', 'date_to'] as $dateField) {
                    if (!empty($fixDate[$dateField]) && $fixDate[$dateField] !== '0000-00-00 00:00:00') {
                        if ($fixDate['all_day'] === '1') {
                            $filterRules[$dateField] = $this->dateFilter;
                        } else {
                            $filterRules[$dateField] = $this->datetimeFilter;
                        }
                    }
                }

                $data['rental-fixeddate']['fixeddates'][$key] = (new \Zend_Filter_Input($filterRules, [], $fixDate))->getUnescaped();
                if (new \DateTime($data['rental-fixeddate']['fixeddates'][$key]['date_from']) > new \DateTime($data['rental-fixeddate']['fixeddates'][$key]['date_to'])) {
                    unset($data['rental-fixeddate']['fixeddates'][$key]);
                }
            }
        }

        return $data;
    }

    /**
     * Check if required fields is not empty.
     *
     * @param array $data
     *
     * @return bool
     */
    public function validateRequireEntry(array $data)
    {
        $requiredFields = [
            'name',
            /*'catalog_rules',*/
        ];
        $errorNo = true;
        $hasDates = false;
        foreach ($data as $field => $value) {
            if ($value === '' && array_key_exists($field, $requiredFields)) {
                $errorNo = false;
                $this->messageManager->addErrorMessage(
                    __('To apply changes you should fill in hidden required "%1" field', $requiredFields[$field])
                );
            }
            if ($field === 'rental-fixeddate') {
                $hasDates = true;
            }
        }
        if (!$hasDates) {
            $errorNo = false;
            $this->messageManager->addErrorMessage(
                __('You need at least one date filled')
            );
        }

        return $errorNo;
    }
}
