<?php

namespace App\Console\Commands;

use App\Models\RoomType;
use App\Services\CreatePriceConfigurationsService;
use Illuminate\Console\Command;

// can be used to fix max_pax limits for room types
class UpdateRoomConfigByPax extends Command
{
    protected $signature = 'update:room-config-by-pax';
    protected $description = 'Update room configurations based on number of pax';

    protected $priceConfigurationService;

    public function __construct(CreatePriceConfigurationsService $priceConfigurationService)
    {
        parent::__construct();
        $this->priceConfigurationService = $priceConfigurationService;
    }

    public function handle()
    {
        $this->info("Starting room config update by pax...");

        RoomType::all()->each(function ($roomType) {
            $this->info("Updating room config for room type: {$roomType->name}");
            $this->priceConfigurationService->updateConfigsToPaxAndFill($roomType->id, $roomType->max_occupancy);
            $this->priceConfigurationService->cleanPriceConfigurationsByMaxPax($roomType);
        });

        return Command::SUCCESS;
    }
}
