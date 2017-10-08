<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Block\System\Config\Form\Field;

/**
 * Backend system config array field renderer
 */
class GlobalExcludeDates extends \Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray
{
    /**
     * @var string
     */
    protected $_template = 'SalesIgniter_Rental::system/config/form/field/array.phtml';

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory
     */
    protected $_elementFactory;

    /**
     * @var \Magento\Framework\View\Design\Theme\LabelFactory
     */
    protected $_labelFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param \Magento\Framework\Data\Form\Element\Factory      $elementFactory
     * @param \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        \Magento\Framework\View\Design\Theme\LabelFactory $labelFactory,
        array $data = []
    )
    {
        $this->_elementFactory = $elementFactory;
        $this->_labelFactory = $labelFactory;
        parent::__construct($context, $data);
    }

    /**
     * Initialise form fields
     *
     * @return void
     */
    protected function _construct()
    {
        $this->addColumn('disabled_from', ['label' => __('Disabled From')]);
        $this->addColumn('disabled_to', ['label' => __('Disabled To')]);
        $this->addColumn('all_day', ['label' => __('All Day')]);
        $this->addColumn('disabled_type', ['label' => __('Repeat')]);
        $this->addColumn('exclude_dates_from', ['label' => __('Exclude From')]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
        parent::_construct();
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return string
     */
    public function renderDateTemplate($columnName)
    {
        return parent::renderCellTemplate($columnName);
    }

    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return string
     */
    public function renderRepeatTemplate($columnName)
    {
        return parent::renderCellTemplate($columnName);
    }


    /**
     * Render array cell for prototypeJS template
     *
     * @param string $columnName
     *
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function renderCellTemplate($columnName)
    {
        if (($columnName == 'disabled_from' || $columnName == 'disabled_to') && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $dateFormat = $this->_localeDate->getDateFormat(
                \IntlDateFormatter::MEDIUM
            );
            $timeFormat = 'HH:mm';
            $element = $this->_elementFactory->create('date');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setDateFormat(
                $dateFormat
            )->setTimeFormat(
                $timeFormat
            )->setImage(
                $this->getViewFileUrl('Magento_Theme::calendar.png')
            );
            return $element->getElementHtml();
        }
        if ($columnName == 'disabled_type' && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $label = $this->_labelFactory->create();
            $options = [
                'none' => __('None'),
                //This period never repeats
                'daily' => __('Daily'),
                //It will repeat every day at the same time Start and End date should
                // be the same only Times should be different
                'dayweek' => __('Weekly'),
                //It will repeat the same day of week every week
                // and it will repeat same day of the week at the same time or full day
                // if 'all day' is enabled
                'monthly' => __('Monthly'),
                //It will repeat the same day every month at the same time
                'yearly' => __('Yearly')
                //It will repeat the same day of the same month every year
            ];
            $element = $this->_elementFactory->create('select');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );
            return str_replace("\n", '', $element->getElementHtml());
        }

        if ($columnName == 'all_day' && isset($this->_columns[$columnName])) {
            $element = $this->_elementFactory->create('checkbox');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            );
            return str_replace("\n", '', $element->getElementHtml());
        }


        if ($columnName == 'exclude_dates_from' && isset($this->_columns[$columnName])) {
            /** @var $label \Magento\Framework\View\Design\Theme\Label */
            $label = $this->_labelFactory->create();
            $options = [
                ['value' => 'calendar', 'label' => __('Calendar')],
                ['value' => 'price', 'label' => __('Price')],
                ['value' => 'turnover', 'label' => __('Turnover')]
            ];
            $element = $this->_elementFactory->create('multiselect');
            $element->setForm(
                $this->getForm()
            )->setName(
                $this->_getCellInputElementName($columnName)
            )->setHtmlId(
                $this->_getCellInputElementId('<%- _id %>', $columnName)
            )->setValues(
                $options
            );
            return str_replace("\n", '', $element->getElementHtml());
        }


        return parent::renderCellTemplate($columnName);
    }
}
