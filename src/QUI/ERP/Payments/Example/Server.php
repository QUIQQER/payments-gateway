<?php

/**
 * This file contains QUI\ERP\Payments\Example\Server
 */

namespace QUI\ERP\Payments\Example;

use QUI;

use \Symfony\Component\HttpFoundation\RedirectResponse;
use \Symfony\Component\HttpFoundation\Response;

/**
 * #### IMPORTANT #####
 *
 * - this is just an example of a server to mimic a payment service provider
 * - its only an example, its for development and not for the live usage
 * - This usually does not have to be implemented
 * - This functionality comes from the payment provider
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
            // forwarding to the cancel url
            $Redirect = new RedirectResponse($_POST['cancelUrl']);
            $Redirect->setStatusCode(Response::HTTP_SEE_OTHER);

            echo $Redirect->getContent();
            $Redirect->send();
            exit;
        }

        // payment
        if (isset($_POST['submit']) && $_POST['submit'] === 'PAY') {
            // send payment
            $Gateway    = new QUI\ERP\Accounting\Payments\Gateway\Gateway();
            $paymentUrl = $Gateway->getPaymentProviderUrl();

            $query['amount']    = $_POST['pay'];
            $query['orderHash'] = $_POST['orderHash'];

            $paymentUrl = $paymentUrl.'&'.http_build_query($query);

            // send request
            file_get_contents($paymentUrl);
            exit;
        }

        // $_POST['orderId'];
        // $_POST['orderUrl'];

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
        $Articles->calc();

        $Engine->assign(array(
            'Order'      => $Order,
            'Articles'   => $Articles,
            'calculated' => $Articles->toArray(),
            'orderId'    => $_POST['orderId'],
            'orderUrl'   => $_POST['orderUrl'],
            'gatewayUrl' => $_POST['gatewayUrl'],
            'cancelUrl'  => $_POST['cancelUrl'],
            'successUrl' => $_POST['successUrl']
        ));

        echo $Engine->fetch(dirname(__FILE__).'/Server.Result.html');
        exit;
    }
}
