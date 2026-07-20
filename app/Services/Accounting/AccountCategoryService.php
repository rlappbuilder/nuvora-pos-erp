<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountCategory;

class AccountGroupService
{
    public function create(array $data): AccountGroup
    {
        $data['created_by'] = auth()->id();

        return AccountGroup::create($data);
    }

    public function update(AccountGroup $accountGroup, array $data): AccountGroup
    {
        $data['updated_by'] = auth()->id();

        $accountGroup->update($data);

        return $accountGroup->fresh();
    }

    public function delete(AccountGroup $accountGroup): void
    {
        $accountGroup->update([
            'deleted_by' => auth()->id(),
        ]);

        $accountGroup->delete();
    }

    public function restore(AccountGroup $accountGroup): void
    {
        $accountGroup->restore();

        $accountGroup->update([
            'deleted_by' => null,
        ]);
    }
}