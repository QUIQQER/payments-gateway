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
     * @return string
     */
    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();


        return $Engine->fetch(dirname(__FILE__).'/Gateway.html');
    }
}
