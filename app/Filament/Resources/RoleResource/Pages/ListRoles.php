<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Exports\PermissionsExport;
use App\Filament\Resources\RoleResource;
use App\Models\Permission;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),

            Actions\Action::make('export')
                ->label(trans('admin.global.buttons.export'))
                ->icon('heroicon-m-arrow-down-tray')
                ->action(fn () => Excel::download(new PermissionsExport, 'permissions-export.xlsx'))
                ->visible(fn () => Permission::exists()),
        ];
    }
}
