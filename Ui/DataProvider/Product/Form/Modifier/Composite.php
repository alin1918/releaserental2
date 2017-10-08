<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\ObjectManagerInterface;
use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use SalesIgniter\Rental\Model\Product\Type\Sirent;

/**
 * Class Bundle customizes Bundle product creation flow
 */
class Composite extends AbstractModifier
{
    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var array
     */
    protected $modifiers = [];

    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param LocatorInterface           $locator
     * @param ObjectManagerInterface     $objectManager
     * @param ProductRepositoryInterface $productRepository
     * @param array                      $modifiers
     */
    public function __construct(
        LocatorInterface $locator,
        ObjectManagerInterface $objectManager,
        ProductRepositoryInterface $productRepository,
        array $modifiers = []
    ) {
        $this->locator = $locator;
        $this->objectManager = $objectManager;
        $this->productRepository = $productRepository;
        $this->modifiers = $modifiers;
    }

    /**
     * {@inheritdoc}
     */
    public function modifyMeta(array $meta)
    {
        if ($this->locator->getProduct()->getTypeId() === Sirent::TYPE_RENTAL) {
            foreach ($this->modifiers as $reservationClass) {
                /** @var ModifierInterface $reservationModifier */
                $reservationModifier = $this->objectManager->get($reservationClass);
                if (!$reservationModifier instanceof ModifierInterface) {
                    throw new \InvalidArgumentException(
                        'Type "' . $reservationClass . '" is not an instance of ' . ModifierInterface::class
                    );
                }
                $meta = $reservationModifier->modifyMeta($meta);
            }
        }
        return $meta;
    }

    /**
     * {@inheritdoc}
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function modifyData(array $data)
    {
        if ($this->locator->getProduct()->getTypeId() === Sirent::TYPE_RENTAL) {
            $modelId = $this->locator->getProduct()->getId();
            /*For some reason Select and Multiselect type uses decimal*/
            $fieldsArray = [
                'sirent_rental_type',
                'sirent_use_times',
                'sirent_pricingtype',
            ];

            foreach ($fieldsArray as $field) {
                if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][$field])) {
                    $data[$modelId][static::DATA_SOURCE_DEFAULT][$field] =
                        (int)$data[$modelId][static::DATA_SOURCE_DEFAULT][$field];
                }
            }

            $fieldsArray = [
                'sirent_excludeddays_start',
                'sirent_excludeddays_end',
                'sirent_excluded_days',
            ];

            foreach ($fieldsArray as $field) {
                if (isset($data[$modelId][static::DATA_SOURCE_DEFAULT][$field])) {
                    $arrValues = $data[$modelId][static::DATA_SOURCE_DEFAULT][$field];
                    if (is_array($arrValues)) {
                        foreach ($arrValues as $key => $value) {
                            $arrValues[$key] = (int)$value;
                        }
                        $data[$modelId][static::DATA_SOURCE_DEFAULT][$field] = $arrValues;
                    }
                }
            }
        }
        foreach ($this->modifiers as $reservationClass) {
            /** @var ModifierInterface $reservationModifier */
            $reservationModifier = $this->objectManager->get($reservationClass);
            if (!$reservationModifier instanceof ModifierInterface) {
                throw new \InvalidArgumentException(
                    'Type "' . $reservationClass . '" is not an instance of ' . ModifierInterface::class
                );
            }
            $data = $reservationModifier->modifyData($data);
        }

        return $data;
    }
}
