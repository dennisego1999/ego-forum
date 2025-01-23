<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Filament\Resources\RoleResource\Forms\PermissionFields;
use App\Filament\Resources\RoleResource\Pages;
use App\Models\Role;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Gate;

class RoleResource extends Resource
{
    protected static ?string $model = Role::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getLabel(): ?string
    {
        return trans_choice('models.roles.label', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('models.roles.label', 0);
    }

    public static function getRecordTitle(?Model $record): string|Htmlable|null
    {
        return $record?->label;
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin.navigation_groups.user_management');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label(trans('admin.global.attributes.name'))
                    ->disabled(function (?Role $record = null) {
                        return $record && Gate::denies('updateProperties', $record);
                    })
                    ->required()
                    ->rules(['lowercase', 'alpha_dash'])
                    ->maxLength(255),

                // Permissions
                Forms\Components\Tabs::make('permissions')
                    ->statePath('permissions')
                    ->schema([
                        PermissionFields::tab(),
                    ])
                    ->columnSpanFull()
                    ->hidden(function ($context, ?Role $record = null) {
                        // Hide while creating roles
                        if ($context === 'create') {
                            return true;
                        }

                        // Hide for the super admin role
                        return $record->name === RoleEnum::SUPER_ADMIN->value;
                    }),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('admin.global.attributes.name'))
                    ->formatStateUsing(fn ($record) => $record->label)
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('users_count')
                    ->label(trans('admin.global.columns.users_count'))
                    ->counts('users')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
