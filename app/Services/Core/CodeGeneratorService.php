<?php

namespace App\Services\Core;

use App\Models\Core\CodeGenerator;
use Illuminate\Support\Facades\DB;

class CodeGeneratorService
{
    public function preview(string $module): string
    {
        $generator = CodeGenerator::where('module', $module)
            ->where('is_active', true)
            ->firstOrFail();

        return $this->formatCode(
            $generator,
            $generator->next_number
        );
    }

    public function next(string $module): string
    {
        return DB::transaction(function () use ($module) {

            $generator = CodeGenerator::lockForUpdate()
                ->where('module', $module)
                ->where('is_active', true)
                ->firstOrFail();

            $code = $this->formatCode(
                $generator,
                $generator->next_number
            );

            $generator->increment('next_number');

            return $code;
        });
    }
public function sync(
    string $module,
    int $lastNumber
): void {

    $generator = CodeGenerator::where(
        'module',
        $module
    )->firstOrFail();

    $generator->update([
        'next_number' => $lastNumber + 1,
    ]);

}
    protected function formatCode(
        CodeGenerator $generator,
        int $number
    ): string {

        $sequence = str_pad(
            $number,
            $generator->digit,
            '0',
            STR_PAD_LEFT
        );

        return str_replace(
            [
                '{PREFIX}',
                '{YYYY}',
                '{YY}',
                '{MM}',
                '{DD}',
                '{SEQ}',
            ],
            [
                $generator->prefix,
                now()->format('Y'),
                now()->format('y'),
                now()->format('m'),
                now()->format('d'),
                $sequence,
            ],
            $generator->format
        );
    }
}

