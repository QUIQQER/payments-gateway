<?php

/**
 * This file contains QUI\ERP\Payments\Gateways\Example\Gateway
 */

namespace QUI\ERP\Payments\Gateways\Example;

use QUI;

/**
 * Class Gateway
 * - This class provides the display for the gateway.
 * - The gateway control allows the user to pay with the payment method
 *      -> by clicking on the button the user is directed to the payment service provider
 *
 * @package QUI\ERP\Payments\Gateways\Example
 */
class Gateway extends QUI\Control
{
    /**
     * Return the body of the control
     * Here you can integrate the payment form, or forwarding functionality to the gateway
     *
     * @return string
     */
    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();

        /* @var $Order QUI\ERP\Order\OrderInProcess */
        $Order = $this->getAttribute('Order');

        /* @var $Payment QUI\ERP\Accounting\Payments\Api\AbstractPayment */
        $Payment = $this->getAttribute('Payment');

        $Gateway = QUI\ERP\Accounting\Payments\Gateway\Gateway::getInstance();
        $Gateway->setOrderId($Order->getId());

        $Engine->assign(array(
            'Order'      => $Order,
            'Payment'    => $Payment,
            'gatewayUrl' => $Gateway->getGatewayUrl(),
            'cancelUrl'  => $Gateway->getCancelUrl(),
            'successUrl' => $Gateway->getSuccessUrl(),
            'orderUrl'   => $Gateway->getOrderUrl()
        ));

        return $Engine->fetch(dirname(__FILE__).'/Gateway.html');
    }
}
