<?php

/**
 * This file contains QUI\ERP\Payments\Example\PaymentDisplay
 */

namespace QUI\ERP\Payments\Example;

use QUI;

/**
 * Class GatewayPaymentDisplay
 * - This class provides the display for the gateway.
 * - The gateway control allows the user to pay with the payment method
 *      -> by clicking on the button the user is directed to the payment service provider
 *
 * @package QUI\ERP\Payments\Example\Example
 */
class PaymentDisplay extends QUI\Control
{
    /**
     * Return the body of the control
     * Here you can integrate the payment form, or forwarding functionality to the gateway
     *
     * @return string
     */
    public function getBody()
    {
        $this->setAttribute(
            'data-qui',
            'package/quiqqer/payments-gateway/bin/controls/frontend/PaymentDisplay'
        );

        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);

            return '';
        }


        /* @var $Order QUI\ERP\Order\OrderInProcess */
        $Order = $this->getAttribute('Order');

        /* @var $Payment QUI\ERP\Accounting\Payments\Api\AbstractPayment */
        $Payment = $this->getAttribute('Payment');

        $Gateway = QUI\ERP\Accounting\Payments\Gateway\Gateway::getInstance();
        $Gateway->setOrder($Order);

        $Engine->assign([
            'Order'      => $Order,
            'Payment'    => $Payment,
            'gatewayUrl' => $Gateway->getGatewayUrl(),
            'cancelUrl'  => $Gateway->getCancelUrl(),
            'successUrl' => $Gateway->getSuccessUrl(),
            'orderUrl'   => $Gateway->getOrderUrl()
        ]);

        return $Engine->fetch(dirname(__FILE__).'/PaymentDisplay.html');
    }
}
