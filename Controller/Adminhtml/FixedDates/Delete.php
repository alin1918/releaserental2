<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\FixedDates;

use Magento\Backend\App\Action\Context;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface
     */
    private $fixedRentalNamesRepository;

    /**
     * @param Context                                                      $context
     * @param \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
     */
    public function __construct(
        Context $context,
        FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
    ) {
        parent::__construct($context);

        $this->fixedRentalNamesRepository = $fixedRentalNamesRepository;
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
     * Delete action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        // check if we know what should be deleted
        $idName = $this->getRequest()->getParam('name_id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($idName) {
            try {
                $this->fixedRentalNamesRepository->delete($idName);
                // display success message
                $this->messageManager->addSuccessMessage(__('The Predetermined date has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['name_id' => $idName]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a predetermined date to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}
