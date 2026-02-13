<?php

namespace App\Exports;

use App\Models\Package;
use App\Exports\Sheets\RoomTypesSheet;
use App\Exports\Sheets\SeasonTypesSheet;
use App\Exports\Sheets\DateTypeRangesSheet;
use App\Exports\Sheets\PriceConfigurationSheet;
use App\Exports\Sheets\DateBlockersSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class PackageExport implements WithMultipleSheets
{
    public function __construct(protected Package $package) {}

    public function sheets(): array
    {
        return [
            new RoomTypesSheet($this->package),
            new SeasonTypesSheet($this->package),
            new DateTypeRangesSheet($this->package),
            new PriceConfigurationSheet($this->package),
            new DateBlockersSheet($this->package),
        ];
    }
}
