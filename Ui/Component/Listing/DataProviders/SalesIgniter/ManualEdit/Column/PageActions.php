<?php
namespace SalesIgniter\Rental\Ui\Component\Listing\DataProviders\SalesIgniter\ManualEdit\Column;

class PageActions extends \Magento\Ui\Component\Listing\Columns\Column
{
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $name = $this->getData('name');
                $id = 0;
                if (isset($item['reservationorder_id'])) {
                    $id = $item['reservationorder_id'];
                }
                $idOrder = 0;
                if (isset($item['order_id'])) {
                    $idOrder = $item['order_id'];
                }
                $item[$name][] = [
                    'href' => $this->getContext()->getUrl(
                        'salesigniter_rental/manualedit/edit', ['id' => $id]),
                    'label' => __('Edit'),
                ];                
            }
        }

        return $dataSource;
    }
}
