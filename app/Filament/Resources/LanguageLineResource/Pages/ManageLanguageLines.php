<?php

namespace App\Filament\Resources\LanguageLineResource\Pages;

use App\Filament\Resources\LanguageLineResource;
use App\Models\LanguageLine;
use ArtcoreSociety\TranslationImport\Commands\ImportTranslationsCommand;
use ArtcoreSociety\TranslationImport\Excel\LanguageLineExport;
use ArtcoreSociety\TranslationImport\Excel\LanguageLineImport;
use Closure;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;

class ManageLanguageLines extends ManageRecords
{
    protected static string $resource = LanguageLineResource::class;

    public function getTabs(): array
    {
        $tabs = [
            null => Tab::make(trans('filament::components/pagination.fields.records_per_page.options.all')),
        ];

        LanguageLine::query()
            ->distinct('group')
            ->pluck('group')
            ->each(function ($group) use (&$tabs) {
                $tabs[$group] = Tab::make($group)
                    ->label(Str::headline($group))
                    ->query(fn ($query) => $query->where('group', $group));
            });

        if (count($tabs) <= 2) {
            return [];
        }

        return $tabs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('Import')
                ->label(trans('admin.global.buttons.import'))
                ->icon('heroicon-s-arrow-up-tray')
                ->hidden(fn () => ! Auth::user()?->isSuperAdmin())
                ->action(function ($data) {
                    Excel::import(new LanguageLineImport, $data['excel']);

                    Notification::make()
                        ->title(trans('admin.language_lines.alerts.translations_imported'))
                        ->success()
                        ->send();
                })
                ->form([
                    Forms\Components\FileUpload::make('excel')
                        ->label(trans('admin.language_lines.attributes.excel'))
                        ->disk('local')
                        ->directory('filament-import')
                        ->hint(trans('admin.language_lines.helpers.export_first'))
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->required()
                        ->rules([
                            function (Forms\Set $set) {
                                return static function (string $attribute, $value, Closure $fail) use ($set) {
                                    try {
                                        Excel::import(new LanguageLineImport(validateOnly: true), $value);
                                    } catch (ValidationException $e) {
                                        $errors = [];

                                        foreach ($e->failures() as $failure) {
                                            foreach ($failure->errors() as $error) {
                                                $errors[] = trans('admin.global.labels.row').' '.$failure->row().': '.$error;
                                            }
                                        }
                                        $set('errors', implode(PHP_EOL, $errors));
                                        $fail(trans('admin.global.alerts.one_or_more_rows_has_issues'));
                                    }
                                };
                            },
                        ]),
                    Forms\Components\Textarea::make('errors')
                        ->hidden(fn ($state) => blank($state))
                        ->dehydrated(false)
                        ->disabled(),
                ]),

            Actions\Action::make('export')
                ->label(trans('admin.global.buttons.export'))
                ->icon('heroicon-s-arrow-down-tray')
                ->action(fn () => Excel::download(new LanguageLineExport, 'language-lines.xlsx')),

            Actions\Action::make('scan')
                ->label(trans('admin.language_lines.buttons.scan'))
                ->color('gray')
                ->action(function () {
                    Artisan::call(ImportTranslationsCommand::class);

                    Notification::make()
                        ->title(trans('admin.language_lines.alerts.translations_scanned'))
                        ->success()
                        ->send();
                }),
        ];
    }
}
