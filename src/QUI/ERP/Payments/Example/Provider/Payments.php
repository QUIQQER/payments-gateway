<?php

declare(strict_types=1);

namespace QUI\ERP\Payments\Example\Provider;

use QUI\ERP\Accounting\Payments\Api\AbstractPaymentProvider;
use QUI\ERP\Payments\Example\Payment;

/**
 * Registers the example payment method with the QUIQQER payment system.
 */
class Payments extends AbstractPaymentProvider
{
    /**
     * @return list<class-string>
     */
    public function getPaymentTypes(): array
    {
        return [Payment::class];
    }
}
