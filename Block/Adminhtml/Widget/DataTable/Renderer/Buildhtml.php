<?php

namespace SalesIgniter\Rental\Block\Adminhtml\Widget\DataTable\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\Text;
use Magento\Framework\DataObject;

class Buildhtml extends Text
{

    /**
     * @param DataObject $row
     *
     * @return string
     */
    public function _getValue(DataObject $row)
    {
        $RenderConfig = $this->getColumn()->getData('renderConfig');
        $Template = $RenderConfig['template'];

        try {
            $Variables = [];
            preg_match_all('/{{(.[^}]+)}}/', $Template, $Variables);
            if (isset($Variables[1])) {
                foreach ($Variables[1] as $DataKey) {
                    try {
                        $Template = preg_replace('/{{' . $DataKey . '}}/', $row->getData($DataKey), $Template);
                    } catch (\Exception $e) {
                        $Template = 'There was an error in the template parsing.';
                        $Template .= '<br><br>' . '/{{' . $DataKey . '}}/';
                        break;
                    }
                }
            }
        } catch (\Exception $e) {
            $Template = $e->getMessage();
        }

        $Html = $Template;

        return $Html;
    }
}
