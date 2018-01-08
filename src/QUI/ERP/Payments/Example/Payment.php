<?php

/**
 * This file contains QUI\ERP\Payments\Example\Payment
 */

namespace QUI\ERP\Payments\Example;

use QUI;
use QUI\ERP\Order\AbstractOrder;

/**
 * Class Payment
 * - This class is your main API point for your payment type
 *
 * @package QUI\ERP\Payments\Example\Example
 */
class Payment extends QUI\ERP\Accounting\Payments\Api\AbstractPayment
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.description');
    }

    /**
     * Is the payment a gateway payment?
     *
     * @return bool
     */
    public function isGateway()
    {
        return true;
    }

    /**
     * If the Payment method is a payment gateway, it can return a gateway display
     *
     * @param AbstractOrder $Order
     * @return string
     */
    public function getGatewayDisplay(AbstractOrder $Order)
    {
        $Control = new PaymentDisplay();
        $Control->setAttribute('Order', $Order);
        $Control->setAttribute('Payment', $this);

        return $Control->create();
    }

    /**
     * @param QUI\ERP\Accounting\Payments\Gateway\Gateway $Gateway
     */
    public function executeGatewayPayment(QUI\ERP\Accounting\Payments\Gateway\Gateway $Gateway)
    {
        QUI\System\Log::writeRecursive('Execute Payment from the Test Gateway');

        $Order  = $Gateway->getOrder();
        $amount = $_REQUEST['amount'];

        $Order->addComment('Add Payment from Example Gateway Payment: '.$amount);

        QUI\System\Log::writeRecursive('Add Payment from Gateway Example; Amount: '.$amount);

        return;
        // @todo addPayment in order rein
        $Invoice = $Order->getInvoice();
        $Invoice->addPayment($_REQUEST['amount'], $this);
    }
}
