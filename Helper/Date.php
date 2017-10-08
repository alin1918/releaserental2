<?php

namespace SalesIgniter\Rental\Helper;

use Magento\Catalog\Api\ProductRepositoryInterface;

/**
 * General Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Date extends \Magento\Framework\App\Helper\AbstractHelper
{

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
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $_resourceProduct;

    /**
     * @var   \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param \Magento\Framework\App\Helper\Context           $context
     * @param \Magento\Store\Model\StoreManagerInterface      $storeManager
     * @param \Magento\Catalog\Model\Session                  $catalogSession
     * @param \Magento\Catalog\Model\ResourceModel\Product    $resourceProduct
     * @param \Magento\Framework\Registry                     $coreRegistry
     * @param \Magento\Framework\App\State                    $appState
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Customer\Model\Session                 $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\State $appState,
        ProductRepositoryInterface $productRepository,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_storeManager = $storeManager;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->_resourceProduct = $resourceProduct;
        $this->_customerSession = $customerSession;
        $this->_appState = $appState;
        $this->_productRepository = $productRepository;

        parent::__construct($context);
    }

    /**
     * @param \DateTime $recurringDate
     * @param \DateTime $dateObj
     * @param int       $type
     *
     * @return bool
     */
    public function isRecurringDate($recurringDate, $dateObj, $type)
    {
        if ($type == 'none') {
            return $this->compareDateTimeObj($recurringDate, $dateObj, false) === 0;
        }
        if ($type == 'daily') {
            return ((int)$recurringDate->format('H') === (int)$dateObj->format('H') && (int)$recurringDate->format('i') === (int)$dateObj->format('i'));
        }
        if ($type == 'dayweek') {
            return ((int)$recurringDate->format('w') === (int)$dateObj->format('w'));
        }
        if ($type == 'monthly') {
            return ((int)$recurringDate->format('d') === (int)$dateObj->format('d'));
        }
        if ($type == 'yearly') {
            return ((int)$recurringDate->format('d') === (int)$dateObj->format('d') && (int)$recurringDate->format('m') === (int)$dateObj->format('m'));
        }

        return false;
    }

    /**
     * @param \DateTime $dateObj
     *
     * @param bool      $withoutTime
     *
     * @return \DateTime
     */
    public function getCloneDate($dateObj, $withoutTime = true)
    {
        if ($dateObj === null) {
            $dateObjNew = new \DateTime();
        } else {
            if (is_object($dateObj)) {
                $dateObjNew = new \DateTime($dateObj->format('Y-m-d H:i:s'), $dateObj->getTimezone());
            } else {
                $dateObjNew = new \DateTime($dateObj);
            }
        }
        if ($withoutTime) {
            $dateObjNew->setTime(0, 0, 0);
        }
        return $dateObjNew;
    }

    /**
     * @param \DateTime $recurringDateStart
     * @param \DateTime $recurringDateEnd
     * @param \DateTime $dateObj
     * @param int       $type
     *
     * @return bool
     */
    public function isRecurringDateBetween($recurringDateStart, $recurringDateEnd, $dateObj, $type)
    {
        if ($type == 'notime') {
            return ($this->compareDateTimeObj($recurringDateStart, $dateObj, false) === -1 ||
                    $this->compareDateTimeObj($recurringDateStart, $dateObj, false) === 0) &&
                ($this->compareDateTimeObj($recurringDateEnd, $dateObj, false) === 1 ||
                    $this->compareDateTimeObj($recurringDateEnd, $dateObj, false) === 0);
        }
        if ($type == 'none') {
            return $this->compareDateTimeObj($recurringDateStart, $dateObj, true) === -1 &&
                $this->compareDateTimeObj($recurringDateEnd, $dateObj, true) === 1;
        }
        if ($type == 'daily') {
            return ((int)$recurringDateStart->format('H') === (int)$dateObj->format('H') &&
                    (int)$recurringDateStart->format('i') <= (int)$dateObj->format('i') ||
                    (int)$recurringDateStart->format('H') < (int)$dateObj->format('H')) &&
                ((int)$recurringDateEnd->format('H') === (int)$dateObj->format('H') && (int)$recurringDateEnd->format('i') >= (int)$dateObj->format('i') ||
                    (int)$recurringDateEnd->format('H') > (int)$dateObj->format('H'));
        }
        if ($type == 'dayweek') {
            return (int)$recurringDateStart->format('w') >= (int)$dateObj->format('w') && (int)$recurringDateEnd->format('w') <= (int)$dateObj->format('w');
        }
        if ($type == 'monthly') {
            return (int)$recurringDateStart->format('d') >= (int)$dateObj->format('d') &&
                (int)$recurringDateEnd->format('d') <= (int)$dateObj->format('d');//todo check monthly datetime repeat
        }
        if ($type == 'yearly') {
            return ((int)$recurringDateStart->format('m') === (int)$dateObj->format('m') &&
                    (int)$recurringDateStart->format('d') <= (int)$dateObj->format('d') ||
                    (int)$recurringDateStart->format('m') < (int)$dateObj->format('m')) &&
                ((int)$recurringDateEnd->format('m') === (int)$dateObj->format('m') && (int)$recurringDateEnd->format('d') >= (int)$dateObj->format('d') ||
                    (int)$recurringDateEnd->format('m') > (int)$dateObj->format('m'));
        }

        return false;
    }

    /**
     * @param \DateTime $startA
     * @param \DateTime $endA
     * @param \DateTime $startB
     * @param \DateTime $endB
     *
     * @return bool
     */
    public function checkDatesOverlap($startA, $endA, $startB, $endB)
    {
        return ($startA < $endB) && ($endA > $startB);
    }

    /**
     *
     * @param \DateTime $dateObjStart
     * @param \DateTime $dateObjEnd
     * @param bool      $withTime
     *
     * @return int
     */
    public function compareDateTimeObj($dateObjStart, $dateObjEnd, $withTime)
    {
        $dateObjStartNew = $this->getCloneDate($dateObjStart, !$withTime);
        $dateObjEndNew = $this->getCloneDate($dateObjEnd, !$withTime);
        return $this->compareDates($dateObjStartNew, $dateObjEndNew);
    }

    /**
     * @param $dateObjStart
     * @param $dateObjEnd
     *
     * @return int
     */
    public function compareDates($dateObjStart, $dateObjEnd)
    {
        /** @noinspection TypeUnsafeComparisonInspection */
        if ($dateObjStart == $dateObjEnd) {
            return 0;
        }
        if ($dateObjStart > $dateObjEnd) {
            return 1;
        }
        if ($dateObjStart < $dateObjEnd) {
            return -1;
        }
    }

    /**
     * Returns interval number of seconds
     *
     * @param \DateInterval $interval
     *
     * @return mixed
     */
    public function intervalInSeconds($interval)
    {
        $reference = new \DateTimeImmutable();
        $endTime = $reference->add($interval);

        return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    /**
     * @param  string $interval
     *
     * @return \DateInterval
     * @throws \Exception
     */
    public function normalizeInterval($interval)
    {
        $periodArray = [
            'm' => 'PT',
            'h' => 'PT',
            'd' => 'P',
            'w' => 'P',
            'M' => 'P',
            'y' => 'P',
        ];
        $returnValue = new \DateInterval('P0D');
        $lastChar = substr($interval, -1);
        if (array_key_exists($lastChar, $periodArray)) {
            $returnValue = new \DateInterval($periodArray[$lastChar] . strtoupper($interval));
        }
        return $returnValue;
    }

    /**
     * @param $date
     * @param $locale
     */
    public function formatUTCDate($date, $locale)
    {
        //todo check for string and check for null locale
        $dateTime = new \DateTime($date);
        $dateTime->setTimezone(new \DateTimeZone('UTC'));
        $dateType = \IntlDateFormatter::SHORT;
        $timeType = \IntlDateFormatter::SHORT;
        $formatter = new \IntlDateFormatter(
            $locale,
            $dateType,
            $timeType
        );
        return $formatter->format($dateTime);
    }

    /**
     * @param  string | \DateInterval $intervalStart
     * @param  string | \DateInterval $intervalEnd
     * @param bool                    $normalizedStart
     * @param bool                    $normalizedEnd
     *
     * @return int
     * @throws \Exception
     */
    public function compareInterval($intervalStart, $intervalEnd, $normalizedStart = false, $normalizedEnd = false)
    {
        $dateTimeStart = $this->getCloneDate(null);
        $dateTimeEnd = $this->getCloneDate($dateTimeStart);

        if (!$normalizedStart) {
            $intervalStart = $this->normalizeInterval($intervalStart);
        } else {
            $intervalStart->invert = 0;
        }
        if (!$normalizedEnd) {
            $intervalEnd = $this->normalizeInterval($intervalEnd);
        } else {
            $intervalEnd->invert = 0;
        }
        $dateTimeStart->add($intervalStart);
        $dateTimeEnd->add($intervalEnd);

        return $this->compareDates($dateTimeStart, $dateTimeEnd);
    }

    /**
     * Convert given date to default (UTC) timezone
     *
     * @param int $date
     * @param int $hour
     * @param int $minute
     * @param int $second
     *
     * @return \DateTime|null
     */
    public function convertDate($date, $hour = 0, $minute = 0, $second = 0)
    {
        try {
            $dateObj = $this->localeDate->date(
                new \DateTime(
                    $date,
                    new \DateTimeZone($this->localeDate->getConfigTimezone())
                ),
                $this->getLocale(),
                true
            );
            $dateObj->setTime($hour, $minute, $second);
            //convert store date to default date in UTC timezone without DST
            $dateObj->setTimezone(new \DateTimeZone('UTC'));
            return $dateObj;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function isRecurringDateBetweenMultiple($dateStart, $dateEnd, $fromDate, $toDate, $recurring)
    {
        return $this->isRecurringDateBetween($fromDate, $toDate, $dateStart, $recurring) && $this->isRecurringDateBetween($fromDate, $toDate, $dateEnd, $recurring);
    }
}
