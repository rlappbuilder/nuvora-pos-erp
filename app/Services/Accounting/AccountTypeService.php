<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountType;

class AccountTypeService
{
    public function create(array $data): AccountType
    {
        $data['created_by'] = auth()->id();

        return AccountType::create($data);
    }

    public function update(AccountType $accountType, array $data): AccountType
    {
        $data['updated_by'] = auth()->id();

        $accountType->update($data);

        return $accountType->fresh();
    }

    public function delete(AccountType $accountType): void
    {
        $accountType->update([
            'deleted_by' => auth()->id(),
        ]);

        $accountType->delete();
    }

    public function restore(AccountType $accountType): void
    {
        $accountType->restore();

        $accountType->update([
            'deleted_by' => null,
        ]);
    }
}