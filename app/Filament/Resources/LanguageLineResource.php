<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LanguageLineResource\Pages;
use App\Models\LanguageLine;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\HtmlString;
use Mcamara\LaravelLocalization\Facades\LaravelLocalization;

class LanguageLineResource extends Resource
{
    protected static ?string $model = LanguageLine::class;

    protected static ?string $slug = 'translations';

    protected static ?string $navigationIcon = 'heroicon-o-language';

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'update',
            'delete',
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return trans('admin.navigation_groups.settings');
    }

    public static function getModelLabel(): string
    {
        return trans_choice('admin.language_lines.model', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('admin.language_lines.model', 0);
    }

    public static function form(Form $form): Form
    {
        $locales = LaravelLocalization::getSupportedLocales();

        return $form
            ->schema([
                Forms\Components\TextInput::make('group')
                    ->label(trans('admin.language_lines.attributes.group'))
                    ->hidden(fn () => LanguageLine::select('group')->groupBy('group')->count() <= 1)
                    ->disabled(),
                Forms\Components\TextInput::make('key')
                    ->label(trans('admin.language_lines.attributes.key'))
                    ->dehydrated(false)
                    ->disabled(),
                Forms\Components\Fieldset::make(trans('admin.language_lines.attributes.group'))
                    ->columns(1)
                    ->schema(fn () => collect($locales)->map(fn ($locale, $key) => Forms\Components\TextInput::make("text.$key")
                        ->helperText(function ($record) use ($key) {
                            $originalText = $record->getOriginalValue($key);
                            $label = trans('admin.language_lines.labels.original_translation');

                            if (blank($originalText)) {
                                return null;
                            }

                            return new HtmlString("<strong>$label</strong>: $originalText");
                        })
                        ->label($locale['name']))->toArray()
                    ),
            ]);
    }

    public static function table(Table $table): Table
    {
        $defaultLocale = App::getLocale();

        // Check which locales have been filled
        $localeColumns = Arr::map(LaravelLocalization::getSupportedLanguagesKeys(), static function ($key) use ($defaultLocale) {
            return Tables\Columns\IconColumn::make("{$key}_exists")
                ->label(strtoupper($key))
                ->getStateUsing(fn (LanguageLine $record) => array_key_exists($key, $record->text))
                ->sortable(query: fn (Builder $query, string $direction) => $query
                    ->select(['language_lines.*'])
                    ->selectRaw("JSON_EXTRACT(text, '$.$key') as result")
                    ->orderBy('result', $direction)
                )
                ->toggleable(isToggledHiddenByDefault: $key !== $defaultLocale)
                ->boolean();
        });

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('group')
                    ->label(trans('admin.language_lines.attributes.group'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('key')
                    ->label(trans('admin.language_lines.attributes.key'))
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('text')
                    ->getStateUsing(fn ($record) => data_get($record->text, app()->getLocale(), $record->text))
                    ->label(trans('admin.language_lines.attributes.text'))
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $search = strtolower($search);

                        return $query->whereRaw('LOWER(text) LIKE ?', ["%$search%"]);
                    })
                    ->sortable()
                    ->size('sm')
                    ->limit()
                    ->wrap(),
                ...$localeColumns,
                Tables\Columns\TextColumn::make('updated_at'),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make()->modalDescription(trans('admin.language_lines.helpers.delete_translation')),
                ]),
            ])
            ->paginated([10, 25, 50])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageLanguageLines::route('/'),
        ];
    }
}
