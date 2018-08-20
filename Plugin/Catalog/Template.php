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
	protected $cartClass = '.tocart, .btn-add-to-cart';
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
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \InvalidArgumentException
	 *
	 * @internal param string $dom
	 */
	private function _renameButtons( &$domHtml, &$isChanged ) {
		$this->renameButtonsOnProductPage( $domHtml, $isChanged );
		$this->renameButtonsOnListing( $domHtml, $isChanged );
	}

	/**
	 * Function which add the pricing blocks and styles.
	 *
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _addPricingJs( &$dom ) {
		if ( $this->helperCalendar->globalDatesPricingOnListing() !== GlobalDatesPricingOnListing::NORMAL ) {
			/** @var \SalesIgniter\Rental\Block\Footer\Pricingppr $block */
			$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Footer\Pricingppr' );
			$dom->append( $this->cleanHtml( $block->toHtml() ) );
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
	 * @param \QueryPath\DOMQuery $dom
	 * @param                     $isChanged
	 *
	 * @return string
	 *
	 * @throws \InvalidArgumentException
	 */
	private function _hideStartEndCustomOptions( &$dom, &$isChanged ) {
		$hasStartEndDate = false;
		$nodes           = $dom->find( '.field' );
		foreach ( $nodes as $node ) {
			$legendText = '';
			$nodeLabels = $node->find( '.label, .legend' );
			foreach ( $nodeLabels as $nodeLabel ) {
				$legendText = $nodeLabel->text();
				break;
			}
			if ( strpos( $legendText, 'End Date:' ) !== false || strpos( $legendText, 'Start Date:' ) !== false ) {
				$node->addClass( 'hiddenDates' );
				$node->attr( 'style', 'display:none' );
				$hasStartEndDate = true;
				$isChanged       = true;
			}
			if ( strpos( $legendText, 'Rental Buyout:' ) !== false || strpos( $legendText, 'Damage Waiver:' ) !== false ) {
				$node->addClass( 'hiddenDates' );
				$node->attr( 'style', 'display:none' );
				$isChanged = true;
			}
		}

		$isBundle = $dom->find( '.fieldset-bundle-options' )->first();
		if ( $hasStartEndDate && $isBundle->length === 0 ) {
			$html = $this->cleanHtml( $this->getCalendar() );
			$dom->find( '.date' )->first()->parent()->append( $html );
			$isChanged = true;
		}
		if ( ! $hasStartEndDate && $isBundle->length === 0 ) {
			$bundleFields = $dom->find( '.bundle-info' )->first();
			if ( is_object( $bundleFields ) ) {
				$html = $this->cleanHtml( $this->getCalendar() );
				$bundleFields->append( $html );
				$isChanged = true;
			}
		}
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 * @param                     $isChanged
	 *
	 * @return string
	 */
	private function _hideStartEndCustomOptionsAdmin( &$dom, &$isChanged ) {
		$nodes = $dom->find( '.field' );
		foreach ( $nodes as $node ) {
			$legendText = '';
			$nodeLabels = $node->find( '.label' );
			foreach ( $nodeLabels as $nodeLabel ) {
				$legendText = $nodeLabel->text();
				break;
			}

			if ( strpos( $legendText, 'End Date:' ) !== false || strpos( $legendText, 'Start Date:' ) !== false || strpos( $legendText, 'Rental Buyout:' ) !== false || strpos( $legendText, 'Damage Waiver:' ) !== false ) {
				$node->addClass( 'hiddenDates' );
				$isChanged = true;
			}
		}
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _appendCalendarAdmin( &$dom ) {
		$html = $this->cleanHtml( $this->getCalendar( \Magento\Framework\App\Area::AREA_ADMINHTML ) );
		$dom->prepend( $html );
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _appendAdminCreateOrderUpdate( &$dom ) {
		$html = '<script>
            require(["sirentcreateorder"], function(){
                
            });
            </script>';
		$html = $this->cleanHtml( $html );
		$dom->append( $html );
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _appendFrontendGeneralStyles( &$html ) {
		$html .= '<script>
            require(["css!css/general/styles.min"], function(){
                
            });
            </script>';
		//$html = $this->cleanHtml( $html );
		//$dom->append( $html );
	}

	/**
	 * Function to hide custom options.
	 *
	 * @param $subject
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 */
	private function hideCustomOptions( $subject, &$html ) {
		if ( ( $subject->getNameInLayout() === 'product.info.options.wrapper' &&
		       $this->_helperRental->isFrontend() &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) ) ||
		     ( $subject->getNameInLayout() === 'bundle.summary' &&
		       $this->_helperRental->isFrontend() &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) )
		) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_hideStartEndCustomOptions( $domHtml, $isChanged );
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
		if ( $this->_helperRental->isBackend() && strpos( $subject->getNameInLayout(), 'product.composite.fieldset' ) !== false ) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_hideStartEndCustomOptionsAdmin( $domHtml, $isChanged );
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
	}

	/**
	 * Function to add pricing and stylesheets.
	 *
	 * @param $subject
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function addPricingAndStylesheets( $subject, &$html ) {
		if ( $subject->getNameInLayout() === 'absolute_footer' && $this->_helperRental->isFrontend() ) {
			//$this->_addPricingJs( $domHtml );
			$this->_appendFrontendGeneralStyles( $html );
			$isChanged = true;
		}
	}

	private function cleanHtml( $html ) {
		$scripts     = $this->Translate_DoHTML_GetScripts( $html );
		$myhtml      = $scripts['body'];
		$htmlCleaned = html5qp( '<div class="si_generated_div">' . $myhtml . '</div>' );
		$domHtml     = $htmlCleaned->find( 'div.si_generated_div' )->first();

		return $this->Translate_DoHTML_SetScripts( $domHtml->innerXML(), $scripts['scripts'] );
	}

	/**
	 * Function to take care of removing Dates in emails and add the dates block.
	 *
	 * @param $subject
	 * @param $fileName
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @return mixed|string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \RuntimeException
	 * @throws \InvalidArgumentException
	 */
	private function addDates( $subject, $fileName, &$html ) {
		if ( $subject->getNameInLayout() === 'items' && strpos( $fileName, 'email' ) !== false ) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_addDatesInfoEmail( $domHtml, $subject->getOrder() );
			$this->_removeStartEndDatesPerItem( $domHtml, $isChanged );
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}

		if ( $subject->getNameInLayout() === 'order_info' && $this->_helperRental->isBackend() ) {
            
            $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/tondebug.log');
            $logger = new \Zend\Log\Logger();
            $logger->addWriter($writer);
            
            //$logger->info($html);            
            
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;

            $logger->info($scripts);                        
            //$logger->info($myhtml);            
            
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_addDatesInfo( $domHtml );
			$isChanged = true;
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}

		if ( $this->_helperRental->isBackend() &&
		     $this->helperCalendar->isSameDayOrder() &&
		     ( $subject->getNameInLayout() === 'order_items' ||
		       $subject->getNameInLayout() === 'shipment_items' ||
		       $subject->getNameInLayout() === 'creditmemo_items' )
		) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_removeStartEndDatesPerItem( $domHtml, $isChanged );
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
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
		$this->renameButtonsOnListingAndProductPage( $fileName, $html );

		$this->hideCustomOptions( $subject, $html );
		$this->addPricingAndStylesheets( $subject, $html );

		$this->addCalendarAdmin( $subject, $html );
		$this->addDates( $subject, $fileName, $html );

		$this->orderViewUpdate( $subject, $html );
		$this->modifyShipPage( $subject, $html );

		//todo for shipment view show serials. check regex https://simple-regex.com/build/580477fa34714

		return $html;

	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 * @param                     $isChanged
	 *
	 * @return mixed
	 *
	 * @throws \RuntimeException
	 */
	private function _removeStartEndDatesPerItem( $dom, &$isChanged ) {
		$dateField = $dom->find( 'dl' )->first();
		if ( is_object( $dateField ) && $dateField->hasClass( 'item-options' ) ) {
			$dateFields = $dateField->find( 'dt' );

			/** @var \QueryPath\DOMQuery $dateField */
			foreach ( $dateFields as $dateField ) {
				$legendText = $dateField->text();
				if ( $legendText == 'End Date::' || $legendText == 'Start Date::' || $legendText == 'End Date:' || $legendText == 'Start Date:' ) {
					$dateField->next()->remove();
					$dateField->remove();
					$isChanged = true;
				}
			}
		}
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 *
	 * @return string
	 */
	private function _addDatesInfo( $dom ) {
		if ( $this->helperCalendar->isSameDayOrder() ) {
			/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info $block */
			$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\Info' );
			$html  = $this->cleanHtml( $block->toHtml() );
			$dom->append( $html );
		}
	}

	/**
	 * @param $dom
	 *
	 * @return string
	 */
	private function _addReturnGridPanel( $dom ) {

		/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel $block */
		$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\ReturnGridPanel' );
		$html  = $this->cleanHtml( $block->toHtml() );
		$dom->prepend( $html );
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 * @param null                $order
	 *
	 * @return string
	 */
	private function _addDatesInfoEmail( $dom, $order = null ) {
		if ( $this->helperCalendar->isSameDayOrder( $order ) ) {
			/** @var \SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail $block */
			$block = $this->layout->createBlock( '\SalesIgniter\Rental\Block\Adminhtml\Sales\Order\View\InfoEmail', '', [ 'data' => [ 'hasOrder' => $order ] ] );
			$html  = $this->cleanHtml( $block->toHtml() );
			$dom->prepend( $html );
		}
	}

	/**
	 * @param \QueryPath\DOMQuery $node
	 * @param                     $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function _addSerialsInput( $node, &$isChanged ) {
		/*
		 * Because is using PHP 7 we will wait on implementing this. But still is easier to use and should be adopted
		 * link to SRL: https://simple-regex.com/build/57f64c613e77f
		 */
		/* @var array $matches */
		preg_match( '/(?:shipment\[items]\[)(?<orderitem>[0-9]+)(?:])/', $node->attr( 'name' ), $matches );

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
				$block = $this->layout->createBlock(
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
				$html  = $this->cleanHtml( $block->toHtml() );
				$node->parents()->eq( 0 )->append( $html );
				$isChanged = true;
			}
		}

		//return $dom->html();
	}

	/**
	 * @param $fileName
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @return string
	 *
	 * @throws \RuntimeException
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \InvalidArgumentException
	 *
	 * @internal param $html
	 */
	protected function renameButtonsOnListingAndProductPage( $fileName, &$html ) {
		if ( $this->_helperRental->isFrontend() &&
		     ( strpos( $fileName, 'addtocart.phtml' ) !== false ||
		       strpos( $fileName, 'list.phtml' ) !== false )
		) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();
			$this->_renameButtons( $domHtml, $isChanged );
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
	}

	/**
	 * @param \QueryPath\DOMQuery $dom
	 * @param                     $isChanged
	 *
	 * @return string
	 *
	 * @throws \Magento\Framework\Exception\NoSuchEntityException
	 * @throws \InvalidArgumentException
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function renameButtonsOnProductPage( $dom, &$isChanged ) {
		if ( $this->coreRegistry->registry( 'current_product' ) ) {
			$product = $this->coreRegistry->registry( 'current_product' );
			if ( $this->_helperRental->isRentalType( $product->getId() ) ) {
				$buttons = $dom->find( $this->cartClass );

				/** @var \QueryPath\DOMQuery $button */
				foreach ( $buttons as $button ) {
					$dataType = $this->_helperRental->isBuyout( $product->getId() ) ? 'rental-buyout' : '';
					/** @var \QueryPath\DOMQuery $span */
					$span = $button->find( 'span' )->first();
					$span->text( __( 'Rent' ) );

					if ( $dataType == 'rental-buyout' ) {
						$newButton = clone $button;
						/* @var \QueryPath\DOMQuery $newButton */
						$newButton->attr( 'title', 'Buyout' );
						$button->removeAttr( 'xmlns' );
						$newButton->attr( 'name', 'is_buyout' );
						$newButton->addClass( 'rental-buyout' );
						if ( $newButton->find( 'span' )->first()->length > 0 ) {
							$newButton->find( 'span' )->first()->text( __( 'Buyout' ) );
						} else {
							$newButton->text( __( 'Buyout' ) );
						}

						$button->parent()->append( $newButton );
					}
					$isChanged = true;
				}
			}
		}
	}

	/**
	 * @param $dom
	 * @param $isChanged
	 */
	private function renameButtonsOnListing( $dom, &$isChanged ) {
		/** @var \QueryPath\DOMQuery $priceBoxs */
		$priceBoxs = $dom->find( '.pricing-ppr' );

		/** @var \QueryPath\DOMQuery $priceBox */
		foreach ( $priceBoxs as $priceBox ) {
			/** @var string $dataType */
			$dataType = $priceBox->attr( 'data-type' );

			/** @var \QueryPath\DOMQuery $button */
			$button = $priceBox->parent()->parent()->parent()->find( $this->cartClass )->first();
			$span   = $button->find( 'span' )->first();

			$span->text( __( 'Rent' ) );
			if ( $dataType == 'rental-buyout' ) {
				$button->attr( 'style', 'margin-top: 15px' );
				$button->parent()->append( $button );
				$newButton = $button->next();
				$newButton->attr( 'title', 'Buyout' );
				$newButton->attr( 'style', 'margin-top: 15px;' );
				$newButton->addClass( 'rental-buyout' );
				$newButton->find( 'span' )->first()->text( __( 'Buyout' ) );
			}
			$isChanged = true;
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
	private function addCalendarAdmin( $subject, &$html ) {
		if ( ( $this->_helperRental->isBackendAdminOrderEdit() &&
		       $subject->getNameInLayout() === 'items' ) ||
		     ( $this->_helperRental->isBackend() &&
		       $subject->getNameInLayout() === 'product.composite.fieldset.options.js' &&
		       $this->_helperRental->isRentalType( $this->coreRegistry->registry( 'current_product' ) ) )
		) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_appendCalendarAdmin( $domHtml );
			$isChanged = true;
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}

		if ( $this->_helperRental->isBackendAdminOrderEdit() && $subject->getNameInLayout() === 'items_grid' ) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_appendAdminCreateOrderUpdate( $domHtml );
			$isChanged = true;
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
	}

	/**
	 * Function to update order view with return grid panel.
	 *
	 * @param $subject
	 * @param $domHtml
	 * @param $isChanged
	 */
	private function orderViewUpdate( $subject, &$html ) {
		if ( $subject->getNameInLayout() === 'sales_order_edit-return_order-button' && $this->_helperRental->isBackend() ) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$this->_addReturnGridPanel( $domHtml );
			$isChanged = true;
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
	}

	/**
	 * @param $subject
	 * @param $domHtml
	 * @param $isChanged
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 */
	private function modifyShipPage( $subject, &$html ) {
		if ( $subject->getNameInLayout() === 'order_items' && $this->_helperRental->isBackend() ) {
			$scripts = $this->Translate_DoHTML_GetScripts( $html );
			$myhtml  = $scripts['body'];
			// $myhtml = $html;
			$domHtmlModified = html5qp( '<div class="si_generated_local">' . $myhtml . '</div>' );
			$isChanged       = false;
			$domHtml         = $domHtmlModified->find( 'div.si_generated_local' )->first();

			$nodes = $domHtml->find( '.col-qty input' );
			foreach ( $nodes as $node ) {
				$this->_addSerialsInput( $node, $isChanged );
			}
			if ( $isChanged ) {
				$html = $this->Translate_DoHTML_SetScripts( $domHtml->innerHTML5(), $scripts['scripts'] );
				//return $domHtml->innerHTML();
			}
		}
	}

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
