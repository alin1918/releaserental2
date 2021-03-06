<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales Order Email Invoice items
 *
 * @author     Magento Core Team <core@magentocommerce.com>
 */
namespace SalesIgniter\Rental\Block\Emails\Returns;

class Items extends \Magento\Sales\Block\Items\AbstractItems
{
    /**
     * Prepare item before output
     *
     * @param \Magento\Framework\View\Element\AbstractBlock $renderer
     *
     * @return \Magento\Sales\Block\Items\AbstractItems
     */
    protected function _prepareItem(\Magento\Framework\View\Element\AbstractBlock $renderer)
    {
        $renderer->getItem()->setOrder($this->getOrder());
        $renderer->getItem()->setSource($this->getReturn());
    }
}
