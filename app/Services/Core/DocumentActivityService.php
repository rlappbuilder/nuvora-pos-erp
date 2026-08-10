<?php

namespace App\Services\Core;

use App\Models\Core\DocumentActivity;
use Illuminate\Database\Eloquent\Model;

class DocumentActivityService
{
    /*
    |--------------------------------------------------------------------------
    | Record Activity
    |--------------------------------------------------------------------------
    */

    public function record(
        Model $document,
        string $action,
        ?string $oldStatus = null,
        ?string $newStatus = null,
        ?string $description = null,
        ?array $metadata = null
    ): DocumentActivity {

        return DocumentActivity::create([

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            'company_id' =>
                $document->company_id,

            /*
            |--------------------------------------------------------------------------
            | Document
            |--------------------------------------------------------------------------
            */

            'document_type' =>
                $this->resolveDocumentType(
                    $document
                ),

            'document_id' =>
                $document->id,

            /*
            |--------------------------------------------------------------------------
            | Activity
            |--------------------------------------------------------------------------
            */

            'action' =>
                $action,

            'old_status' =>
                $oldStatus,

            'new_status' =>
                $newStatus,

            'description' =>
                $description,

            'metadata' =>
                $metadata,

            /*
            |--------------------------------------------------------------------------
            | Audit
            |--------------------------------------------------------------------------
            */

            'performed_by' =>
                auth()->id(),

            'performed_at' =>
                now(),

        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Document Type
    |--------------------------------------------------------------------------
    */

    private function resolveDocumentType(
        Model $document
    ): string {

        return class_basename(
            $document
        );
    }
}