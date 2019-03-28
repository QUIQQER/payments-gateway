<?php

/**
 * This file contains QUI\ERP\Payments\Example\Payment
 */

namespace QUI\ERP\Payments\Example;

use QUI;
use QUI\ERP\Order\AbstractOrder;
use QUI\ERP\Accounting\Payments\Transactions\Transaction;
use QUI\ERP\Accounting\Payments\Transactions\Factory as TransactionFactory;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class Payment
 * - This class is your main API point for your payment type
 *
 * @package QUI\ERP\Payments\Example\Example
 */
class Payment extends QUI\ERP\Accounting\Payments\Api\AbstractPayment
{
    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.title');
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->getLocale()->get('quiqqer/payments-gateway', 'payment.description');
    }

    /**
     * @param string $hash - Vorgangsnummer - hash number - procedure number
     * @return bool
     */
    public function isSuccessful($hash)
    {
        try {
            $Order = QUI\ERP\Order\Handler::getInstance()->getOrderByHash($hash);

            if ($Order->isPaid()) {
                return true;
            }
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeException($Exception);
        }

        // $status = ERP::getPaymentStatus($hash);

        return false;
    }

    /**
     * Is the payment a gateway payment?
     *
     * @return bool
     */
    public function isGateway()
    {
        return true;
    }

    /**
     * test -> unique
     */
    public function isUnique()
    {
        return false;
    }

    /**
     * If the Payment method is a payment gateway, it can return a gateway display
     *
     * @param AbstractOrder $Order
     * @param QUI\ERP\Order\Controls\AbstractOrderingStep|null $Step
     * @return string
     */
    public function getGatewayDisplay(AbstractOrder $Order, $Step = null)
    {
        $Control = new PaymentDisplay();
        $Control->setAttribute('Order', $Order);
        $Control->setAttribute('Payment', $this);

        $Order->setPaymentData('payment-test-gateway-inProcess', 'test-value');
        $Order->update();

        return $Control->create();
    }

    /**
     * @param QUI\ERP\Accounting\Payments\Gateway\Gateway $Gateway
     * @throws QUI\Exception
     */
    public function executeGatewayPayment(QUI\ERP\Accounting\Payments\Gateway\Gateway $Gateway)
    {
        if (isset($_REQUEST['canceled'])) {
            $Redirect = new RedirectResponse($Gateway->getOrderUrl());
            $Redirect->setStatusCode(Response::HTTP_SEE_OTHER);

            echo $Redirect->getContent();
            $Redirect->send();

            return;
        }

        $Order    = $Gateway->getOrder();
        $amount   = floatval($_REQUEST['amount']);
        $Currency = $Order->getCurrency();

        // variable payment data
        $paymentData = [
            'payment' => $this->getName(),
            'title'   => $this->getTitle()
        ];

        $Order->setPaymentData('payment-test-gateway-order', 'test-value');
        $Order->update(QUI::getUsers()->getSystemUser());

        // Gateway::paymentError();
        // Gateway::paymentPending();

        QUI\System\Log::writeRecursive([
            $amount,
            $Currency->getCode(),
            $Order->getHash(),
            $this->getTitle(),
            $paymentData
        ]);

        $Transaction = $Gateway->purchase($amount, $Currency, $Order, $this, $paymentData);
        //$Transaction->pending();
    }


    /**
     * This payment has refund support
     *
     * @return bool
     */
    public function refundSupport()
    {
        return true;
    }

    /**
     * Execute a refund
     *
     * @param QUI\ERP\Accounting\Payments\Transactions\Transaction $Transaction
     * @param $amount
     * @param string $message
     * @param string|bool $hash
     */
    public function refund(
        Transaction $Transaction,
        $amount,
        $message = '',
        $hash = false
    ) {
        // example for a refund

        // this here can also run asynchronously or take longer
        // ....
        // ....

        // execute this code if the payment refund is successfully done
        try {
            if ($hash === false) {
                $hash = $Transaction->getHash();
            }

            // create a refund transaction
            $RefundTransaction = TransactionFactory::createPaymentRefundTransaction(
                $amount,
                $Transaction->getCurrency(),
                $hash,
                $Transaction->getPayment()->getName(),
                [
                    'isRefund' => 1,
                    'message'  => $message
                ],
                null,
                false,
                $Transaction->getGlobalProcessId()
            );

            // execute the
            QUI::getEvents()->fireEvent('transactionSuccessfullyRefunded', [
                $RefundTransaction,
                $this,
            ]);
        } catch (QUI\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
            QUI\System\Log::writeException($Exception);
        }
    }
}
