<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace SalesIgniter\Rental\Plugin\Model\Layout;

use Magento\Framework\View\Element\BlockFactory;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Framework\View\Layout\Element;
use Magento\PageCache\Model\DepersonalizeChecker;

/**
 * Class DepersonalizePlugin
 */
class DepersonalizePlugin
{
    /**
     * @var DepersonalizeChecker
     */
    protected $depersonalizeChecker;

    /**
     * @var \Magento\Catalog\Model\Session
     */
    protected $catalogSession;

    /**
     * @var string
     */
    protected $startDateGlobal;
    /**
     * @var string
     */
    protected $endDateGlobal;
    /**
     * @var \Magento\Framework\View\Layout\Data\Structure
     */
    private $structure;
    /**
     * @var \Magento\Framework\View\Layout\GeneratorPool
     */
    private $generatorPool;
    /**
     * @var \Magento\Framework\View\Element\UiComponentFactory
     */
    private $uiComponentFactory;
    /**
     * @var \Magento\Framework\View\Element\BlockFactory
     */
    private $blockFactory;

    /**
     * @param DepersonalizeChecker                               $depersonalizeChecker
     * @param \Magento\Framework\View\Layout\Data\Structure      $structure
     * @param \Magento\Framework\View\Layout\GeneratorPool       $generatorPool
     * @param \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory
     * @param \Magento\Framework\View\Element\BlockFactory       $blockFactory
     * @param \Magento\Catalog\Model\Session                     $catalogSession
     */
    public function __construct(
        DepersonalizeChecker $depersonalizeChecker,
        \Magento\Framework\View\Layout\Data\Structure $structure,
        \Magento\Framework\View\Layout\GeneratorPool $generatorPool,
        UiComponentFactory $uiComponentFactory,
        BlockFactory $blockFactory,
        \Magento\Catalog\Model\Session $catalogSession
    ) {
        $this->catalogSession = $catalogSession;
        $this->depersonalizeChecker = $depersonalizeChecker;
        $this->structure = $structure;
        $this->generatorPool = $generatorPool;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->blockFactory = $blockFactory;
    }

    /**
     * Before generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     *
     * @return array
     */
    public function beforeGenerateXml(\Magento\Framework\View\LayoutInterface $subject)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            if ($this->catalogSession->getStartDateGlobal()) {
                $this->startDateGlobal = $this->catalogSession->getStartDateGlobal();
                $this->endDateGlobal = $this->catalogSession->getEndDateGlobal();
            }
        }
        return [];
    }

    /**
     * After generate Xml
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Magento\Framework\View\LayoutInterface $result
     *
     * @return \Magento\Framework\View\LayoutInterface
     */
    public function afterGenerateXml(\Magento\Framework\View\LayoutInterface $subject, $result)
    {
        if ($this->depersonalizeChecker->checkIfDepersonalize($subject)) {
            if ($this->startDateGlobal) {
                $this->catalogSession->setStartDateGlobal($this->startDateGlobal);
                $this->catalogSession->setEndDateGlobal($this->endDateGlobal);
            }
        }
        return $result;
    }

    /**
     * Block Factory
     *
     * @param \Magento\Framework\View\LayoutInterface $subject
     * @param \Closure                                $proceed
     * @param string                                  $type
     * @param string                                  $name
     * @param array                                   $arguments
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    public function aroundCreateBlock(
        \Magento\Framework\View\LayoutInterface $subject,
        \Closure $proceed,
        $type,
        $name = '',
        array $arguments = []
    ) {
        $blockFinal = $proceed($type, $name, $arguments);
        if (array_key_exists('is_uicomponent', $arguments)) {
            //unset($arguments['is_uicomponent']);
            $name = $this->structure->createStructuralElement($name, Element::TYPE_UI_COMPONENT, $type);

            $block = $this->_createBlockUI($subject, $type, $name, $arguments);
            //$block->setLayout($subject);
            return $block;
        } else {
            return $blockFinal;
        }
    }

    /**
     * Create block ui and add to layout
     *
     * @param        $subject
     * @param string $type
     * @param string $name
     * @param array  $arguments
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _createBlockUI($subject, $type, $name, array $arguments = [])
    {
        /** @var \Magento\Ui\Component\Form $blockGenerator */
        //$blockGenerator = $this->generatorPool->getGenerator(\Magento\Framework\View\Layout\Generator\UiComponent::TYPE);
        //$block = $blockGenerator->createBlock($type, $name, $arguments);
        $component = $this->uiComponentFactory->create($name, null, [
            /*'context' => $context*/
        ]);
        $this->prepareComponent($component);

        /** @var ContainerInterface $blockContainer */
        $blockContainer = $this->blockFactory->createBlock(\Magento\Framework\View\Layout\Generator\UiComponent::CONTAINER, ['component' => $component]);

        $subject->setBlock($name, $blockContainer);
        return $blockContainer;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     *
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareComponent($child);
            }
        }
        $component->prepare();
    }
}
