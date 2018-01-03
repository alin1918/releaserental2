<?php

namespace SalesIgniter\Rental\Plugin\Catalog;

use Magento\Framework\View\Page\Config\Reader\Html;
use SalesIgniter\Rental\Model\Config\GlobalDatesPricingOnListing;

/**
 * Class Template.
 *
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.OverallComplexity)
 */
class Template {
	/**
	 * @var \SalesIgniter\Rental\Helper\Data
	 */
	protected $_helperRental;
	/**
	 * @var \Magento\Framework\View\LayoutInterface
	 */
	private $layout;

	/**
	 * @var \Magento\Framework\Registry
	 */
	private $coreRegistry;
	/**
	 * @var \SalesIgniter\Rental\Helper\Calendar
	 */
	private $helperCalendar;

	/**
	 * @param \SalesIgniter\Rental\Helper\Data        $helperRental
	 * @param \SalesIgniter\Rental\Helper\Calendar    $helperCalendar
	 * @param \Magento\Framework\View\LayoutInterface $layout
	 * @param \Magento\Framework\Registry             $coreRegistry
	 */
	public function __construct(
		\SalesIgniter\Rental\Helper\Data $helperRental,
		\SalesIgniter\Rental\Helper\Calendar $helperCalendar,
		\Magento\Framework\View\LayoutInterface $layout,
		\Magento\Framework\Registry $coreRegistry
	) {
		$this->_helperRental  = $helperRental;
		$this->layout         = $layout;
		$this->coreRegistry   = $coreRegistry;
		$this->helperCalendar = $helperCalendar;
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \InvalidArgumentException
	 *
	 * @internal param string $dom
	 *
	 * @throws \QueryPath
	 */
	private function _renameButtons( $domHtml, &$isChanged ) {
		$this->renameButtonsOnProductPage( $domHtml, $isChanged );
		$this->renameButtonsOnListing( $domHtml, $isChanged );
	}

	/**
	 * Function which add the pricing blocks and styles.
	 *
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 *
	 * @return string
	 */
	private function _addPricingJs( $dom ) {
		if ( $this->helperCalendar->globalDatesPricingOnListing() !== GlobalDatesPricingOnListing::NORMAL ) {
			/** @var \SalesIgniter\Rental\Block\Footer\Pricingppr $block */
			$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Footer\Pricingppr' );
			$block->toHtml();
		}
	}

	/**
	 * Function which gets the calendar widget instance.
	 *
	 * @param string $area
	 *
	 * @return string
	 */
	public function getCalendar( $area = \Magento\Framework\App\Area::AREA_FRONTEND ) {
		/***
		 * Because of Magento caching mechanism is impossible to create a widget with dynamic data without using ajax
		 * even the fact that a block is non-cacheable means is loaded by ajax. So the only solution is to load
		 * the block by ajax, I think is better we do it then let magento because I've seen some strange behaviour.
		 */

		/** @var \SalesIgniter\Rental\Block\Widget\CalendarWidget $block */
		$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Widget\CalendarWidget' );
		$block->setArea( $area );
		$block->setTemplate( 'SalesIgniter_Rental::widgets/calendar.phtml' );

		return $block->toHtml();
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function _hideStartEndCustomOptions( $dom, &$isChanged ) {
		$hasStartEndDate = false;
		$nodes           = $dom->querySelectorAll( '.field' );
		/** @var \IvoPetkov\HTML5DOMElementNodeList $nodes */
		foreach ( $nodes as $node ) {
			$nodeLabels = $node->querySelectorAll( '.label' );
			$this->addHiddenClasses( $isChanged, $nodeLabels, $node, $hasStartEndDate );
			$nodeLabels = $node->querySelectorAll( '.legend' );
			$this->addHiddenClasses( $isChanged, $nodeLabels, $node, $hasStartEndDate );
		}

		$isBundle = $dom->querySelectorAll( '.fieldset-bundle-options' )->item( 0 );
		if ( $hasStartEndDate && ! is_object( $isBundle ) ) {
			$html     = $this->getCalendar();
			$fragment = $dom->ownerDocument->createDocumentFragment();
			$fragment->appendXML( $html );
			$dom->querySelectorAll( '.date' )->item( 0 )->parentNode->appendChild( $fragment );
			$isChanged = true;
		}
		if ( ! $hasStartEndDate && ! is_object( $isBundle ) ) {
			$bundleFields = $dom->querySelectorAll( '.bundle-info' )->item( 0 );
			if ( is_object( $bundleFields ) ) {
				$html     = $this->getCalendar();
				$fragment = $dom->ownerDocument->createDocumentFragment();
				$fragment->appendXML( $html );
				$bundleFields->appendChild( $fragment );
				$isChanged = true;
			}
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 *
	 * @return string
	 */
	private function _hideStartEndCustomOptionsAdmin( $dom, &$isChanged ) {
		$nodes = $dom->querySelectorAll( '.field' );
		/** @var \IvoPetkov\HTML5DOMElementNodeList $nodes */
		foreach ( $nodes as $node ) {
			$nodeLabels = $node->querySelectorAll( '.label' );
			$this->addHiddenClasses( $isChanged, $nodeLabels, $node, $hasStartEndDate );
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 *
	 * @return string
	 */
	private function _appendCalendarAdmin( $dom ) {
		$html     = $this->getCalendar( \Magento\Framework\App\Area::AREA_ADMINHTML );
		$fragment = $dom->ownerDocument->createDocumentFragment();
		$fragment->appendXML( $html );
		$dom->insertBefore( $fragment, $dom->firstChild );
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 *
	 * @return string
	 */
	private function _appendAdminCreateOrderUpdate( $dom ) {
		$html     = '<script>
            require(["sirentcreateorder"], function(){
                
            });
            </script>';
		$fragment = $dom->ownerDocument->createDocumentFragment();
		$fragment->appendXML( $html );
		$dom->appendChild( $fragment );
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _appendFrontendGeneralStyles( $dom ) {
		$html     = '<script>
            require(["css!css/general/styles.min"], function(){
                
            });
            </script>';
		$fragment = $dom->ownerDocument->createDocumentFragment();
		$fragment->appendXML( $html );
		$dom->appendChild( $fragment );
	}

	/**
	 * Function to hide custom options.
	 *
	 * @param                            $subject
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	private function hideCustomOptions( $subject, $domHtml, &$isChanged ) {
		if ( ( $subject->getNameInLayout() === 'product.info.options.wrapper' &&
		       $this->_helperRental->isFrontend() &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) ) ||
		     ( $subject->getNameInLayout() === 'bundle.summary' &&
		       $this->_helperRental->isFrontend() &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) )
		) {
			$this->_hideStartEndCustomOptions( $domHtml, $isChanged );
		}
		if ( $this->_helperRental->isBackend() && strpos( $subject->getNameInLayout(), 'product.composite.fieldset' ) !== false ) {
			$this->_hideStartEndCustomOptionsAdmin( $domHtml, $isChanged );
		}
	}

	/**
	 * Function to add pricing and stylesheets.
	 *
	 * @param                            $subject
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function addPricingAndStylesheets( $subject, $domHtml, &$isChanged ) {
		if ( $subject->getNameInLayout() === 'absolute_footer' && $this->_helperRental->isFrontend() ) {
			$this->_addPricingJs( $domHtml );
			$this->_appendFrontendGeneralStyles( $domHtml );
			$isChanged = true;
		}
	}

	private function cleanHtml( $html ) {
		$htmlCleaned = html5qp( '<div class="si_generated_div">' . $html . '</div>' );

		return $htmlCleaned->find( 'div.si_generated_div' )->first()->innerHTML();
	}

	/**
	 * Function to take care of removing Dates in emails and add the dates block.
	 *
	 * @param                            $subject
	 * @param                            $fileName
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @return mixed|string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	private function addDates( $subject, $fileName, $domHtml, &$isChanged ) {
		if ( strpos( $subject->getNameInLayout(), 'items' ) !== false || strpos( $subject->getNameInLayout(), 'checkout.cart.item.renderers' ) !== false ) {
			//$this->_translateOptionsPerItem( $domHtml, $isChanged );
		}
		if ( $subject->getNameInLayout() === 'items' && strpos( $fileName, 'email' ) !== false ) {
			$this->_addDatesInfoEmail( $domHtml, $subject->getOrder() );
			$this->_removeStartEndDatesPerItem( $domHtml, $isChanged );
		}

		if ( $subject->getNameInLayout() === 'order_info' && $this->_helperRental->isBackend() ) {
			$this->_addDatesInfo( $domHtml );
			$isChanged = true;
		}

		if ( $this->_helperRental->isBackend() &&
		     $this->helperCalendar->isSameDayOrder() &&
		     ( $subject->getNameInLayout() === 'order_items' ||
		       $subject->getNameInLayout() === 'shipment_items' ||
		       $subject->getNameInLayout() === 'creditmemo_items' )
		) {
			$this->_removeStartEndDatesPerItem( $domHtml, $isChanged );
		}
	}

	/**
	 * Retrieve block view from file (template).
	 *
	 * @param \Magento\Framework\View\Element\Template $subject
	 * @param \Closure                                 $proceed
	 * @param string                                   $fileName
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	public function aroundFetchView(
		\Magento\Framework\View\Element\Template $subject,
		\Closure $proceed,
		$fileName
	) {
		$html = $proceed( $fileName );

		if ( $this->_helperRental->isPaymentResponse() ) {
			return $html;
		}
		$scripts = $this->Translate_DoHTML_GetScripts( $html );
		$myhtml  = $scripts['body'];
		// $myhtml = $html;
		$dom = new \IvoPetkov\HTML5DOMDocument();
		$dom->loadHTML( '<div class="si_generated_local">' . $myhtml . '</div>' );
		$isChanged = false;
		/* @var \IvoPetkov\HTML5DOMNodeList $domHtmllist */
		$domHtmllist = $dom->querySelectorAll( 'div.si_generated_local' );
		$domHtml     = $domHtmllist->item( 0 );
		$this->renameButtonsOnListingAndProductPage( $fileName, $domHtml, $isChanged );

		$this->hideCustomOptions( $subject, $domHtml, $isChanged );
		$this->addPricingAndStylesheets( $subject, $domHtml, $isChanged );

		$this->addCalendarAdmin( $subject, $domHtml, $isChanged );
		$this->addDates( $subject, $fileName, $domHtml, $isChanged );

		$this->orderViewUpdate( $subject, $domHtml, $isChanged );
		$this->modifyShipPage( $subject, $domHtml, $isChanged );

		//todo for shipment view show serials. check regex https://simple-regex.com/build/580477fa34714
		if ( $isChanged ) {
			return $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML, $scripts['scripts'] );
			//return $domHtml->innerHTML();
		} else {
			return $html;
		}
	}

	private function nextElement( $node, $name = null ) {
		if ( ! $node ) {
			return null;
		}
		$next = $node->nextSibling;
		if ( ! $next ) {
			return null;
		}
		if ( $next->nodeType === 3 ) {
			return $this->nextElement( $next, $name );
		}
		if ( $name && $next->nodeName !== $name ) {
			return null;
		}

		return $next;
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	private function _removeStartEndDatesPerItem( $dom, &$isChanged ) {
		$dateField = $dom->getElementsByTagName( 'dl' )->item( 0 );
		if ( is_object( $dateField ) && strpos( $dateField->getAttribute( 'class' ), 'item-options' ) !== false ) {
			$dateFields = $dateField->getElementsByTagName( 'dt' );
			foreach ( $dateFields as $dateField ) {
				$this->removeDateField( $isChanged, $dateField );
			}
		}
		$dateField = $dom->getElementsByTagName( 'dl' )->item( 0 );
		if ( is_object( $dateField ) && strpos( $dateField->getAttribute( 'class' ), 'item-options' ) !== false ) {
			$dateFields = $dateField->getElementsByTagName( 'dt' );
			foreach ( $dateFields as $dateField ) {
				$this->removeDateField( $isChanged, $dateField );
			}
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	private function _translateOptionsPerItem( $dom, &$isChanged ) {
		$dateField = $dom->getElementsByTagName( 'dl' )->item( 0 );
		if ( is_object( $dateField ) && strpos( $dateField->getAttribute( 'class' ), 'item-options' ) !== false ) {
			$dateFields = $dateField->getElementsByTagName( 'dt' );

			/** @var \IvoPetkov\HTML5DOMElement $dateField */
			foreach ( $dateFields as $dateField ) {
				$legendText = $dateField->textContent;
				if ( $legendText == 'End Date::' || $legendText == 'Start Date::' || $legendText == 'End Date:' || $legendText == 'Start Date:' ) {
					$legendText             = rtrim( $legendText, ':' );
					$dateField->textContent = __( $legendText );
					$isChanged              = true;
				}
				if ( $legendText == 'Rental Buyout::' || $legendText == 'Damage Waiver::' || $legendText == 'Rental Buyout:' || $legendText == 'Damage Waiver:' ) {
					$legendText             = rtrim( $legendText, ':' );
					$dateField->textContent = __( $legendText );
					$isChanged              = true;
				}
				if ( $legendText == 'Pickup Warehouse::' || $legendText == 'Dropoff Warehouse::' || $legendText == 'Pickup Warehouse:' || $legendText == 'Dropoff Warehouse:' ) {
					$legendText             = rtrim( $legendText, ':' );
					$dateField->textContent = __( $legendText );
					$isChanged              = true;
				}
			}
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\InputException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	private function _addDatesInfo( $dom ) {
		if ( $this->helperCalendar->isSameDayOrder() ) {
			/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info $block */
			$block    = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info' );
			$fragment = $dom->ownerDocument->createDocumentFragment();
			$fragment->appendXML( $block->toHtml() );
			$dom->appendChild( $fragment );
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 *
	 * @return string
	 */
	private function _addReturnGridPanel( $dom ) {

		/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel $block */
		$block    = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel' );
		$fragment = $dom->ownerDocument->createDocumentFragment();
		$fragment->appendXML( $block->toHtml() );
		$dom->insertBefore( $fragment, $dom->firstChild );
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param null                       $order
	 *
	 * @return string
	 */
	private function _addDatesInfoEmail( $dom, $order = null ) {
		if ( $this->helperCalendar->isSameDayOrder( $order ) ) {
			/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail $block */
			$block    = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail', '', [ 'data' => [ 'hasOrder' => $order ] ] );
			$fragment = $dom->ownerDocument->createDocumentFragment();
			$fragment->appendXML( $block->toHtml() );
			$dom->insertBefore( $fragment, $dom->firstChild );
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $node
	 * @param                            $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \QueryPath
	 */
	private function _addSerialsInput( $node, &$isChanged ) {
		/*
		 * Because is using PHP 7 we will wait on implementing this. But still is easier to use and should be adopted
		 * link to SRL: https://simple-regex.com/build/57f64c613e77f
		 * todo shipment view -> check regex https://simple-regex.com/build/580477fa34714
		 */
		/* @var array $matches */
		preg_match( '/(?:shipment\[items]\[)(?<orderitem>[0-9]+)(?:])/', $node->getAttribute( 'name' ), $matches );

		/** @var array $productsData */
		$productsData = [];
		if ( array_key_exists( 'orderitem', $matches ) ) {
			$orderItem = $matches['orderitem'];
			if ( $this->_helperRental->isRentalType( $this->_helperRental->getProductIdFromOrderItem( $orderItem ) ) ) {
				$productsData = $this->_helperRental->getProductIdsFromOrderItem( $orderItem );
			}
		}
		/** @var array $product */
		foreach ( $productsData as $product ) {
			if ( $this->_helperRental->isSerialEnabledForProduct( $product['product_id'] ) ) {
				/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment\Serials $block */
				$block    = $this->layout->createBlock(
					'\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\Shipment\Serials',
					'serial_select_' . $product['order_item_id'],
					[
						'data' => [
							'product_id' => $product['product_id'],
							'item_id'    => $product['order_item_id'],
							'qty_value'  => $product['qty'],
						],
					]
				);
				$fragment = $node->ownerDocument->createDocumentFragment();
				$fragment->appendXML( $block->toHtml() );
				$node->parentNode->appendChild( $fragment );

				$isChanged = true;
			}
		}

		//return $dom->html();
	}

	/**
	 * @param                            $fileName
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \QueryPath
	 *
	 * @internal param $html
	 */
	protected function renameButtonsOnListingAndProductPage( $fileName, $domHtml, &$isChanged ) {
		if ( $this->_helperRental->isFrontend() &&
		     ( strpos( $fileName, 'addtocart.phtml' ) !== false ||
		       strpos( $fileName, 'list.phtml' ) !== false )
		) {
			$this->_renameButtons( $domHtml, $isChanged );
		}
	}

	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \QueryPath
	 */
	private function renameButtonsOnProductPage( $dom, &$isChanged ) {
		if ( $this->coreRegistry->registry( 'current_product' ) ) {
			$product = $this->coreRegistry->registry( 'current_product' );
			if ( $this->_helperRental->isRentalType( $product->getId() ) ) {
				$buttons = $dom->querySelectorAll( '.tocart' );

				/** @var \IvoPetkov\HTML5DOMElement $button */
				foreach ( $buttons as $button ) {
					$dataType = $this->_helperRental->isBuyout( $product->getId() ) ? 'rental-buyout' : '';
					/** @var \IvoPetkov\HTML5DOMElement $span */
					$span              = $button->getElementsByTagName( 'span' )->item( 0 );
					$span->textContent = __( 'Rent' );

					if ( $dataType == 'rental-buyout' ) {
						$newButton = $button->cloneNode();
						/* @var \IvoPetkov\HTML5DOMElement $newButton */
						$newButton->setAttribute( 'title', 'Buyout' );
						$button->removeAttr( 'xmlns' );
						$newButton->setAttribute( 'name', 'is_buyout' );
						$newButton->setAttribute( 'class', $newButton->getAttribute( 'class' ) . ' rental-buyout' );
						if ( $newButton->getElementsByTagName( 'span' )->item( 0 ) ) {
							$newButton->getElementsByTagName( 'span' )->item( 0 )->textContent = __( 'Buyout' );
						} else {
							$newButton->textContent = __( 'Buyout' );
						}

						$button->parentNode->appendChild( $newButton );
					}
					$isChanged = true;
				}
			}
		}
	}


	/**
	 * @param \IvoPetkov\HTML5DOMElement $dom
	 * @param                            $isChanged
	 */
	private function renameButtonsOnListing( $dom, &$isChanged ) {
		/** @var \IvoPetkov\HTML5DOMElementNodeList $priceBoxs */
		$priceBoxs = $dom->querySelectorAll( '.pricing-ppr' );

		/** @var \IvoPetkov\HTML5DOMElement $priceBox */
		foreach ( $priceBoxs as $priceBox ) {
			/** @var string $dataType */
			$dataType = $priceBox->getAttribute( 'data-type' );

			/** @var \IvoPetkov\HTML5DOMElement $button */
			$button = $priceBox->parentNode->parentNode->parentNode->querySelectorAll( '.tocart' )->item( 0 );
			if ( $button === null ) {
				$button = $priceBox->parentNode->parentNode->parentNode->querySelectorAll( '.add-to-cart' )->item( 0 );

			}
			if ( $button ) {
				$span = $button->getElementsByTagName( 'span' )->item( 0 );

				$span->textContent = __( 'Rent' );
				if ( $dataType == 'rental-buyout' ) {
					$button->setAttribute( 'style', 'margin-top: 15px' );
					$button->parentNode->appendChild( $button );
					$newButton = $this->nextElement( $button );
					$newButton->setAttribute( 'title', 'Buyout' );
					$newButton->setAttribute( 'style', 'margin-top: 15px;' );
					$newButton->setAttribute( 'class', $newButton->getAttribute( 'class' ) . ' rental-buyout' );
					$newButton->getElementsByTagName( 'span' )->item( 0 )->textContent = __( 'Buyout' );
				}

				$isChanged = true;
			}
		}
	}

	/**
	 * @param                     $subject
	 * @param \QueryPath\DOMQuery $domHtml
	 * @param                     $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function addCalendarAdmin( $subject, $domHtml, &$isChanged ) {
		if ( ( $this->_helperRental->isBackendAdminOrderEdit() &&
		       $subject->getNameInLayout() === 'items' ) ||
		     ( $this->_helperRental->isBackend() &&
		       $subject->getNameInLayout() === 'product.composite.fieldset.options.js' &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) )
		) {
			$this->_appendCalendarAdmin( $domHtml );
			$isChanged = true;
		}

		if ( $this->_helperRental->isBackendAdminOrderEdit() && $subject->getNameInLayout() === 'items_grid' ) {
			$this->_appendAdminCreateOrderUpdate( $domHtml );
			$isChanged = true;
		}
	}

	/**
	 * Function to update order view with return grid panel.
	 *
	 * @param                            $subject
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param bool                       $isChanged
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function orderViewUpdate( $subject, $domHtml, &$isChanged ) {
		if ( $subject->getNameInLayout() === 'sales_order_edit-return_order-button' && $this->_helperRental->isBackend() ) {
			$this->_addReturnGridPanel( $domHtml );
			$isChanged = true;
		}
	}

	/**
	 * @param                            $subject
	 * @param \IvoPetkov\HTML5DOMElement $domHtml
	 * @param                            $isChanged
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \QueryPath
	 */
	private function modifyShipPage( $subject, $domHtml, &$isChanged ) {
		if ( $subject->getNameInLayout() === 'order_items' && $this->_helperRental->isBackend() ) {
			$nodes = $domHtml->querySelectorAll( '.col-qty' );

			/** @var \IvoPetkov\HTML5DOMElement $nodeQ */
			foreach ( $nodes as $nodeQ ) {
				$nodeList = $nodeQ->getElementsByTagName( 'input' );
				foreach ( $nodeList as $node ) {
					$this->_addSerialsInput( $node, $isChanged );
				}
			}
		}
	}

	/**
	 * @param                                    $isChanged
	 * @param \IvoPetkov\HTML5DOMElementNodeList $nodeLabels
	 * @param \IvoPetkov\HTML5DOMElement         $node
	 * @param                                    $hasStartEndDate
	 */
	private function addHiddenClasses( &$isChanged, $nodeLabels, $node, &$hasStartEndDate ) {
		$legendText = '';
		foreach ( $nodeLabels as $nodeLabel ) {
			$legendText = $nodeLabel->textContent;
			break;
		}
		if ( strpos( $legendText, 'End Date:' ) !== false || strpos( $legendText, 'Start Date:' ) !== false ) {
			$node->setAttribute( 'class', $node->getAttribute( 'class' ) . ' hiddenDates' );
			$node->setAttribute( 'style', 'display:none' );
			$hasStartEndDate = true;
			$isChanged       = true;
		}
		if ( strpos( $legendText, 'Rental Buyout:' ) !== false || strpos( $legendText, 'Damage Waiver:' ) !== false ) {
			$node->setAttribute( 'class', $node->getAttribute( 'class' ) . ' hiddenDates' );
			$node->setAttribute( 'style', 'display:none' );
			$isChanged = true;
		}

		if ( strpos( $legendText, 'Pickup Warehouse:' ) !== false || strpos( $legendText, 'Dropoff Warehouse:' ) !== false ) {
			$node->setAttribute( 'class', $node->getAttribute( 'class' ) . ' hiddenDates' );
			$node->setAttribute( 'style', 'display:none' );
			$isChanged = true;
		}
	}

	/**
	 * @param $isChanged
	 * @param $dateField
	 */
	private function removeDateField( &$isChanged, $dateField ) {
		/* @var \IvoPetkov\HTML5DOMElement $dateField */

		$legendText = $dateField->textContent;
		if ( $legendText == 'End Date' || $legendText == 'Start Date' || $legendText == 'End Date::' || $legendText == 'Start Date::' || $legendText == 'End Date:' || $legendText == 'Start Date:' ) {
			$nextElement = $this->nextElement( $dateField );
			if ( ! is_null( $nextElement ) ) {
				$nextElement->parentNode->removeChild( $nextElement );
			}
			$dateField->parentNode->removeChild( $dateField );
			$isChanged = true;
		}
	}

	/**
	 * @param                                    $isChanged
	 * @param \IvoPetkov\HTML5DOMElementNodeList $nodeLabels
	 * @param \IvoPetkov\HTML5DOMElement         $node
	 * @param                                    $hasStartEndDate
	 */
	private function Translate_DoHTML_GetScripts( $body ) {
		$res = array();
		if ( preg_match_all( '/<script\b[^>]*>([\s\S]*?)<\/script>/m', $body, $matches ) && is_array( $matches ) && isset( $matches[0] ) ) {
			foreach ( $matches[0] as $key => $match ) {
				$res[ '<!-- __SCRIPTBUGFIXER_PLACEHOLDER' . $key . '__ -->' ] = $match;
			}
			$body = str_ireplace( array_values( $res ), array_keys( $res ), $body );
		}

		return array( 'body' => $body, 'scripts' => $res );
	}

	private function Translate_DoHTML_SetScripts( $body, $scripts ) {
		return str_ireplace( array_keys( $scripts ), array_values( $scripts ), $body );
	}
}
