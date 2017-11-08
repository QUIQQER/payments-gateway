<?php

/**
 * This file contains QUI\ERP\Accounting\Payments\Provider\Payments
 */

namespace QUI\ERP\Payments\Example\Provider;

use QUI\ERP\Accounting\Payments\Api\AbstractPaymentProvider;
use QUI\ERP\Payments\Gateways\Example;

/**
 * Class Provider
 * - provides the example payment method to the system
 *
 * @package QUI\ERP\Payments\Gateways\Example
 */
class Payments extends AbstractPaymentProvider
{
    /**
     * @return array
     */
    public function getPaymentTypes()
    {
        return [
            Example\Payment::class
        ];
    }
}
