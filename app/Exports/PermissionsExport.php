<?php

namespace App\Exports;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PermissionsExport implements FromCollection, ShouldAutoSize, WithEvents, WithHeadings, WithMapping, WithStrictNullComparison, WithStyles, WithTitle
{
    private Collection $roles;

    private Collection $permissions;

    public function __construct()
    {
        $this->roles = Role::query()
            ->select('id', 'name')
            ->with(['permissions'])
            ->get();

        $this->permissions = Permission::query()
            ->select(['id', 'name'])
            ->get();
    }

    public function title(): string
    {
        return trans('admin.roles.titles.permissions');
    }

    public function collection(): Collection
    {
        return $this->permissions;
    }

    /**
     * Style the first row as bold text.
     */
    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        return $this->roles
            ->pluck('label')
            ->toArray();
    }

    /**
     * @param  Permission  $row
     */
    public function map($row): array
    {
        return $this->roles
            ->map(function (Role $role) use ($row) {
                $data = ['role' => $role->label, 'permission' => $row->getLabel()];

                return $role->hasDirectPermission($row->name)
                    ? trans('admin.roles.labels.role_can', $data)
                    : trans('admin.roles.labels.role_cannot', $data);
            })
            ->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $row = 2;
                $column = 'A';

                // Map roles with their direct permissions
                foreach ($this->permissions as $permission) {
                    foreach ($this->roles as $role) {
                        $hasPermission = $role->hasDirectPermission($permission->name);
                        $hex = $hasPermission ? 'bbf7d0' : 'fecaca';

                        // Fill in the background of the cell
                        $event->sheet->getDelegate()
                            ->getStyle($column.$row)
                            ->applyFromArray(self::getFillStyle($hex));
                        $column++; // Navigate to next column
                    }

                    // Navigate to the next row
                    $column = 'A';
                    $row++;
                }
            },
        ];
    }

    private static function getFillStyle(string $hex): array
    {
        return [
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'color' => ['argb' => $hex],
            ],
        ];
    }
}
