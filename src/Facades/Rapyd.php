<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sabaab\Rapyd\Rapyd
 *
 * @method static \Sabaab\Rapyd\Client\RapydClient client()
 * @method static \Sabaab\Rapyd\Resources\Collect\PaymentResource payments()
 * @method static \Sabaab\Rapyd\Resources\Collect\RefundResource refunds()
 * @method static \Sabaab\Rapyd\Resources\Collect\CustomerResource customers()
 * @method static \Sabaab\Rapyd\Resources\Collect\CheckoutResource checkout()
 * @method static \Sabaab\Rapyd\Resources\Collect\PaymentMethodResource paymentMethods()
 * @method static \Sabaab\Rapyd\Resources\Collect\PaymentLinkResource paymentLinks()
 * @method static \Sabaab\Rapyd\Resources\Collect\SubscriptionResource subscriptions()
 * @method static \Sabaab\Rapyd\Resources\Collect\PlanResource plans()
 * @method static \Sabaab\Rapyd\Resources\Collect\ProductResource products()
 * @method static \Sabaab\Rapyd\Resources\Collect\InvoiceResource invoices()
 * @method static \Sabaab\Rapyd\Resources\Collect\DisputeResource disputes()
 * @method static \Sabaab\Rapyd\Resources\Collect\EscrowResource escrows()
 * @method static \Sabaab\Rapyd\Resources\Disburse\PayoutResource payouts()
 * @method static \Sabaab\Rapyd\Resources\Disburse\PayoutMethodResource payoutMethods()
 * @method static \Sabaab\Rapyd\Resources\Disburse\BeneficiaryResource beneficiaries()
 * @method static \Sabaab\Rapyd\Resources\Disburse\SenderResource senders()
 * @method static \Sabaab\Rapyd\Resources\Wallet\WalletResource wallets()
 * @method static \Sabaab\Rapyd\Resources\Wallet\WalletContactResource walletContacts()
 * @method static \Sabaab\Rapyd\Resources\Wallet\WalletTransferResource walletTransfers()
 * @method static \Sabaab\Rapyd\Resources\Wallet\WalletTransactionResource walletTransactions()
 * @method static \Sabaab\Rapyd\Resources\Wallet\VirtualAccountResource virtualAccounts()
 * @method static \Sabaab\Rapyd\Resources\Issuing\CardResource cards()
 * @method static \Sabaab\Rapyd\Resources\Issuing\CardProgramResource cardPrograms()
 * @method static \Sabaab\Rapyd\Resources\Verify\IdentityResource identities()
 * @method static \Sabaab\Rapyd\Resources\Verify\VerificationResource verification()
 * @method static \Sabaab\Rapyd\Resources\Protect\FraudResource fraud()
 * @method static \Sabaab\Rapyd\Resources\Data\DataResource data()
 */
class Rapyd extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sabaab\Rapyd\Rapyd::class;
    }
}
