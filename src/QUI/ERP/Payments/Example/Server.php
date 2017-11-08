<?php

namespace QUI\ERP\Payments\Gateways\Example;

use QUI;

/**
 * Class Server
 * - this is just an example of a server to mimic a payment service provider
 * - its only an example, its for development and not for the live usage
 *
 * @package QUI\ERP\Payments\Gateways\Example
 */
class Server
{
    /**
     *
     */
    public static function onRequest(QUI\Rewrite $Rewrite, $url)
    {
echo 1;
    }
}
