<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Ui\DataProvider\FixedDates\Form\Modifier;

use Magento\Catalog\Api\ProductLinkRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Helper\Image as ImageHelper;
use Magento\Catalog\Model\Locator\LocatorInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Eav\Api\AttributeSetRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\UrlInterface;
use Magento\Ui\Component\Form\Element\MultiSelect;
use Magento\Ui\Component\Form\Element\Input;
use Magento\Ui\Component\Form\Field;
use SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface;
use SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface;
use SalesIgniter\Rental\Model\Attribute\Sources\CatalogRules;

/**
 * Class Related.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FixedRentalCatalogRules extends AbstractModifier
{
    const DATA_SCOPE = 'catalog_rules';
    const DATA_SCOPE_FIXEDDATES = 'catalog_rules';
    const GROUP_FIXEDDATES = 'catalog-rules-fixeddates';

    /**
     * @var string
     */
    private static $previousGroup = 'search-engine-optimization';

    /**
     * @var int
     */
    private static $sortOrder = 90;

    /**
     * @var LocatorInterface
     */
    protected $locator;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var ProductLinkRepositoryInterface
     */
    protected $productLinkRepository;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var ImageHelper
     */
    protected $imageHelper;

    /**
     * @var Status
     */
    protected $status;

    /**
     * @var AttributeSetRepositoryInterface
     */
    protected $attributeSetRepository;

    /**
     * @var string
     */
    protected $scopeName;

    /**
     * @var string
     */
    protected $scopePrefix;

    /**
     * @var \Magento\Catalog\Ui\Component\Listing\Columns\Price
     */
    private $priceModifier;
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;
    /**
     * @var \SalesIgniter\Rental\Ui\DataProvider\Reservation\Form\Modifier\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface
     */
    private $fixedRentalDatesRepository;
    /**
     * @var \SalesIgniter\Rental\Model\Attribute\Sources\CatalogRules
     */
    private $catalogRules;
    /**
     * @var \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface
     */
    private $fixedRentalNamesRepository;

    /**
     * @param UrlInterface                                                 $urlBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder                 $searchCriteriaBuilder
     * @param \SalesIgniter\Rental\Api\FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository
     * @param \SalesIgniter\Rental\Api\FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository
     * @param \SalesIgniter\Rental\Model\Attribute\Sources\CatalogRules    $catalogRules
     * @param \Magento\Framework\App\RequestInterface                      $request
     * @param string                                                       $scopeName
     * @param string                                                       $scopePrefix
     */
    public function __construct(
        UrlInterface $urlBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FixedRentalDatesRepositoryInterface $fixedRentalDatesRepository,
        FixedRentalNamesRepositoryInterface $fixedRentalNamesRepository,
        \SalesIgniter\Rental\Model\Attribute\Sources\CatalogRules $catalogRules,
        \Magento\Framework\App\RequestInterface $request,
        $scopeName = '',
        $scopePrefix = ''
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->scopeName = $scopeName;
        $this->scopePrefix = $scopePrefix;
        $this->request = $request;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        $this->fixedRentalDatesRepository = $fixedRentalDatesRepository;
        $this->catalogRules = $catalogRules;
        $this->fixedRentalNamesRepository = $fixedRentalNamesRepository;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_FIXEDDATES => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'collapsible' => false,
                                'componentType' => Field::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'formElement' => Input::NAME,
                                'label' => __('Discount(Percent or Fixed, use + to add to price)'),
                                'notice' => __('e.g. 25% is a 25% discount || +25% is a 25% markup || 25 is a fixed discount 25 || +25 is a markup of 25'),
                                'enableLabel' => true,
                            ],
                        ],

                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyData(array $data)
    {
        $dataScope = self::DATA_SCOPE_FIXEDDATES;
        $idName = $this->request->getParam('id');
        if ($idName) {
            $fixedName = $this->fixedRentalNamesRepository->getById($idName);
            if ($fixedName->getId()) {
                $data[$idName][$dataScope] = $fixedName->getCatalogRules();
            }
        }
        if (!isset($data[$idName][$dataScope])) {
            $data[$idName][$dataScope] = '';
        }

        return $data;
    }

    /*With Catalog rules*/
    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function modifyMetaCR(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                static::GROUP_FIXEDDATES => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'collapsible' => false,
                                'componentType' => Field::NAME,
                                'dataScope' => static::DATA_SCOPE,
                                'formElement' => MultiSelect::NAME,
                                'options' => $this->catalogRules->getOptionsArray(),
                                'label' => __('Catalog Rules'),
                                'enableLabel' => true,
                            ],
                        ],

                    ],
                ],
            ]
        );

        return $meta;
    }

    /**
     * {@inheritdoc}
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modifyDataCR(array $data)
    {
        $dataScope = self::DATA_SCOPE_FIXEDDATES;
        $idName = $this->request->getParam('id');
        if ($idName) {
            $fixedName = $this->fixedRentalNamesRepository->getById($idName);
            if ($fixedName->getId()) {
                $data[$idName][$dataScope] = array_map('intval', unserialize($fixedName->getCatalogRules()));
            }
        }
        if (!isset($data[$idName][$dataScope])) {
            $data[$idName][$dataScope] = '';
        }

        return $data;
    }

    /**
     * Retrieve all data scopes.
     *
     * @return array
     */
    protected function getDataScopes()
    {
        return [
            static::DATA_SCOPE_FIXEDDATES,
        ];
    }
}
