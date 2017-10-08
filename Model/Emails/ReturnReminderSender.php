<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace SalesIgniter\Rental\Model\Emails;

use Magento\Sales\Model\Order\Email\Sender;

/**
 * Email notification sender for Returns.
 */
class ReturnReminderSender extends Sender
{

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $globalConfig;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @param \Magento\Sales\Model\Order\Email\Container\Template      $templateContainer
     * @param \SalesIgniter\Rental\Model\Emails\ReturnReminderIdentity $identityContainer
     * @param \Magento\Sales\Model\Order\Email\SenderBuilderFactory    $senderBuilderFactory
     * @param \Psr\Log\LoggerInterface                                 $logger
     * @param \Magento\Sales\Model\Order\Address\Renderer              $addressRenderer
     * @param \Magento\Payment\Helper\Data                             $paymentHelper
     * @param \Magento\Framework\App\Config\ScopeConfigInterface       $globalConfig
     * @param \Magento\Framework\Event\ManagerInterface                $eventManager
     */
    public function __construct(
        \Magento\Sales\Model\Order\Email\Container\Template $templateContainer,
        ReturnReminderIdentity $identityContainer,
        \Magento\Sales\Model\Order\Email\SenderBuilderFactory $senderBuilderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\Order\Address\Renderer $addressRenderer,
        \Magento\Payment\Helper\Data $paymentHelper,
        \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        parent::__construct(
            $templateContainer,
            $identityContainer,
            $senderBuilderFactory,
            $logger,
            $addressRenderer
        );

        $this->globalConfig = $globalConfig;
        $this->eventManager = $eventManager;
        $this->identityContainer = $identityContainer;
    }

    /**
     * Sends order invoice email to the customer.
     *
     * Email will be sent immediately in two cases:
     *
     * - if asynchronous email sending is disabled in global settings
     * - if $forceSyncMode parameter is set to TRUE
     *
     * Otherwise, email will be sent later during running of
     * corresponding cron job.
     *
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     *
     * @param                                        $orders
     * @param \Magento\Sales\Api\Data\OrderInterface $return
     *
     * @return bool
     */
    public function send(
        \Magento\Sales\Api\Data\OrderInterface $order,
        $orders,
        \Magento\Sales\Api\Data\OrderInterface $return

    ) {
        ///the correct way to send email is by using the identity class and the send class and go from there so should be a returnreminder etc
        ///the cron should use these classes
        ///
        $transport = [
            'order' => $order,
            'orders' => $orders,
            'return' => $return,
            'comment' => '',
            'billing' => $order->getBillingAddress(),
            'store' => $order->getStore(),
            'formattedShippingAddress' => $this->getFormattedShippingAddress($order),
            'formattedBillingAddress' => $this->getFormattedBillingAddress($order),
        ];

        $this->eventManager->dispatch(
            'sirent_return_reminder_set_template_vars_before',
            ['sender' => $this, 'transport' => $transport]
        );

        $this->templateContainer->setTemplateVars($transport);

        if ($this->checkAndSend($order)) {
            return true;
        }

        return false;
    }

    /**
     * get reminder days
     * @return mixed
     */
    public function getReminderDays(){
        return $this->identityContainer->getReturnReminderDays();
    }
}
