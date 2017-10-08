<?php

namespace SalesIgniter\Rental\Block\Adminhtml;

use Magento\Backend\Block\Template as BlockTemplate;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;

class Report extends BlockTemplate
{

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Report constructor.
     *
     * @param Context          $context
     * @param EncoderInterface $JsonEncoder
     * @param array            $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $JsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_jsonEncoder = $JsonEncoder;
    }

    /**
     * @param $Code
     *
     * @return $this
     */
    public function setCode($Code)
    {
        $this->setData('code', $Code);
        return $this;
    }

    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->getData('code');
    }

    /**
     * @return string
     */
    public function getReportConfig()
    {
        $Configuration = [
            'code' => $this->getCode(),
            'Report' => [
                'dataUrl' => $this->getUrl('*/*/getReportData'),
            ],
            'Filter' => [],
            'Pager' => [
                'pageVarName' => 'p',
                'page' => 1,
                'limitVarName' => 'limit',
                'limit' => 5
            ],
            'Calendar' => [
                'rendererCode' => 'month'
            ]
        ];

        return $this->_jsonEncoder->encode($Configuration);
    }
}
