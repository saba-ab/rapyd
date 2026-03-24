<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Enums;

enum WebhookEventType: string
{
    // Payments
    case PaymentCompleted = 'PAYMENT_COMPLETED';
    case PaymentSucceeded = 'PAYMENT_SUCCEEDED';
    case PaymentFailed = 'PAYMENT_FAILED';
    case PaymentExpired = 'PAYMENT_EXPIRED';
    case PaymentUpdated = 'PAYMENT_UPDATED';
    case PaymentCaptured = 'PAYMENT_CAPTURED';
    case PaymentCanceled = 'PAYMENT_CANCELED';
    case PaymentRefundCompleted = 'PAYMENT_REFUND_COMPLETED';
    case PaymentRefundFailed = 'PAYMENT_REFUND_FAILED';
    case PaymentRefundRejected = 'PAYMENT_REFUND_REJECTED';
    case PaymentDisputeCreated = 'PAYMENT_DISPUTE_CREATED';
    case PaymentDisputeUpdated = 'PAYMENT_DISPUTE_UPDATED';

    // Refunds
    case RefundCompleted = 'REFUND_COMPLETED';
    case RefundFailed = 'REFUND_FAILED';
    case RefundRejected = 'REFUND_REJECTED';

    // Customers
    case CustomerCreated = 'CUSTOMER_CREATED';
    case CustomerUpdated = 'CUSTOMER_UPDATED';
    case CustomerDeleted = 'CUSTOMER_DELETED';
    case CustomerPaymentMethodCreated = 'CUSTOMER_PAYMENT_METHOD_CREATED';
    case CustomerPaymentMethodUpdated = 'CUSTOMER_PAYMENT_METHOD_UPDATED';
    case CustomerPaymentMethodDeleted = 'CUSTOMER_PAYMENT_METHOD_DELETED';
    case CustomerPaymentMethodExpiring = 'CUSTOMER_PAYMENT_METHOD_EXPIRING';

    // Subscriptions
    case SubscriptionCreated = 'CUSTOMER_SUBSCRIPTION_CREATED';
    case SubscriptionUpdated = 'CUSTOMER_SUBSCRIPTION_UPDATED';
    case SubscriptionCompleted = 'CUSTOMER_SUBSCRIPTION_COMPLETED';
    case SubscriptionCanceled = 'CUSTOMER_SUBSCRIPTION_CANCELED';
    case SubscriptionPastDue = 'CUSTOMER_SUBSCRIPTION_PAST_DUE';
    case SubscriptionTrialEnd = 'CUSTOMER_SUBSCRIPTION_TRIAL_END';
    case SubscriptionRenewed = 'CUSTOMER_SUBSCRIPTION_RENEWED';

    // Invoices
    case InvoiceCreated = 'INVOICE_CREATED';
    case InvoiceUpdated = 'INVOICE_UPDATED';
    case InvoicePaymentCreated = 'INVOICE_PAYMENT_CREATED';
    case InvoicePaymentSucceeded = 'INVOICE_PAYMENT_SUCCEEDED';
    case InvoicePaymentFailed = 'INVOICE_PAYMENT_FAILED';

    // Payouts
    case PayoutCompleted = 'PAYOUT_COMPLETED';
    case PayoutUpdated = 'PAYOUT_UPDATED';
    case PayoutFailed = 'PAYOUT_FAILED';
    case PayoutExpired = 'PAYOUT_EXPIRED';
    case PayoutCanceled = 'PAYOUT_CANCELED';
    case PayoutReturned = 'PAYOUT_RETURNED';

    // Wallet
    case WalletTransaction = 'WALLET_TRANSACTION';
    case WalletFundsAdded = 'WALLET_FUNDS_ADDED';
    case WalletFundsRemoved = 'WALLET_FUNDS_REMOVED';
    case WalletTransferCompleted = 'WALLET_TRANSFER_COMPLETED';
    case WalletTransferFailed = 'WALLET_TRANSFER_FAILED';
    case WalletTransferResponseReceived = 'WALLET_TRANSFER_RESPONSE_RECEIVED';

    // Card Issuing
    case CardIssuingAuthApproved = 'CARD_ISSUING_AUTHORIZATION_APPROVED';
    case CardIssuingAuthDeclined = 'CARD_ISSUING_AUTHORIZATION_DECLINED';
    case CardIssuingSale = 'CARD_ISSUING_SALE';
    case CardIssuingCredit = 'CARD_ISSUING_CREDIT';
    case CardIssuingReversal = 'CARD_ISSUING_REVERSAL';
    case CardIssuingRefund = 'CARD_ISSUING_REFUND';
    case CardIssuingChargeback = 'CARD_ISSUING_CHARGEBACK';
    case CardIssuingAdjustment = 'CARD_ISSUING_ADJUSTMENT';
    case CardIssuingAtmFee = 'CARD_ISSUING_ATM_FEE';
    case CardIssuingAtmWithdrawal = 'CARD_ISSUING_ATM_WITHDRAWAL';
    case CardAddedSuccessfully = 'CARD_ADDED_SUCCESSFULLY';
    case CardIssuingTxnCompleted = 'CARD_ISSUING_TRANSACTION_COMPLETED';

    // Verify
    case VerifyApplicationSubmitted = 'VERIFY_APPLICATION_SUBMITTED';
    case VerifyApplicationApproved = 'VERIFY_APPLICATION_APPROVED';
    case VerifyApplicationRejected = 'VERIFY_APPLICATION_REJECTED';

    // Virtual Accounts
    case VirtualAccountCreated = 'VIRTUAL_ACCOUNT_CREATED';
    case VirtualAccountUpdated = 'VIRTUAL_ACCOUNT_UPDATED';
    case VirtualAccountClosed = 'VIRTUAL_ACCOUNT_CLOSED';
    case VirtualAccountTransaction = 'VIRTUAL_ACCOUNT_TRANSACTION';
}
