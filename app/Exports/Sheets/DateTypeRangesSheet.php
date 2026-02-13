<?php

namespace App\Exports\Sheets;

use App\Models\Package;
use App\Models\DateType;
use App\Models\DateTypeRange;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DateTypeRangesSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Package $package) {}

    public function title(): string
    {
        return 'Date Type Ranges';
    }

    public function headings(): array
    {
        return ['Date Type Name', 'Start Date', 'End Date'];
    }

    public function collection()
    {
        $defaultTypeIds = DateType::whereIn('name', ['Default', 'Weekday', 'Weekend'])->pluck('id');

        return DateTypeRange::with('dateType')
            ->where('package_id', $this->package->id)
            ->whereHas('dateType', fn($q) => $q->whereNotIn('id', $defaultTypeIds))
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($r) => [
                $r->dateType->name ?? '',
                $r->start_date?->format('d/m/Y') ?? '',
                $r->end_date?->format('d/m/Y') ?? '',
            ]);
    }
}
