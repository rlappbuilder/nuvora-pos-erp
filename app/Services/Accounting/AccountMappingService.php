<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountMapping;
use App\Models\Accounting\ChartOfAccount;
use RuntimeException;

class AccountMappingService
{
    public function getAccount(
        int $companyId,
        string $key
    ): ChartOfAccount {
        $mapping = AccountMapping::query()
            ->with('account')
            ->where('company_id', $companyId)
            ->where('key', $key)
            ->where('is_active', true)
            ->first();

        if (! $mapping || ! $mapping->account) {
            throw new RuntimeException(
                "Account mapping [{$key}] is not configured for company [{$companyId}]."
            );
        }

        return $mapping->account;
    }
}