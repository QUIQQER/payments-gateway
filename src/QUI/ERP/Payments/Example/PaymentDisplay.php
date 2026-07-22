<?php

declare(strict_types=1);

namespace QUI\ERP\Payments\Example;

use QUI;
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

        if (!$Order instanceof AbstractOrder) {
            return '';
        }

        $Engine->assign([
            'Order' => $Order
        ]);

        return $Engine->fetch(__DIR__ . '/PaymentDisplay.html');
    }
}
