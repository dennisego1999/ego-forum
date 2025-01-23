<?php

namespace App\Filament\Concerns;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;

trait FilamentPanelDefaults
{
    protected function registerFilamentDefaults(): void
    {
        Table::configureUsing(static function (Table $table): void {
            $table->striped();
        });

        TextInput::configureUsing(static function (TextInput $input): void {
            // Check state
            $inputName = $input->getName();

            // Setup generic column translations
            if (in_array($input, trans('admin.global.attributes'), true)) {
                $input->label(trans("admin.global.attributes.$inputName"));
            }
        });

        Textarea::configureUsing(static function (Textarea $input): void {
            // Check state
            $inputName = $input->getName();

            // Setup generic column translations
            if (in_array($input, trans('admin.global.attributes'), true)) {
                $input->label(trans("admin.global.attributes.$inputName"));
            }
        });

        Toggle::configureUsing(static function (Toggle $toggle): void {
            $toggle
                ->label(fn ($state) => $state
                    ? trans('admin.global.labels.enabled')
                    : trans('admin.global.labels.disabled')
                )
                ->reactive();
        });

        TextColumn::configureUsing(static function (TextColumn $column): void {
            // Check state
            $columnName = $column->getName();

            // Setup placeholders
            $column->placeholder(new HtmlString('&mdash;'));

            // Setup generic column translations
            if (in_array($columnName, trans('admin.global.attributes'), true)) {
                $column->label(trans("admin.global.attributes.$columnName"));
            }

            // Setup timestamp columns
            if (in_array($columnName, ['created_at', 'updated_at'], true)) {
                $column->dateTime()
                    ->since()
                    ->sortable()
                    ->alignRight()
                    ->size('sm')
                    ->toggleable(isToggledHiddenByDefault: true);
            }

            // Show visual feedback for relationship counting columns
            if (Str::endsWith($columnName, '_count')) {
                $column->color(fn ($state) => $state > 0 ? 'success' : 'danger');
            }
        });
    }

    protected function registerScripts(): void
    {
        if (file_exists(public_path('build/manifest.json'))) {
            FilamentAsset::register([
                Js::make('admin', Vite::asset('resources/js/admin.js')),
            ]);

            FilamentAsset::registerScriptData([
                'appEnv' => config('app.env'),
            ]);
        }
    }
}
