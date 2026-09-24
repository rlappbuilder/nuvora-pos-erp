<?php

namespace App\Services\User;

use App\Models\MasterData\Branch;
use App\Models\User;
use App\Models\UserBranch;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class UserBranchService
{
    /**
     * Sync branch access untuk user.
     *
     * @param  User  $user
     * @param  array<int>  $branchIds
     */
    public function sync(
        User $user,
        array $branchIds
    ): void {
        DB::transaction(function () use (
            $user,
            $branchIds
        ) {

            $branchIds = collect($branchIds)
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all();

            if (empty($branchIds)) {
                throw new RuntimeException(
                    'User minimal harus memiliki satu akses cabang.'
                );
            }

            $branches = Branch::query()
                ->whereIn('id', $branchIds)
                ->get();

            if (
                $branches->count() !== count($branchIds)
            ) {
                throw new RuntimeException(
                    'Terdapat cabang yang tidak ditemukan.'
                );
            }

            $invalidCompany = $branches->contains(
                fn (Branch $branch) =>
                    (int) $branch->company_id !==
                    (int) $user->company_id
            );

            if ($invalidCompany) {
                throw new RuntimeException(
                    'User hanya dapat diberikan akses ke cabang dalam company yang sama.'
                );
            }

            $existingDefault = UserBranch::query()
                ->where('user_id', $user->id)
                ->where('is_default', true)
                ->first();

            $currentDefaultBranchId =
                $existingDefault?->branch_id;

            $user->branches()->sync(
                collect($branchIds)
                    ->mapWithKeys(
                        fn ($branchId) => [
                            $branchId => [
                                'is_default' =>
                                    $branchId ===
                                    $currentDefaultBranchId,
                            ],
                        ]
                    )
                    ->all()
            );

            $hasDefault = UserBranch::query()
                ->where('user_id', $user->id)
                ->where('is_default', true)
                ->exists();

            if (! $hasDefault) {
                UserBranch::query()
                    ->where('user_id', $user->id)
                    ->update([
                        'is_default' => false,
                    ]);

                UserBranch::query()
                    ->where('user_id', $user->id)
                    ->where(
                        'branch_id',
                        $branchIds[0]
                    )
                    ->update([
                        'is_default' => true,
                    ]);
            }
        });
    }

    /**
     * Set default branch user.
     */
    public function setDefault(
        User $user,
        Branch $branch
    ): void {
        if (
            (int) $branch->company_id !==
            (int) $user->company_id
        ) {
            throw new RuntimeException(
                'Cabang harus berasal dari company yang sama dengan user.'
            );
        }

        $hasAccess = UserBranch::query()
            ->where('user_id', $user->id)
            ->where('branch_id', $branch->id)
            ->exists();

        if (! $hasAccess) {
            throw new RuntimeException(
                'User tidak memiliki akses ke cabang tersebut.'
            );
        }

        DB::transaction(function () use (
            $user,
            $branch
        ) {

            UserBranch::query()
                ->where('user_id', $user->id)
                ->update([
                    'is_default' => false,
                ]);

            UserBranch::query()
                ->where('user_id', $user->id)
                ->where(
                    'branch_id',
                    $branch->id
                )
                ->update([
                    'is_default' => true,
                ]);
        });
    }

    /**
     * Check apakah user memiliki akses ke branch.
     */
    public function hasAccess(
        User $user,
        int $branchId
    ): bool {
        return UserBranch::query()
            ->where('user_id', $user->id)
            ->where('branch_id', $branchId)
            ->exists();
    }

    /**
     * Get default branch user.
     */
    public function getDefaultBranch(
        User $user
    ): ?Branch {
        return $user->branches()
            ->wherePivot(
                'is_default',
                true
            )
            ->first();
    }

    /**
     * Get current branch user.
     *
     * Jika current branch belum ada,
     * gunakan default branch.
     */
    public function getCurrentBranch(
        User $user
    ): ?Branch {
        $currentBranchId =
            session('current_branch_id');

        if ($currentBranchId) {
            $branch = $user->branches()
                ->where(
                    'branches.id',
                    $currentBranchId
                )
                ->first();

            if ($branch) {
                return $branch;
            }

            /*
             * Current branch sudah tidak valid
             * atau user sudah tidak memiliki akses.
             */
            session()->forget(
                'current_branch_id'
            );
        }

        $defaultBranch =
            $this->getDefaultBranch($user);

        if ($defaultBranch) {
            session([
                'current_branch_id' =>
                    $defaultBranch->id,
            ]);
        }

        return $defaultBranch;
    }

    /**
     * Set current branch user.
     */
    public function setCurrentBranch(
        User $user,
        int $branchId
    ): Branch {
        $branch = $user->branches()
            ->where(
                'branches.id',
                $branchId
            )
            ->first();

        if (! $branch) {
            throw new RuntimeException(
                'User tidak memiliki akses ke cabang tersebut.'
            );
        }

        session([
            'current_branch_id' =>
                $branch->id,
        ]);

        return $branch;
    }

    /**
     * Clear current branch.
     */
    public function clearCurrentBranch(): void
    {
        session()->forget(
            'current_branch_id'
        );
    }
}