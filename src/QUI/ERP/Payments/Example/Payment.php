<?php

declare(strict_types=1);

namespace QUI\ERP\Payments\Example;

use QUI;
use QUI\ERP\Accounting\Payments\Api\AbstractPayment;
use QUI\ERP\Accounting\Payments\Gateway\Gateway;
use QUI\ERP\Accounting\Payments\Transactions\Factory as TransactionFactory;
use QUI\ERP\Accounting\Payments\Transactions\Transaction;
use QUI\ERP\Order\AbstractOrder;
use QUI\ERP\Order\Controls\AbstractOrderingStep;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Example implementation of a gateway payment method.
 */
class Payment extends AbstractPayment
{
    public function getTitle(): string
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.title');
    }

    public function getDescription(): string
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.description');
    }

    public function isSuccessful(string $hash): bool
    {
        try {
            return QUI\ERP\Order\Handler::getInstance()->getOrderByHash($hash)->isPaid();
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);

            return false;
        }
    }

    public function isGateway(): bool
    {
        return true;
    }

    public function isUnique(): bool
    {
        return false;
    }

    public function getGatewayDisplay(
        AbstractOrder $Order,
        ?AbstractOrderingStep $Step = null
    ): string {
        $Control = new PaymentDisplay();
        $Control->setAttribute('Order', $Order);

        $Order->setPaymentData('payment-test-gateway-inProcess', 'test-value');
        $Order->update();

        return $Control->create();
    }

    /**
     * @throws QUI\Exception
     */
    public function executeGatewayPayment(Gateway $Gateway): void
    {
        if (isset($_REQUEST['canceled'])) {
            (new RedirectResponse($Gateway->getOrderUrl(), Response::HTTP_SEE_OTHER))->send();

            return;
        }

        $Order = $Gateway->getOrder();

        if ($Order === null) {
            throw new QUI\Exception('No order is available for the example gateway payment.');
        }

        $amount = filter_var($_REQUEST['amount'] ?? null, FILTER_VALIDATE_FLOAT);

        if ($amount === false || $amount <= 0) {
            throw new QUI\Exception('The example gateway received an invalid payment amount.');
        }

        $Currency = $Order->getCurrency();
        $paymentData = [
            'payment' => $this->getName(),
            'title' => $this->getTitle()
        ];

        $Order->setPaymentData('payment-test-gateway-order', 'test-value');
        $Order->update(QUI::getUsers()->getSystemUser());

        QUI\System\Log::writeRecursive([
            $amount,
            $Currency->getCode(),
            $Order->getHash(),
            $this->getTitle(),
            $paymentData
        ]);

        $Gateway->purchase($amount, $Currency, $Order, $this, $paymentData);
    }

    public function refundSupport(): bool
    {
        return true;
    }

    public function refund(
        Transaction $Transaction,
        float | int $amount,
        string $message = '',
        bool | string $hash = false
    ): void {
        try {
            if ($hash === false) {
                $hash = $Transaction->getHash();
            }

            $Payment = $Transaction->getPayment();

            if ($Payment === null) {
                throw new QUI\Exception('The transaction has no payment method.');
            }

            $RefundTransaction = TransactionFactory::createPaymentRefundTransaction(
                $amount,
                $Transaction->getCurrency(),
                $hash,
                $Payment->getName(),
                [
                    'isRefund' => 1,
                    'message' => $message
                ],
                null,
                false,
                $Transaction->getGlobalProcessId()
            );

            QUI::getEvents()->fireEvent('transactionSuccessfullyRefunded', [
                $RefundTransaction,
                $this
            ]);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
            QUI\System\Log::writeException($Exception);
        }
    }
}
