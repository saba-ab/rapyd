<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Webhooks;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Sabaab\Rapyd\Webhooks\Events\CardAddedSuccessfullyEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingAdjustmentEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingAtmFeeEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingAtmWithdrawalEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingAuthApprovedEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingAuthDeclinedEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingChargebackEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingCreditEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingRefundEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingReversalEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingSaleEvent;
use Sabaab\Rapyd\Webhooks\Events\CardIssuingTxnCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerDeletedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerPaymentMethodCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerPaymentMethodDeletedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerPaymentMethodExpiringEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerPaymentMethodUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\CustomerUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\InvoiceCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\InvoicePaymentCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\InvoicePaymentFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\InvoicePaymentSucceededEvent;
use Sabaab\Rapyd\Webhooks\Events\InvoiceUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentCanceledEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentCapturedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentDisputeCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentDisputeUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentExpiredEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentRefundCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentRefundFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentRefundRejectedEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentSucceededEvent;
use Sabaab\Rapyd\Webhooks\Events\PaymentUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutCanceledEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutExpiredEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutReturnedEvent;
use Sabaab\Rapyd\Webhooks\Events\PayoutUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\RapydWebhookReceived;
use Sabaab\Rapyd\Webhooks\Events\RefundCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\RefundFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\RefundRejectedEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionCanceledEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionPastDueEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionRenewedEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionTrialEndEvent;
use Sabaab\Rapyd\Webhooks\Events\SubscriptionUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\VerifyApplicationApprovedEvent;
use Sabaab\Rapyd\Webhooks\Events\VerifyApplicationRejectedEvent;
use Sabaab\Rapyd\Webhooks\Events\VerifyApplicationSubmittedEvent;
use Sabaab\Rapyd\Webhooks\Events\VirtualAccountClosedEvent;
use Sabaab\Rapyd\Webhooks\Events\VirtualAccountCreatedEvent;
use Sabaab\Rapyd\Webhooks\Events\VirtualAccountTransactionEvent;
use Sabaab\Rapyd\Webhooks\Events\VirtualAccountUpdatedEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletFundsAddedEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletFundsRemovedEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletTransactionEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletTransferCompletedEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletTransferFailedEvent;
use Sabaab\Rapyd\Webhooks\Events\WalletTransferResponseReceivedEvent;

final class WebhookController
{
    private const EVENT_MAP = [
        'PAYMENT_COMPLETED' => PaymentCompletedEvent::class,
        'PAYMENT_SUCCEEDED' => PaymentSucceededEvent::class,
        'PAYMENT_FAILED' => PaymentFailedEvent::class,
        'PAYMENT_EXPIRED' => PaymentExpiredEvent::class,
        'PAYMENT_UPDATED' => PaymentUpdatedEvent::class,
        'PAYMENT_CAPTURED' => PaymentCapturedEvent::class,
        'PAYMENT_CANCELED' => PaymentCanceledEvent::class,
        'PAYMENT_REFUND_COMPLETED' => PaymentRefundCompletedEvent::class,
        'PAYMENT_REFUND_FAILED' => PaymentRefundFailedEvent::class,
        'PAYMENT_REFUND_REJECTED' => PaymentRefundRejectedEvent::class,
        'PAYMENT_DISPUTE_CREATED' => PaymentDisputeCreatedEvent::class,
        'PAYMENT_DISPUTE_UPDATED' => PaymentDisputeUpdatedEvent::class,
        'REFUND_COMPLETED' => RefundCompletedEvent::class,
        'REFUND_FAILED' => RefundFailedEvent::class,
        'REFUND_REJECTED' => RefundRejectedEvent::class,
        'CUSTOMER_CREATED' => CustomerCreatedEvent::class,
        'CUSTOMER_UPDATED' => CustomerUpdatedEvent::class,
        'CUSTOMER_DELETED' => CustomerDeletedEvent::class,
        'CUSTOMER_PAYMENT_METHOD_CREATED' => CustomerPaymentMethodCreatedEvent::class,
        'CUSTOMER_PAYMENT_METHOD_UPDATED' => CustomerPaymentMethodUpdatedEvent::class,
        'CUSTOMER_PAYMENT_METHOD_DELETED' => CustomerPaymentMethodDeletedEvent::class,
        'CUSTOMER_PAYMENT_METHOD_EXPIRING' => CustomerPaymentMethodExpiringEvent::class,
        'CUSTOMER_SUBSCRIPTION_CREATED' => SubscriptionCreatedEvent::class,
        'CUSTOMER_SUBSCRIPTION_UPDATED' => SubscriptionUpdatedEvent::class,
        'CUSTOMER_SUBSCRIPTION_COMPLETED' => SubscriptionCompletedEvent::class,
        'CUSTOMER_SUBSCRIPTION_CANCELED' => SubscriptionCanceledEvent::class,
        'CUSTOMER_SUBSCRIPTION_PAST_DUE' => SubscriptionPastDueEvent::class,
        'CUSTOMER_SUBSCRIPTION_TRIAL_END' => SubscriptionTrialEndEvent::class,
        'CUSTOMER_SUBSCRIPTION_RENEWED' => SubscriptionRenewedEvent::class,
        'INVOICE_CREATED' => InvoiceCreatedEvent::class,
        'INVOICE_UPDATED' => InvoiceUpdatedEvent::class,
        'INVOICE_PAYMENT_CREATED' => InvoicePaymentCreatedEvent::class,
        'INVOICE_PAYMENT_SUCCEEDED' => InvoicePaymentSucceededEvent::class,
        'INVOICE_PAYMENT_FAILED' => InvoicePaymentFailedEvent::class,
        'PAYOUT_COMPLETED' => PayoutCompletedEvent::class,
        'PAYOUT_UPDATED' => PayoutUpdatedEvent::class,
        'PAYOUT_FAILED' => PayoutFailedEvent::class,
        'PAYOUT_EXPIRED' => PayoutExpiredEvent::class,
        'PAYOUT_CANCELED' => PayoutCanceledEvent::class,
        'PAYOUT_RETURNED' => PayoutReturnedEvent::class,
        'WALLET_TRANSACTION' => WalletTransactionEvent::class,
        'WALLET_FUNDS_ADDED' => WalletFundsAddedEvent::class,
        'WALLET_FUNDS_REMOVED' => WalletFundsRemovedEvent::class,
        'WALLET_TRANSFER_COMPLETED' => WalletTransferCompletedEvent::class,
        'WALLET_TRANSFER_FAILED' => WalletTransferFailedEvent::class,
        'WALLET_TRANSFER_RESPONSE_RECEIVED' => WalletTransferResponseReceivedEvent::class,
        'CARD_ISSUING_AUTHORIZATION_APPROVED' => CardIssuingAuthApprovedEvent::class,
        'CARD_ISSUING_AUTHORIZATION_DECLINED' => CardIssuingAuthDeclinedEvent::class,
        'CARD_ISSUING_SALE' => CardIssuingSaleEvent::class,
        'CARD_ISSUING_CREDIT' => CardIssuingCreditEvent::class,
        'CARD_ISSUING_REVERSAL' => CardIssuingReversalEvent::class,
        'CARD_ISSUING_REFUND' => CardIssuingRefundEvent::class,
        'CARD_ISSUING_CHARGEBACK' => CardIssuingChargebackEvent::class,
        'CARD_ISSUING_ADJUSTMENT' => CardIssuingAdjustmentEvent::class,
        'CARD_ISSUING_ATM_FEE' => CardIssuingAtmFeeEvent::class,
        'CARD_ISSUING_ATM_WITHDRAWAL' => CardIssuingAtmWithdrawalEvent::class,
        'CARD_ADDED_SUCCESSFULLY' => CardAddedSuccessfullyEvent::class,
        'CARD_ISSUING_TRANSACTION_COMPLETED' => CardIssuingTxnCompletedEvent::class,
        'VERIFY_APPLICATION_SUBMITTED' => VerifyApplicationSubmittedEvent::class,
        'VERIFY_APPLICATION_APPROVED' => VerifyApplicationApprovedEvent::class,
        'VERIFY_APPLICATION_REJECTED' => VerifyApplicationRejectedEvent::class,
        'VIRTUAL_ACCOUNT_CREATED' => VirtualAccountCreatedEvent::class,
        'VIRTUAL_ACCOUNT_UPDATED' => VirtualAccountUpdatedEvent::class,
        'VIRTUAL_ACCOUNT_CLOSED' => VirtualAccountClosedEvent::class,
        'VIRTUAL_ACCOUNT_TRANSACTION' => VirtualAccountTransactionEvent::class,
    ];

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        $type = $payload['type'] ?? '';
        $data = $payload['data'] ?? [];
        $webhookId = $payload['id'] ?? '';
        $timestamp = (int) ($payload['created_at'] ?? 0);
        $triggerOperationId = $payload['trigger_operation_id'] ?? '';

        event(new RapydWebhookReceived($type, $data, $webhookId, $timestamp, $triggerOperationId));

        $eventClass = self::EVENT_MAP[$type] ?? null;
        if ($eventClass !== null) {
            event(new $eventClass($type, $data, $webhookId, $timestamp, $triggerOperationId));
        }

        return response()->json(['status' => 'ok']);
    }
}
