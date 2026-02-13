<?php

namespace App\Exports\Sheets;

use App\Models\Package;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class RoomTypesSheet implements FromCollection, WithTitle, WithHeadings, ShouldAutoSize
{
    public function __construct(protected Package $package) {}

    public function title(): string
    {
        return 'Room Types';
    }

    public function headings(): array
    {
        return ['Name', 'Max Occupancy', 'Max Adults', 'Max Children', 'Max Infants', 'Bed Description', 'Description'];
    }

    public function collection()
    {
        return $this->package->loadRoomTypes()
            ->orderBy('sequence')
            ->get()
            ->map(fn($rt) => [
                $rt->name,
                $rt->max_occupancy,
                $rt->max_adults,
                $rt->max_children,
                $rt->max_infants,
                $rt->bed_desc,
                $rt->description,
            ]);
    }
}
