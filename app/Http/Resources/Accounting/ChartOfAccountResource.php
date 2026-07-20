<?php

namespace App\Http\Resources\Accounting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChartOfAccountResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'company' => [
                'id'   => $this->company?->id,
                'name' => $this->company?->name,
            ],

            'parent' => $this->parent ? [
                'id'   => $this->parent->id,
                'code' => $this->parent->code,
                'name' => $this->parent->name,
            ] : null,

            'account_category' => [
                'id'   => $this->accountCategory?->id,
                'code' => $this->accountCategory?->code,
                'name' => $this->accountCategory?->name,
            ],

            'code' => $this->code,
            'name' => $this->name,

            'normal_balance' => $this->normal_balance,

            'level' => $this->level,

            'is_header' => $this->is_header,

            'is_posting' => $this->is_posting,

            'opening_balance' => $this->opening_balance,

            'status' => $this->status,

            'description' => $this->description,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'deleted_at' => $this->deleted_at,
        ];
    }
}