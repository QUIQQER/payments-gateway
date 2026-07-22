<?php

declare(strict_types=1);

namespace QUI\ERP\Payments\Example\Server;

use QUI;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Simulates the external service of a payment provider for demonstration purposes.
 */
class Server
{
    /**
     * @param QUI\Rewrite $Rewrite
     * @param string $url
     */
    public static function onRequest(QUI\Rewrite $Rewrite, string $url): void
    {
        $Response = self::handleRequest($_POST);

        if ($Response === null) {
            return;
        }

        $Response->send();
        exit;
    }

    /**
     * Build the example provider response for submitted gateway data.
     *
     * @param array<string, mixed> $post
     */
    public static function handleRequest(array $post, ?Gateway $Gateway = null): ?Response
    {
        if (($post['PAYMENT_TEST_GATEWAY'] ?? null) !== '1') {
            return null;
        }

        $orderHash = $post['orderHash'] ?? null;

        if (!is_string($orderHash) || trim($orderHash) === '') {
            return new RedirectResponse(URL_DIR, Response::HTTP_SEE_OTHER);
        }

        $Gateway ??= Gateway::getInstance();
        $Gateway->setOrder($orderHash);
        $Order = $Gateway->getOrder();

        if ($Order === null) {
            return new RedirectResponse(URL_DIR, Response::HTTP_SEE_OTHER);
        }

        $submit = $post['submit'] ?? null;

        if ($submit === 'CANCEL') {
            $cancelUrl = $Gateway->getCancelUrl();

            if ($cancelUrl === '') {
                $cancelUrl = $Gateway->getOrderUrl();
            }

            return new RedirectResponse(
                $cancelUrl === '' ? URL_DIR : $cancelUrl,
                Response::HTTP_SEE_OTHER
            );
        }

        $Articles = $Order->getArticles();
        $Articles->hideHeader();
        $Articles->calc();
        $calculated = $Articles->toArray();
        $amount = $calculated['calculations']['sum'] ?? null;

        if (!is_int($amount) && !is_float($amount) && !is_numeric($amount)) {
            return new Response('The example gateway could not determine the order amount.', Response::HTTP_BAD_REQUEST);
        }

        $amount = (float)$amount;

        if ($amount <= 0) {
            return new Response('The example gateway requires a positive order amount.', Response::HTTP_BAD_REQUEST);
        }

        if ($submit === 'PAY') {
            return new RedirectResponse(
                $Gateway->getGatewayUrl([
                    Gateway::URL_PARAM_GATEWAY_PAYMENT => 1,
                    Gateway::URL_PARAM_USER_REDIRECTED => 0,
                    'amount' => $amount
                ]),
                Response::HTTP_SEE_OTHER
            );
        }

        try {
            $Engine = QUI::getTemplateManager()->getEngine();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $Engine->assign([
            'Order' => $Order,
            'Articles' => $Articles,
            'calculated' => $calculated
        ]);

        return new Response($Engine->fetch(__DIR__ . '/Server.Result.html'));
    }
}
