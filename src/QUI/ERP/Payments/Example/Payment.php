<?php

namespace QUI\ERP\Payments\Gateways\Example;

use QUI;
use QUI\ERP\Order\AbstractOrder;

/**
 * Class Payment
 *
 * @package QUI\ERP\Payments\Gateways\Example
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
        $Control = new Gateway();
        $Control->setAttribute('Order', $Order);
        $Control->setAttribute('Payment', $this);

        return $Control->create();
    }
}
