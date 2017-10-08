<?php

namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\FixedDates\Column;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                $id = 0;
                if (isset($item['name_id'])) {
                    $id = $item['name_id'];
                }
                $item[$name]['view'] = [
                    'href' => $this->getContext()->getUrl(
                        'salesigniter_rental/fixeddates/edit', ['id' => $id]),
                    'label' => __('Edit'),
                ];
            }
        }

        return $dataSource;
    }
}
