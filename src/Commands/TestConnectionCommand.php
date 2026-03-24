<?php

declare(strict_types=1);

namespace Sabaab\Rapyd\Commands;

use Illuminate\Console\Command;
use Sabaab\Rapyd\Exceptions\AuthenticationException;
use Sabaab\Rapyd\Exceptions\RapydException;
use Sabaab\Rapyd\Rapyd;

final class TestConnectionCommand extends Command
{
    protected $signature = 'rapyd:test-connection';

    protected $description = 'Test connectivity to the Rapyd API';

    public function handle(Rapyd $rapyd): int
    {
        $sandbox = config('rapyd.sandbox');
        $environment = $sandbox ? 'Sandbox' : 'Production';
        $baseUrl = $sandbox
            ? config('rapyd.base_url.sandbox')
            : config('rapyd.base_url.production');

        $this->info("Environment: {$environment}");
        $this->info("Base URL: {$baseUrl}");
        $this->newLine();

        try {
            $countries = $rapyd->data()->countries();
            $count = count($countries);
            $this->info("Connected to Rapyd {$environment} — {$count} countries loaded");

            return self::SUCCESS;
        } catch (AuthenticationException $e) {
            $this->error('Authentication failed. Check your RAPYD_ACCESS_KEY and RAPYD_SECRET_KEY in .env');

            return self::FAILURE;
        } catch (RapydException $e) {
            $this->error("API Error: {$e->getMessage()}");

            return self::FAILURE;
        } catch (\Throwable $e) {
            $this->error("Connection failed: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
