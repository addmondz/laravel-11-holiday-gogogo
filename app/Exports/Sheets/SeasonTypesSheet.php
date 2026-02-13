<?php

namespace App\Exports\Sheets;

use App\Models\Package;
use App\Models\Season;
use App\Models\SeasonType;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SeasonTypesSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Package $package) {}

    public function title(): string
    {
        return 'Season Types';
    }

    public function headings(): array
    {
        return ['Season Type Name', 'Start Date', 'End Date'];
    }

    public function collection()
    {
        $defaultSeasonTypeId = SeasonType::where('name', 'Default')->value('id');

        return Season::with('type')
            ->where('package_id', $this->package->id)
            ->whereHas('type', fn($q) => $q->where('id', '!=', $defaultSeasonTypeId))
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(fn($s) => [
                $s->type->name ?? '',
                $s->start_date?->format('d/m/Y') ?? '',
                $s->end_date?->format('d/m/Y') ?? '',
            ]);
    }
}
