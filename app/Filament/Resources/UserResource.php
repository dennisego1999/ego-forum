<?php

namespace App\Filament\Resources;

use App\Enums\RoleEnum;
use App\Filament\Resources\RoleResource\Forms\PermissionFields;
use App\Filament\Resources\UserResource\Pages;
use App\Models\Role;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

// use STS\FilamentImpersonate\Tables\Actions\Impersonate;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    public static function getNavigationGroup(): ?string
    {
        return trans('admin.navigation_groups.user_management');
    }

    protected static ?string $recordTitleAttribute = 'name';

    public static function getLabel(): ?string
    {
        return trans_choice('models.users.label', 1);
    }

    public static function getPluralLabel(): ?string
    {
        return trans_choice('models.users.label', 0);
    }

    public static function form(Form $form): Form
    {
        // Check which ID belongs to the super admin role
        $superAdminRoleId = Role::query()
            ->where('name', RoleEnum::SUPER_ADMIN)
            ->value('id');

        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make()
                            ->schema(static::getPersonalFields())
                            ->columns(),
                    ])
                    ->columnSpan(['lg' => 2]),

                // Add default roles
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Roles')
                            ->schema(static::getRoleFields()),
                    ])
                    ->columnSpan(['lg' => 1]),

                // Permissions
                Forms\Components\Tabs::make('permissions')
                    ->label(Str::headline(trans('admin.roles.titles.permissions')))
                    ->statePath('permissions')
                    ->schema([
                        PermissionFields::tab(),
                    ])
                    ->columnSpanFull()
                    ->visible(function ($context, Forms\Get $get, ?User $record = null) use ($superAdminRoleId) {
                        // Never show wile creating new users
                        if ($context === 'create') {
                            return false;
                        }

                        // Hide until hard reload when super admin on initial load
                        if ($record->isSuperAdmin()) {
                            return false;
                        }

                        // Always hide for super admins
                        return collect($get('role'))->doesntContain($superAdminRoleId);
                    }),
            ]);
    }

    private static function getPersonalFields(): array
    {
        return [
            Forms\Components\TextInput::make('name')
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('email')
                ->email()
                ->unique(ignorable: fn ($record) => $record)
                ->required()
                ->maxLength(255),
            Forms\Components\TextInput::make('password')
                ->password()
                ->autocomplete(false)
                ->required()
                ->maxLength(255)
                ->hiddenOn('edit'),
        ];
    }

    private static function getRoleFields(): array
    {
        return [
            Forms\Components\Select::make('role')
                ->relationship('roles', 'name')
                ->getOptionLabelFromRecordUsing(fn (Role $record) => $record->label)
                ->preload()
                ->hiddenLabel()
                ->required(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('profile_photo_url')
                    ->label(trans('models.users.columns.profile_photo_url'))
                    ->circular(),
                Tables\Columns\TextColumn::make('name')
                    ->label(trans('models.users.attributes.name'))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label(trans('models.users.attributes.email'))
                    ->sortable()
                    ->searchable()
                    ->copyable(),
                Tables\Columns\IconColumn::make('has_two_factor_authentication')
                    ->label(trans('models.users.columns.has_two_factor_authentication'))
                    ->getStateUsing(fn (User $record) => $record->hasEnabledTwoFactorAuthentication())
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->defaultSort('name')
            ->actions([
                // Impersonate::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->filters([
                // Filter by roles
                Tables\Filters\SelectFilter::make('roles')
                    ->label(ucfirst(trans_choice('models.roles.model', 0)))
                    ->relationship('roles', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Role $record) => $record->label)
                    ->preload()
                    ->multiple()
                    ->searchable(),

                // Tables\Filters\TrashedFilter::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                // SoftDeletingScope::class,
            ]);
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }

    public static function canForceDeleteAny(): bool
    {
        return false;
    }
}
