<?php

declare(strict_types=1);

namespace QUI\ERP\Payments\Example;

use QUI;
use QUI\ERP\Accounting\Payments\Api\AbstractPayment;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;
use QUI\ERP\Order\AbstractOrder;

/**
 * Renders the form that redirects an order to the example gateway server.
 */
class PaymentDisplay extends QUI\Control
{
    public function getBody(): string
    {
        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);

            return '';
        }

        $Order = $this->getAttribute('Order');
        $Payment = $this->getAttribute('Payment');

        if (!$Order instanceof AbstractOrder || !$Payment instanceof AbstractPayment) {
            return '';
        }

        $Gateway = Gateway::getInstance();
        $Gateway->setOrder($Order);

        $Engine->assign([
            'Order' => $Order,
            'Payment' => $Payment,
            'gatewayUrl' => $Gateway->getGatewayUrl(),
            'cancelUrl' => $Gateway->getCancelUrl(),
            'successUrl' => $Gateway->getSuccessUrl(),
            'orderUrl' => $Gateway->getOrderUrl()
        ]);

        return $Engine->fetch(__DIR__ . '/PaymentDisplay.html');
    }
}
