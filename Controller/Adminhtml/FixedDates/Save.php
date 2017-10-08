<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Controller\Adminhtml\FixedDates;

use Magento\Backend\App\Action;
use Magento\Cms\Model\Page;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Exception\LocalizedException;
use SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;

class Save extends \Magento\Backend\App\Action
{
    /**
     * @var PostDataProcessor
     */
    protected $dataProcessor;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface
     */
    private $fixedRentalDatesRepository;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface
     */
    private $fixedRentalNamesRepository;

    /**
     * @param Action\Context                                               $context
     * @param PostDataProcessor                                            $dataProcessor
     * @param \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository
     * @param \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
     * @param DataPersistorInterface                                       $dataPersistor
     */
    public function __construct(
        Action\Context $context,
        PostDataProcessor $dataProcessor,
        FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository,
        FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository,
        DataPersistorInterface $dataPersistor
    )
    {
        $this->dataProcessor = $dataProcessor;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
        $this->fixedRentalDatesRepository = $fixedRentalDatesRepository;
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
     * Save action.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     *
     * @return \Magento\Framework\Controller\ResultInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            if (empty($data['date_from']) || $data['date_from'] == 'Invalid date') {
                $data['date_from'] = '0000-00-00 00:00:00';
            }
            if (empty($data['date_to']) || $data['date_to'] == 'Invalid date') {
                $data['date_to'] = '0000-00-00 00:00:00';
            }

            $data = $this->dataProcessor->filter($data);

            $idName = $this->getRequest()->getParam('name_id');
            if ($idName) {
                $fixedName = $this->fixedRentalNamesRepository->getById($idName);
                $data['name_id'] = (int)$fixedName->getNameId();
            } else {
                unset($data['name_id']);
            }

            if (!$this->dataProcessor->validateRequireEntry($data)) {
                if ($idName) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $fixedName->getId(), '_current' => true]);
                } else {
                    return $resultRedirect->setPath('*/*/');
                }
            }

            try {
                //$data['catalog_rules'] = serialize($data['catalog_rules']);//this will be used if at any point catalog rules should be considered

                $idName = (int)$this->fixedRentalNamesRepository->saveData($data);
                $datesData = $data['rental-fixeddate']['fixeddates'];

                $this->fixedRentalDatesRepository->deleteByNameId($idName);
                foreach ($datesData as $dates) {
                    $dates['name_id'] = $idName;
                    unset($dates['date_id']);

                    $dateFrom = new \DateTime($dates['date_from']);
                    $dates['date_from'] = $dateFrom->format('Y-m-d H:i:s');
                    $dateTo = new \DateTime($dates['date_to']);
                    if ($dates['all_day']) {
                        $dateTo->add(new \DateInterval('PT23H59M'));
                    }
                    $dates['date_to'] = $dateTo->format('Y-m-d H:i:s');
                    if (isset($dates['repeat_days'])) {
                        $dates['repeat_days'] = serialize($dates['repeat_days']);
                    }
                    if (isset($dates['week_month'])) {
                        $dates['week_month'] = serialize($dates['week_month']);
                    }

                    $this->fixedRentalDatesRepository->saveData($dates);
                }
                $this->messageManager->addSuccessMessage(__('You saved the predetermined date.'));
                $this->dataPersistor->clear('sirent_fixeddates');
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['id' => $fixedName->getId(), '_current' => true]);
                }

                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the page.'));
            }

            $this->dataPersistor->set('sirent_fixeddates', $data);

            return $resultRedirect->setPath('*/*/edit', ['id' => $this->getRequest()->getParam('name_id')]);
        }

        return $resultRedirect->setPath('*/*/');
    }
}
