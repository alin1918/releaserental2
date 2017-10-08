<?php

namespace SalesIgniter\Rental\Model\Product;

use League\Period\Period;
use Magento\Catalog\Model\ProductRepository;

/**
 * Class Stock
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.LongVariableNames)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 *
 * @package SalesIgniter\Rental\Model\Product
 */
class Stock
{
    const OVERBOOK_QTY = 9999999;
    const MINIMUM_PERIOD_ERROR = 1;
    const MAXIMUM_PERIOD_ERROR = 2;
    const BOOKED_DATES_ERROR = 3;
    const NO_ERROR = 0;
    const DISABLED_DATES_ERROR = 5;
    const SAME_DATES_ENFORCE_ERROR = 6;
    const SELECT_START_END_DATES_ERROR = 7;
    const NOT_ENOUGH_QUANTITY_ERROR = 8;
    const NOT_ENOUGH_QUANTITY_ERROR_BUYOUT = 11;
    const END_DATE_DISABLED_ERROR = 9;
    const START_DATE_DISABLED_ERROR = 10;
    const END_DATE_DISABLED_ERROR_FULL = 12;
    const START_DATE_DISABLED_ERROR_FULL = 13;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $_catalogSession;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;
    /**
     * @var \Magento\Catalog\Model\Product\Action
     */
    protected $attributeAction;
    /**
     * @var \SalesIgniter\Rental\Helper\Data
     */
    protected $rentalHelper;
    /**
     * @var \SalesIgniter\Rental\Helper\Calendar
     */
    protected $calendarHelper;
    /**
     * @var \SalesIgniter\Rental\Helper\Date
     */
    protected $dateHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Catalog\Model\Session                     $catalogSession
     * @param \Magento\Catalog\Model\ProductRepository           $productRepository
     * @param \Magento\Catalog\Model\Product\Action              $attributeAction
     * @param \Magento\Framework\Registry                        $coreRegistry
     * @param \SalesIgniter\Rental\Helper\Data                   $rentalHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \SalesIgniter\Rental\Helper\Date                   $dateHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime        $datetime
     * @param \SalesIgniter\Rental\Helper\Calendar               $calendarHelper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        ProductRepository $productRepository,
        \Magento\Catalog\Model\Product\Action $attributeAction,
        \Magento\Framework\Registry $coreRegistry,
        \SalesIgniter\Rental\Helper\Data $rentalHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \SalesIgniter\Rental\Helper\Date $dateHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \SalesIgniter\Rental\Helper\Calendar $calendarHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->productRepository = $productRepository;
        $this->attributeAction = $attributeAction;
        $this->rentalHelper = $rentalHelper;
        $this->calendarHelper = $calendarHelper;
        $this->dateHelper = $dateHelper;
        $this->datetime = $datetime;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * This function merges the periods so we have a fewer periods in the array.
     * This is done only for same day booking. The idea is to see if the day is fully booked
     * 's' ->5 'e'->7, 's'->7 'e'->9 will become 's'-> 5 'e'->9
     *
     * @param array $updatedInventory
     *
     * @return array
     */
    public function mergedInventory($updatedInventory)
    {
        $firstVal = 0;
        $secondVal = 1;
        /** @var array $mergedInventory */
        $mergedInventory = [];
        $minQty = self::OVERBOOK_QTY;
        while ($secondVal < count($updatedInventory)) {
            /** @var Period $reservationPeriodStart */
            $reservationPeriodStart = new Period(
                $updatedInventory[$firstVal]['s'] . ':00',
                $updatedInventory[$firstVal]['e'] . ':00'
            );

            /** @var Period $reservationPeriodEnd */
            $reservationPeriodEnd = new Period(
                $updatedInventory[$secondVal]['s'] . ':00',
                $updatedInventory[$secondVal]['e'] . ':00'
            );
            if (
            $reservationPeriodStart->abuts($reservationPeriodEnd)
            ) {
                $reservationPeriodEnd = $reservationPeriodStart->merge($reservationPeriodEnd);
                if ($minQty > $updatedInventory[$secondVal]['q']) {
                    $minQty = $updatedInventory[$secondVal]['q'];
                }
                $updatedInventory[$secondVal]['s'] = $reservationPeriodEnd->getStartDate()->format('Y-m-d H:i');
                $updatedInventory[$secondVal]['e'] = $reservationPeriodEnd->getEndDate()->format('Y-m-d H:i');
            } else {
                if ($minQty !== self::OVERBOOK_QTY) {
                    $updatedInventory[$secondVal]['q'] = $minQty;
                    $mergedInventory[] = $updatedInventory[$secondVal];
                    $minQty = self::OVERBOOK_QTY;
                } else {
                    $mergedInventory[] = $updatedInventory[$firstVal];
                }
            }

            $firstVal++;
            $secondVal++;
        }
        if ($minQty !== self::OVERBOOK_QTY) {
            $updatedInventory[$firstVal]['q'] = $minQty;
            $mergedInventory[] = $updatedInventory[$firstVal];
        } else {
            $mergedInventory[] = $updatedInventory[$firstVal];
        }
        return $mergedInventory;
    }

    /**
     * This function is needed for the calendar.
     * If using times and store_open store_close times is impossible to know when a full date is booked.
     * This need complex calculation, based on store open and store close times. Keep in mind that
     * these are different based on days.
     *
     * @param $updatedInventory
     *
     * @return array
     */
    public function updateFullDatesBooking($updatedInventory)
    {
        $firstVal = 0;
        $fullDaysArray = [];
        if ($updatedInventory === null || count($updatedInventory) === 0) {
            return $fullDaysArray;
        }

        $mergedInventory = $this->mergedInventory($updatedInventory);

        if (!array_key_exists($firstVal, $mergedInventory) || $mergedInventory[$firstVal] === '') {
            return $fullDaysArray;
        }

        $fullDaysArray = $this->getFullDaysArray($updatedInventory, $firstVal, $fullDaysArray);
        $fullDaysArray = $this->getFullDaysArray($mergedInventory, $firstVal, $fullDaysArray);
        usort($fullDaysArray, function ($aVal, $bVal) {
            $diff = strtotime($aVal['s'] . ':00') - strtotime($bVal['s'] . ':00');
            return $diff;
        });
        return $fullDaysArray;
    }

    /**
     * Is a merger function but only if qtys are the same
     * compact like 3-7 7-9 became 3-9 but only if the qty is the same
     *
     * @param array $updatedInventory
     *
     * @return array
     */
    public function compactInventory($updatedInventory)
    {
        /**
         * first we sort the dates by start and end
         */
        usort($updatedInventory, function ($aVal, $bVal) {
            $diff = strtotime($aVal['s'] . ':00') - strtotime($bVal['s'] . ':00');
            if ($diff !== 0) {
                return $diff;
            } else {
                return strtotime($aVal['e'] . ':00') - strtotime($bVal['e'] . ':00');
            }
        });
        $overlaps = false;

        for ($jVal = 0, $jValMax = count($updatedInventory); $jVal < $jValMax - 1; $jVal++) {
            for ($iVal = $jVal + 1, $iValMax = count($updatedInventory); $iVal < $iValMax; $iVal++) {

                /** @var Period $reservationPeriod */
                $reservationPeriodStart = new Period(
                    $updatedInventory[$jVal]['s'] . ':00',
                    $updatedInventory[$jVal]['e'] . ':00'
                );
                /** @var Period $reservationPeriod */
                $reservationPeriodEnd = new Period(
                    $updatedInventory[$iVal]['s'] . ':00',
                    $updatedInventory[$iVal]['e'] . ':00'
                );

                if ($updatedInventory[$iVal]['q'] === $updatedInventory[$jVal]['q'] && $reservationPeriodStart->abuts($reservationPeriodEnd)) {
                    $reservationPeriodEnd = $reservationPeriodStart->merge($reservationPeriodEnd);
                    $updatedInventory[] = [
                        'q' => $updatedInventory[$iVal]['q'],
                        's' => $reservationPeriodEnd->getStartDate()->format('Y-m-d H:i'),
                        'e' => $reservationPeriodEnd->getEndDate()->format('Y-m-d H:i'),
                    ];
                    $overlaps = true;
                    break;
                }
            }
            if ($overlaps) {
                break;
            }
        }

        if ($overlaps) {
            unset($updatedInventory[$jVal]);
            unset($updatedInventory[$iVal]);
            $updatedInventory = $this->compactInventory($updatedInventory);
        }
        return $updatedInventory;
    }

    /**
     * Is needed because we want unique intervals
     *
     * @param array $updatedInventory
     *
     * @return array
     */
    public function normalizeInventory($updatedInventory)
    {
        /**
         * first we sort the dates by start and end
         */
        usort($updatedInventory, function ($aVal, $bVal) {
            $diff = strtotime($aVal['s'] . ':00') - strtotime($bVal['s'] . ':00');
            if ($diff !== 0) {
                return $diff;
            } else {
                return strtotime($aVal['e'] . ':00') - strtotime($bVal['e'] . ':00');
            }
        });
        $overlaps = false;
        /**
         * we search for 2 intersecting dates we create the intervals and we exclude them.
         * The idea is to have unique intervals
         * For example we have start-> 9 end-> 13 and start->9 and end->11.
         * the intersection will give start->9 end->11 and start->11 end->13 .2 unique intervals
         */
        $keyDel = -1;
        for ($jVal = 0, $jValMax = count($updatedInventory); $jVal < $jValMax - 1; $jVal++) {
            for ($iVal = $jVal + 1, $iValMax = count($updatedInventory); $iVal < $iValMax; $iVal++) {
                if ($updatedInventory[$jVal]['q'] <= 0) {
                    $keyDel = $jVal;
                    break;
                }
                if ($updatedInventory[$iVal]['q'] <= 0) {
                    $keyDel = $iVal;
                    break;
                }
                /** @var Period $reservationPeriod */
                $reservationPeriodStart = new Period(
                    $updatedInventory[$jVal]['s'] . ':00',
                    $updatedInventory[$jVal]['e'] . ':00'
                );
                /** @var Period $reservationPeriod */
                $reservationPeriodEnd = new Period(
                    $updatedInventory[$iVal]['s'] . ':00',
                    $updatedInventory[$iVal]['e'] . ':00'
                );

                if ($reservationPeriodStart->overlaps($reservationPeriodEnd)) {
                    $qty = $updatedInventory[$jVal]['q'];//maybe needs a check to see if both qtys are the same

                    /** @var Period $intersectionPeriod */
                    $intersectionPeriod = $reservationPeriodStart->intersect($reservationPeriodEnd);

                    $updatedInventory[] = [
                        'q' => $qty,
                        's' => $intersectionPeriod->getStartDate()->format('Y-m-d H:i'),
                        'e' => $intersectionPeriod->getEndDate()->format('Y-m-d H:i'),
                    ];

                    /**
                     * We make the difference of the periods for the non overlapping ones
                     * The reserved qtys will be the same
                     */
                    $diffPeriodArray = $reservationPeriodStart->diff($reservationPeriodEnd);
                    /** @var Period $diffPeriod */
                    foreach ($diffPeriodArray as $diffPeriod) {
                        $qty = $updatedInventory[$iVal]['q'];
                        if ($reservationPeriodStart->overlaps($diffPeriod)) {
                            $qty = $updatedInventory[$jVal]['q'];
                        }
                        $updatedInventory[] = [
                            'q' => $qty,
                            's' => $diffPeriod->getStartDate()->format('Y-m-d H:i'),
                            'e' => $diffPeriod->getEndDate()->format('Y-m-d H:i'),
                        ];
                    }
                    $overlaps = true;
                    break;
                }
            }
            if ($overlaps) {
                break;
            }
        }
        if ($keyDel !== -1) {
            unset($updatedInventory[$keyDel]);
            $updatedInventory = $this->normalizeInventory($updatedInventory);
        }
        if (count($updatedInventory) === 1 && $updatedInventory[0]['q'] <= 0) {
            unset($updatedInventory[0]);
        }
        if ($overlaps) {
            unset($updatedInventory[$jVal]);
            unset($updatedInventory[$iVal]);
            $updatedInventory = $this->normalizeInventory($updatedInventory);
        }
        return $updatedInventory;
    }

    /**
     * Function needed mostly for testing
     *
     * @param             $productId
     * @param string|null $inventory
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function resetInventory($productId, $inventory = null)
    {
        if (null !== $inventory) {
            $inventory = serialize($inventory);
        }
        foreach ($this->rentalHelper->getStoreIdsForCurrentWebsite() as $storeId) {
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                return [];
            }
            $product->setSirentInvBydateSerialized($inventory);
            $this->attributeAction->updateAttributes(
                [$product->getId()],
                ['sirent_inv_bydate_serialized' => $inventory],
                $storeId
            );
        }
    }

    /**
     * Function to get the full day from start hour until end hour
     * reservationPeriod is the start period basically it adds iVal days
     * to a date and checks its start and end hours and return the Period Object
     *
     * @param $reservationPeriod
     *
     * @param $iVal
     *
     * @return \League\Period\Period
     */
    private function getNextPeriod($reservationPeriod, $iVal)
    {
        $startDateInitial = strtotime('+' . $iVal . ' DAY',
            strtotime($reservationPeriod->getStartDate()->format('Y-m-d'))
        );
        $dateTimeInitial = new \DateTime('@' . $startDateInitial);
        $storeHoursPeriod = $this->calendarHelper->storeHoursForDate($dateTimeInitial);
        $startDate = new \DateTime($dateTimeInitial->format('Y-m-d') . ' ' . $storeHoursPeriod['start'] . ':00');
        $endDate = new \DateTime($dateTimeInitial->format('Y-m-d') . ' ' . $storeHoursPeriod['end'] . ':00');

        /** @var Period $reservationPeriod2 */
        $nextPeriod = new Period(
            $startDate, $endDate
        );
        return $nextPeriod;
    }

    /**
     * This function is used to check if a interval is really continuous so if it has
     * any whole then it means is not a full day. like 8-12pm 13-5pm is not continuos it returns only the days which a fully continuous.
     *
     * @param array $updatedInventory
     * @param       $firstVal
     * @param       $fullDaysArray
     *
     * @return array
     */
    protected function getFullDaysArray($updatedInventory, $firstVal, $fullDaysArray)
    {
        while ($firstVal < count($updatedInventory)) {
            /** @var Period $reservationPeriod */
            $reservationPeriod = new Period(
                $updatedInventory[$firstVal]['s'] . ':00',
                $updatedInventory[$firstVal]['e'] . ':00'
            );
            $iVal = 0;
            $nextPeriod = $this->getNextPeriod($reservationPeriod, $iVal);
            while ($nextPeriod->getStartDate() < $reservationPeriod->getEndDate()) {
                if ($reservationPeriod->contains($nextPeriod)) {
                    $dateFormatted = $nextPeriod->getStartDate()->format('Y-m-d H:i');
                    $key = array_search($dateFormatted, array_column($fullDaysArray, 's'));
                    if ($key === false) {
                        $fullDaysArray[] = [
                            's' => $dateFormatted,
                            'q' => $updatedInventory[$firstVal]['q'],
                        ];
                    }
                }
                $iVal++;
                $nextPeriod = $this->getNextPeriod($reservationPeriod, $iVal);
            }

            $firstVal++;
        }
        return $fullDaysArray;
    }

    /**
     * Inventory Configurations
     */

    /**
     * Setting for reserving inventory when no invoice is issued
     *
     * @return bool
     */
    public function reserveInventoryWithoutOrderInvoiced()
    {
        $statuses = $this->reserveInventoryByStatus();
        return $statuses === 'noInvoice';
    }

    /**
     * Setting for reserving inventory when no invoice is issued
     *
     * @return bool
     */
    public function reserveInventoryWithOrderInvoiced()
    {
        $statuses = $this->reserveInventoryByStatus();
        return $statuses === 'withInvoice';
    }

    /**
     * Setting for reserving inventory starting from send date if that is earlier
     *
     * @return bool
     */
    public function reserveInventoryEarlySendDate()
    {
        return (bool)$this->scopeConfig->getValue(
            'salesigniter_rental/inventory/reserve_inventory_early_send_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Setting for reserving inventory when return date is earlier, end date will become return date
     *
     * @return bool
     */
    public function reserveInventoryEarlyReturnDate()
    {
        return (bool)$this->scopeConfig->getValue(
            'salesigniter_rental/inventory/reserve_inventory_early_return_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Setting for reserving inventory until return date
     *
     * @return bool
     */
    public function reserveInventoryUntilReturnDate()
    {
        return (bool)$this->scopeConfig->getValue(
            'salesigniter_rental/inventory/reserve_inventory_until_return_date',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Setting for reserving inventory when specific status
     *
     * @return bool | array
     */
    public function reserveInventoryByStatus()
    {
        $statuses = $this->scopeConfig->getValue(
            'salesigniter_rental/inventory/reserve_inventory_status',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $statuses;
    }
}
