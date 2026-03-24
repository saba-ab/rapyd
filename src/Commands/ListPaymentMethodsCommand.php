<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Commands;

use Illuminate\Console\Command;
use Sabaab\Rapyd\Exceptions\RapydException;
use Sabaab\Rapyd\Rapyd;

final class ListPaymentMethodsCommand extends Command
{
    protected $signature = 'rapyd:list-payment-methods {country : Two-letter country code (e.g. US, GB, DE)}';

    protected $description = 'List available payment methods for a country';

    public function handle(Rapyd $rapyd): int
    {
        $country = strtoupper($this->argument('country'));

        if (strlen($country) !== 2) {
            $this->error("Invalid country code: {$country}. Must be a 2-letter ISO code (e.g. US, GB, DE).");

            return self::FAILURE;
        }

        try {
            $methods = $rapyd->paymentMethods()->listByCountry($country);

            if ($methods === []) {
                $this->warn("No payment methods found for {$country}");

                return self::SUCCESS;
            }

            $rows = array_map(fn (array $method) => [
                $method['type'] ?? 'N/A',
                $method['name'] ?? 'N/A',
                $method['category'] ?? 'N/A',
                isset($method['currencies']) ? implode(', ', $method['currencies']) : 'N/A',
                ($method['is_refundable'] ?? false) ? 'Yes' : 'No',
            ], $methods);

            $this->table(['Type', 'Name', 'Category', 'Currencies', 'Refundable'], $rows);
            $this->info('Found '.count($methods)." payment methods for {$country}");

            return self::SUCCESS;
        } catch (RapydException $e) {
            $this->error("API Error: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
