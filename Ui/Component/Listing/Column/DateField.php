<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class DateField
 *
 * @package SalesIgniter\Rental\Ui\Component\Listing\Column
 */
class DateField extends Column
{
    private $_userFactory;

    /**
     * Constructor
     *
     * @param ContextInterface                                   $context
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\User\Model\UserFactory                    $userFactory
     * @param array                                              $components
     * @param array                                              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\User\Model\UserFactory $userFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_userFactory = $userFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName . '_value'] = '';
            }
        }

        return $dataSource;
    }
}
