<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Services\UserAdministrationService;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->using(function (User $record) {
                    /** @var User $actor */
                    $actor = auth()->user();

                    return app(UserAdministrationService::class)->deleteUser($actor, $record);
                }),
        ];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var User $actor */
        $actor = auth()->user();
        /** @var User $record */
        $service = app(UserAdministrationService::class);

        if (isset($data['role']) && $data['role'] !== $record->role) {
            $service->updateRole($actor, $record, $data['role']);
            unset($data['role']);
        }

        $record->update($data);

        return $record;
    }
}
