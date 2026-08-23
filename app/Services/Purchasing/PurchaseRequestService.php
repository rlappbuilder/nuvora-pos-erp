<?php

namespace App\Services\Purchasing;

use App\Models\Purchasing\PurchaseRequestHeader;
use App\Models\Purchasing\PurchaseRequestDetail;
use App\Services\Core\CodeGeneratorService;
use App\Services\Core\DocumentActivityService;
use Illuminate\Support\Facades\DB;

class PurchaseRequestService
{
    protected CodeGeneratorService $codeGeneratorService;

    protected DocumentActivityService $documentActivityService;

    public function __construct(
        CodeGeneratorService $codeGeneratorService,
        DocumentActivityService $documentActivityService
    ) {
        $this->codeGeneratorService =
            $codeGeneratorService;

        $this->documentActivityService =
            $documentActivityService;
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function createPurchaseRequest(
        array $data
    ): PurchaseRequestHeader {

        return DB::transaction(
            function () use ($data) {

                /*
                |--------------------------------------------------------------------------
                | Create Header - DRAFT
                |--------------------------------------------------------------------------
                */

                $header =
                    PurchaseRequestHeader::create([

                        'company_id' =>
                            $data['company_id'],

                        'branch_id' =>
                            $data['branch_id'],

                        'warehouse_id' =>
                            $data['warehouse_id'],

                        'number' =>
                            $this
                                ->codeGeneratorService
                                ->next(
                                    'purchase_request'
                                ),

                        'request_date' =>
                            $data['request_date'],

                        'required_date' =>
                            $data['required_date']
                            ?? null,

                        'priority' =>
                            $data['priority']
                            ?? 'Normal',

                        'status' =>
                            'Draft',

                        'description' =>
                            $data['description']
                            ?? null,

                        'created_by' =>
                            auth()->id(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Create Details
                |--------------------------------------------------------------------------
                */

                foreach (
                    $data['details']
                    as $detail
                ) {

                    PurchaseRequestDetail::create([

                        'purchase_request_id' =>
                            $header->id,

                        'product_variant_id' =>
                            $detail['product_variant_id'],

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty' =>
                            $detail['qty'],

                        'description' =>
                            $detail['description']
                            ?? null,

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Document Activity - CREATED
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $header,

                        'CREATED',

                        null,

                        'Draft',

                        'Purchase request created.'

                    );


                return $header;

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function updatePurchaseRequest(
        PurchaseRequestHeader $purchaseRequest,
        array $data
    ): void {

        DB::transaction(
            function () use (
                $purchaseRequest,
                $data
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $purchaseRequest =
                    PurchaseRequestHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseRequest->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    ! in_array(
                        $purchaseRequest->status,
                        [
                            'Draft',
                            'Rejected',
                        ],
                        true
                    )
                ) {

                    throw new \RuntimeException(
                        'Only Draft or Rejected purchase request can be edited.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Capture Old Status
                |--------------------------------------------------------------------------
                */

                $oldStatus =
                    $purchaseRequest->status;


                /*
                |--------------------------------------------------------------------------
                | Update Header
                |--------------------------------------------------------------------------
                */

                $purchaseRequest->update([

                    'company_id' =>
                        $data['company_id'],

                    'branch_id' =>
                        $data['branch_id'],

                    'warehouse_id' =>
                        $data['warehouse_id'],

                    'request_date' =>
                        $data['request_date'],

                    'required_date' =>
                        $data['required_date']
                        ?? null,

                    'priority' =>
                        $data['priority']
                        ?? 'Normal',

                    'description' =>
                        $data['description']
                        ?? null,

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Replace Details
                |--------------------------------------------------------------------------
                */

                $purchaseRequest
                    ->details()
                    ->delete();


                foreach (
                    $data['details']
                    as $detail
                ) {

                    PurchaseRequestDetail::create([

                        'purchase_request_id' =>
                            $purchaseRequest->id,

                        'product_variant_id' =>
                            $detail['product_variant_id'],

                        'unit_id' =>
                            $detail['unit_id'],

                        'qty' =>
                            $detail['qty'],

                        'description' =>
                            $detail['description']
                            ?? null,

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Rejected → Draft
                |--------------------------------------------------------------------------
                */

                if (
                    $oldStatus === 'Rejected'
                ) {

                    $purchaseRequest->update([

                        'status' =>
                            'Draft',

                        'rejected_at' =>
                            null,

                        'rejected_by' =>
                            null,

                        'rejected_reason' =>
                            null,

                        'updated_by' =>
                            auth()->id(),

                    ]);


                    $this
                        ->documentActivityService
                        ->record(

                            $purchaseRequest,

                            'RESUBMITTED',

                            'Rejected',

                            'Draft',

                            'Rejected purchase request was corrected and resubmitted.'

                        );

                }

                /*
                |--------------------------------------------------------------------------
                | Draft → Draft
                |--------------------------------------------------------------------------
                */

                else {

                    $this
                        ->documentActivityService
                        ->record(

                            $purchaseRequest,

                            'UPDATED',

                            'Draft',

                            'Draft',

                            'Purchase request updated.'

                        );

                }

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    public function submitPurchaseRequest(
        PurchaseRequestHeader $purchaseRequest
    ): void {

        DB::transaction(
            function () use ($purchaseRequest) {

                $purchaseRequest =
                    PurchaseRequestHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseRequest->id
                        );


                if (
                    $purchaseRequest->status !== 'Draft'
                ) {

                    throw new \RuntimeException(
                        'Only Draft purchase request can be submitted.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Details
                |--------------------------------------------------------------------------
                */

                if (
                    ! $purchaseRequest
                        ->details()
                        ->exists()
                ) {

                    throw new \RuntimeException(
                        'Purchase request must have at least one detail.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Submit
                |--------------------------------------------------------------------------
                */

                $purchaseRequest->update([

                    'status' =>
                        'Submitted',

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $purchaseRequest,

                        'SUBMITTED',

                        'Draft',

                        'Submitted',

                        'Purchase request submitted for approval.'

                    );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Approve
    |--------------------------------------------------------------------------
    */

    public function approvePurchaseRequest(
        PurchaseRequestHeader $purchaseRequest
    ): void {

        DB::transaction(
            function () use ($purchaseRequest) {

                $purchaseRequest =
                    PurchaseRequestHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseRequest->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseRequest->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted purchase request can be approved.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Approve
                |--------------------------------------------------------------------------
                */

                $purchaseRequest->update([

                    'status' =>
                        'Approved',

                    'approved_at' =>
                        now(),

                    'approved_by' =>
                        auth()->id(),

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $purchaseRequest,

                        'APPROVED',

                        'Submitted',

                        'Approved',

                        'Purchase request approved.'

                    );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reject
    |--------------------------------------------------------------------------
    */

    public function rejectPurchaseRequest(
        PurchaseRequestHeader $purchaseRequest,
        string $reason
    ): void {

        DB::transaction(
            function () use (
                $purchaseRequest,
                $reason
            ) {

                /*
                |--------------------------------------------------------------------------
                | Lock Header
                |--------------------------------------------------------------------------
                */

                $purchaseRequest =
                    PurchaseRequestHeader::query()
                        ->lockForUpdate()
                        ->findOrFail(
                            $purchaseRequest->id
                        );


                /*
                |--------------------------------------------------------------------------
                | Validate Status
                |--------------------------------------------------------------------------
                */

                if (
                    $purchaseRequest->status !== 'Submitted'
                ) {

                    throw new \RuntimeException(
                        'Only Submitted purchase request can be rejected.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Validate Reason
                |--------------------------------------------------------------------------
                */

                if (
                    trim($reason) === ''
                ) {

                    throw new \InvalidArgumentException(
                        'Rejection reason is required.'
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Reject
                |--------------------------------------------------------------------------
                */

                $purchaseRequest->update([

                    'status' =>
                        'Rejected',

                    'rejected_at' =>
                        now(),

                    'rejected_by' =>
                        auth()->id(),

                    'rejected_reason' =>
                        $reason,

                    'updated_by' =>
                        auth()->id(),

                ]);


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $purchaseRequest,

                        'REJECTED',

                        'Submitted',

                        'Rejected',

                        'Purchase request rejected.',

                        [

                            'reason' =>
                                $reason,

                        ]

                    );

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate
    |--------------------------------------------------------------------------
    */

    public function duplicatePurchaseRequest(
        PurchaseRequestHeader $purchaseRequest
    ): PurchaseRequestHeader {

        return DB::transaction(
            function () use ($purchaseRequest) {

                /*
                |--------------------------------------------------------------------------
                | Load Details
                |--------------------------------------------------------------------------
                */

                $purchaseRequest
                    ->load('details');


                /*
                |--------------------------------------------------------------------------
                | Create Duplicate Header
                |--------------------------------------------------------------------------
                */

                $duplicate =
                    PurchaseRequestHeader::create([

                        'company_id' =>
                            $purchaseRequest
                                ->company_id,

                        'branch_id' =>
                            $purchaseRequest
                                ->branch_id,

                        'warehouse_id' =>
                            $purchaseRequest
                                ->warehouse_id,

                        'number' =>
                            $this
                                ->codeGeneratorService
                                ->next(
                                    'purchase_request'
                                ),

                        'request_date' =>
                            $purchaseRequest
                                ->request_date,

                        'required_date' =>
                            $purchaseRequest
                                ->required_date,

                        'priority' =>
                            $purchaseRequest
                                ->priority,

                        'status' =>
                            'Draft',

                        'description' =>
                            $purchaseRequest
                                ->description
                                ? 'Copy - ' .
                                    $purchaseRequest
                                        ->description
                                : 'Copy Purchase Request',

                        'created_by' =>
                            auth()->id(),

                    ]);


                /*
                |--------------------------------------------------------------------------
                | Duplicate Details
                |--------------------------------------------------------------------------
                */

                foreach (
                    $purchaseRequest->details
                    as $detail
                ) {

                    PurchaseRequestDetail::create([

                        'purchase_request_id' =>
                            $duplicate->id,

                        'product_variant_id' =>
                            $detail
                                ->product_variant_id,

                        'unit_id' =>
                            $detail
                                ->unit_id,

                        'qty' =>
                            $detail->qty,

                        'description' =>
                            $detail
                                ->description,

                    ]);

                }


                /*
                |--------------------------------------------------------------------------
                | Activity
                |--------------------------------------------------------------------------
                */

                $this
                    ->documentActivityService
                    ->record(

                        $duplicate,

                        'CREATED',

                        null,

                        'Draft',

                        'Purchase request duplicated.'

                    );


                return $duplicate;

            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    public function deletePurchaseRequests(
        array $ids
    ): void {

        DB::transaction(
            function () use ($ids) {

                $purchaseRequests =
                    PurchaseRequestHeader::query()
                        ->whereIn(
                            'id',
                            $ids
                        )
                        ->lockForUpdate()
                        ->get();


                foreach (
                    $purchaseRequests
                    as $purchaseRequest
                ) {

                    /*
                    |--------------------------------------------------------------------------
                    | Validate Status
                    |--------------------------------------------------------------------------
                    */

                    if (
                        in_array(
                            $purchaseRequest->status,
                            [
                                'Submitted',
                                'Approved',
                            ],
                            true
                        )
                    ) {

                        throw new \RuntimeException(
                            'Submitted or Approved purchase request cannot be deleted.'
                        );

                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Delete Details
                    |--------------------------------------------------------------------------
                    */

                    $purchaseRequest
                        ->details()
                        ->delete();


                    /*
                    |--------------------------------------------------------------------------
                    | Delete Header
                    |--------------------------------------------------------------------------
                    */

                    $purchaseRequest->update([

                        'deleted_by' =>
                            auth()->id(),

                    ]);

                    $purchaseRequest->delete();

                }

            }
        );
    }
  public function cancelPurchaseRequest(
    PurchaseRequestHeader $purchaseRequest
): void {

    DB::transaction(
        function () use ($purchaseRequest) {

            $purchaseRequest =
                PurchaseRequestHeader::query()
                    ->lockForUpdate()
                    ->findOrFail(
                        $purchaseRequest->id
                    );


            /*
            |--------------------------------------------------------------------------
            | Validate Status
            |--------------------------------------------------------------------------
            */

            if (
                $purchaseRequest->status !== 'Approved'
            ) {

                throw new \RuntimeException(
                    'Only Approved purchase request can be cancelled.'
                );

            }


            /*
            |--------------------------------------------------------------------------
            | Cancel
            |--------------------------------------------------------------------------
            */

            $purchaseRequest->update([

                'status' =>
                    'Cancelled',

                'updated_by' =>
                    auth()->id(),

            ]);


            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            $this
                ->documentActivityService
                ->record(

                    $purchaseRequest,

                    'CANCELLED',

                    'Approved',

                    'Cancelled',

                    'Purchase request cancelled.'

                );

        }
    );
}
}