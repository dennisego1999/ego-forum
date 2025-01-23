<?php

namespace App\Filament\Resources\RoleResource\Forms;

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Tabs\Tab;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PermissionFields
{
    public static function tab(): Tab
    {
        return Tab::make(trans('admin.roles.titles.permissions'))
            ->badge(fn (Forms\Get $get) => collect($get('models'))
                ->flatten()
                ->count()
            )
            ->label(trans('admin.roles.titles.permissions'))
            ->schema(self::getPermissionsSchema())
            ->columns();
    }

    private static function getPermissionsSchema(): array
    {
        $models = Permission::getModels();

        // Build sections for every model
        return Arr::map($models, static function ($model) {
            $instance = app($model);
            $permissions = Permission::getModelPermissions($instance);

            // Decide the model permissions name
            $modelHeadline = Permission::getModelHeadline($model);
            $modelName = Permission::getModelName($model);

            // Map the available permissions
            $permissions = collect($permissions)
                ->mapWithKeys(static function (string $permission) use ($modelName) {
                    return ["{$permission}_$modelName" => Str::headline($permission)];
                });

            // Build out the section for the model
            return Forms\Components\Section::make($modelHeadline)
                ->compact()
                ->collapsible()
                ->statePath('models')
                ->columnSpan(1)
                ->schema([
                    Forms\Components\CheckboxList::make($modelName)
                        ->reactive()
                        ->hiddenLabel()
                        ->options($permissions)
                        ->formatStateUsing(fn (Role|User|null $record = null) => $permissions
                            ->filter(fn ($permission, $name) => $record?->hasPermissionTo($name))
                            ->keys()
                            ->toArray()
                        )
                        ->disableOptionWhen(function ($value, Role|User|null $record = null) {
                            // Never disable individual options for roles
                            if ($record instanceof Role) {
                                return false;
                            }

                            // Disable roles inherited by roles
                            return $record?->hasPermissionTo($value) && ! $record?->hasDirectPermission($value);
                        })
                        ->columns(3),
                ]);
        });
    }
}
