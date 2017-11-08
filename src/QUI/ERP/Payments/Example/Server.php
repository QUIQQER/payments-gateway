<?php

/**
 * This file contains QUI\ERP\Payments\Example\Server
 */

namespace QUI\ERP\Payments\Example;

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
     * on request
     * we check the PAYMENT_TEST_GATEWAY post value, if exists, we have a payment
     */
    public static function onRequest(QUI\Rewrite $Rewrite, $url)
    {
        if (empty($_POST)) {
            return;
        }

        if (!isset($_POST['PAYMENT_TEST_GATEWAY'])) {
            return;
        }

        if (isset($_POST['submit']) && $_POST['submit'] === 'CANCEL') {
            return;
        }

        $Handler = QUI\ERP\Order\Handler::getInstance();

        /* @var $Order QUI\ERP\Order\Order */
        try {
            $Order = $Handler->get($_POST['orderId']);
        } catch (QUI\ERP\Order\Exception $Exception) {
            try {
                $Order = $Handler->getOrderInProcess($_POST['orderId']);
            } catch (QUI\ERP\Order\Exception $Exception) {
                echo $Exception->getMessage();
                exit;
            }
        }

        $Engine = QUI::getTemplateManager()->getEngine();

        $Articles = $Order->getArticles();
        $Articles->hideHeader();

        //Gateway::getUrl();
        //Gateway::getSuccessUrl();
        //Gateway::getCancelUrl();
        //Gateway::getErrorUrl();

        $Engine->assign(array(
            'Order'    => $Order,
            'Articles' => $Articles
        ));

        echo $Engine->fetch(dirname(__FILE__).'/Server.Result.html');
        exit;
    }
}
