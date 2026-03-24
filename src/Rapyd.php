<?php

declare(strict_types=1);

namespace Sabaab\Rapyd;

use Sabaab\Rapyd\Client\RapydClient;
use Sabaab\Rapyd\Resources\Collect\CheckoutResource;
use Sabaab\Rapyd\Resources\Collect\CustomerResource;
use Sabaab\Rapyd\Resources\Collect\DisputeResource;
use Sabaab\Rapyd\Resources\Collect\EscrowResource;
use Sabaab\Rapyd\Resources\Collect\InvoiceResource;
use Sabaab\Rapyd\Resources\Collect\PaymentLinkResource;
use Sabaab\Rapyd\Resources\Collect\PaymentMethodResource;
use Sabaab\Rapyd\Resources\Collect\PaymentResource;
use Sabaab\Rapyd\Resources\Collect\PlanResource;
use Sabaab\Rapyd\Resources\Collect\ProductResource;
use Sabaab\Rapyd\Resources\Collect\RefundResource;
use Sabaab\Rapyd\Resources\Collect\SubscriptionResource;
use Sabaab\Rapyd\Resources\Data\DataResource;
use Sabaab\Rapyd\Resources\Disburse\BeneficiaryResource;
use Sabaab\Rapyd\Resources\Disburse\PayoutMethodResource;
use Sabaab\Rapyd\Resources\Disburse\PayoutResource;
use Sabaab\Rapyd\Resources\Disburse\SenderResource;
use Sabaab\Rapyd\Resources\Issuing\CardProgramResource;
use Sabaab\Rapyd\Resources\Issuing\CardResource;
use Sabaab\Rapyd\Resources\Protect\FraudResource;
use Sabaab\Rapyd\Resources\Verify\IdentityResource;
use Sabaab\Rapyd\Resources\Verify\VerificationResource;
use Sabaab\Rapyd\Resources\Wallet\VirtualAccountResource;
use Sabaab\Rapyd\Resources\Wallet\WalletContactResource;
use Sabaab\Rapyd\Resources\Wallet\WalletResource;
use Sabaab\Rapyd\Resources\Wallet\WalletTransactionResource;
use Sabaab\Rapyd\Resources\Wallet\WalletTransferResource;

class Rapyd
{
    public function __construct(
        private readonly RapydClient $client,
    ) {}

    public function client(): RapydClient
    {
        return $this->client;
    }

    // Collect
    public function payments(): PaymentResource
    {
        return new PaymentResource($this->client);
    }

    public function refunds(): RefundResource
    {
        return new RefundResource($this->client);
    }

    public function customers(): CustomerResource
    {
        return new CustomerResource($this->client);
    }

    public function checkout(): CheckoutResource
    {
        return new CheckoutResource($this->client);
    }

    public function paymentMethods(): PaymentMethodResource
    {
        return new PaymentMethodResource($this->client);
    }

    public function paymentLinks(): PaymentLinkResource
    {
        return new PaymentLinkResource($this->client);
    }

    public function subscriptions(): SubscriptionResource
    {
        return new SubscriptionResource($this->client);
    }

    public function plans(): PlanResource
    {
        return new PlanResource($this->client);
    }

    public function products(): ProductResource
    {
        return new ProductResource($this->client);
    }

    public function invoices(): InvoiceResource
    {
        return new InvoiceResource($this->client);
    }

    public function disputes(): DisputeResource
    {
        return new DisputeResource($this->client);
    }

    public function escrows(): EscrowResource
    {
        return new EscrowResource($this->client);
    }

    // Disburse
    public function payouts(): PayoutResource
    {
        return new PayoutResource($this->client);
    }

    public function payoutMethods(): PayoutMethodResource
    {
        return new PayoutMethodResource($this->client);
    }

    public function beneficiaries(): BeneficiaryResource
    {
        return new BeneficiaryResource($this->client);
    }

    public function senders(): SenderResource
    {
        return new SenderResource($this->client);
    }

    // Wallet
    public function wallets(): WalletResource
    {
        return new WalletResource($this->client);
    }

    public function walletContacts(): WalletContactResource
    {
        return new WalletContactResource($this->client);
    }

    public function walletTransfers(): WalletTransferResource
    {
        return new WalletTransferResource($this->client);
    }

    public function walletTransactions(): WalletTransactionResource
    {
        return new WalletTransactionResource($this->client);
    }

    public function virtualAccounts(): VirtualAccountResource
    {
        return new VirtualAccountResource($this->client);
    }

    // Issuing
    public function cards(): CardResource
    {
        return new CardResource($this->client);
    }

    public function cardPrograms(): CardProgramResource
    {
        return new CardProgramResource($this->client);
    }

    // Verify
    public function identities(): IdentityResource
    {
        return new IdentityResource($this->client);
    }

    public function verification(): VerificationResource
    {
        return new VerificationResource($this->client);
    }

    // Protect
    public function fraud(): FraudResource
    {
        return new FraudResource($this->client);
    }

    // Data
    public function data(): DataResource
    {
        return new DataResource($this->client);
    }
}
