<?php

namespace App\Exports\Sheets;

use App\Models\Package;
use App\Models\DateBlocker;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DateBlockersSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Package $package) {}

    public function title(): string
    {
        return 'Date Blockers';
    }

    public function headings(): array
    {
        return ['Start Date', 'End Date', 'Room Type'];
    }

    public function collection()
    {
        return DateBlocker::with('roomType')
            ->where('package_id', $this->package->id)
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($b) => [
                $b->start_date?->format('d/m/Y') ?? '',
                $b->end_date?->format('d/m/Y') ?? '',
                $b->room_type_id ? ($b->roomType->name ?? '') : 'All Room Types',
            ]);
    }
}
