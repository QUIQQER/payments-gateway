<?php

/**
 * This file contains QUI\ERP\Payments\Example\Server\Server
 */

namespace QUI\ERP\Payments\Example\Server;

use QUI;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;

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
     *
     * @param QUI\Rewrite $Rewrite
     * @param $url
     *
     * @throws QUI\Exception
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
            $Gateway = new QUI\ERP\Accounting\Payments\Gateway\Gateway();
            $Gateway->setOrder($_POST['orderHash']);

            $paymentUrl = $Gateway->getGatewayUrl([
                Gateway::URL_PARAM_GATEWAY_PAYMENT => 1,
                Gateway::URL_PARAM_USER_REDIRECTED => 0
            ]);

            $query['amount']    = $_POST['pay'];
            $query['orderHash'] = $_POST['orderHash'];

            $paymentUrl = $paymentUrl.'&'.\http_build_query($query);

            // send request from the payment provider
            \file_get_contents($paymentUrl);

            $url = $Gateway->getOrderUrl();

            if (empty($url)) {
                $url = URL_DIR;
            }

            // forwarding to the cancel url
            $Redirect = new RedirectResponse($url);
            $Redirect->setStatusCode(Response::HTTP_SEE_OTHER);
            $Redirect->setContent(
                'Payment successfully completed. In some Seconds you will be get back to the Order'
            );

            $Redirect->headers->set('Refresh', 5);

            echo $Redirect->getContent();
            $Redirect->send();

            exit;
        }

        if (!isset($_POST['orderId']) || !isset($_POST['orderHash'])) {
            $Gateway = new QUI\ERP\Accounting\Payments\Gateway\Gateway();
            $url     = $Gateway->getOrderUrl();

            if (empty($url)) {
                $url = URL_DIR;
            }

            $Redirect = new RedirectResponse($url);
            $Redirect->setStatusCode(Response::HTTP_SEE_OTHER);

            echo $Redirect->getContent();
            $Redirect->send();

            exit;
        }

        // $_POST['orderId'];
        // $_POST['orderUrl'];

        /* @var $Order QUI\ERP\Order\Order */
        $Gateway = new QUI\ERP\Accounting\Payments\Gateway\Gateway();
        $Gateway->setOrder($_POST['orderHash']);

        $Order  = $Gateway->getOrder();
        $Engine = QUI::getTemplateManager()->getEngine();

        $Articles = $Order->getArticles();
        $Articles->hideHeader();
        $Articles->calc();

        $Engine->assign([
            'Order'      => $Order,
            'Articles'   => $Articles,
            'calculated' => $Articles->toArray(),
            'orderId'    => $_POST['orderId'],
            'orderUrl'   => $_POST['orderUrl'],
            'gatewayUrl' => $_POST['gatewayUrl'],
            'cancelUrl'  => $_POST['cancelUrl'],
            'successUrl' => $_POST['successUrl']
        ]);

        echo $Engine->fetch(\dirname(__FILE__).'/Server.Result.html');
        exit;
    }
}
