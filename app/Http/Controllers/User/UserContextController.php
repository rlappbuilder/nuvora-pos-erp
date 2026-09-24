<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\User\UserBranchService;
use Illuminate\Http\Request;

class UserContextController extends Controller
{
    protected UserBranchService $userBranchService;

    public function __construct(
        UserBranchService $userBranchService
    ) {
        $this->userBranchService =
            $userBranchService;
    }

    /**
     * Switch current branch.
     */
    public function switchBranch(
        Request $request
    ) {
        $request->validate([
            'branch_id' => [
                'required',
                'integer',
            ],
        ]);

        $user = $request->user();

        $this->userBranchService->setCurrentBranch(
            $user,
            (int) $request->branch_id
        );

        return redirect()
            ->back()
            ->with(
                'success',
                'Current branch updated successfully.'
            );
    }
}