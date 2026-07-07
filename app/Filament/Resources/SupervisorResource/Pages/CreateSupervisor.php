<?php

namespace App\Filament\Resources\SupervisorResource\Pages;

use App\Filament\Resources\SupervisorResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateSupervisor extends CreateRecord
{
    protected static string $resource = SupervisorResource::class;

    protected function afterCreate(): void
    {
        /** @var User $user */
        $user = $this->record;
        $user->syncRoles(['supervisor']);
    }
}
