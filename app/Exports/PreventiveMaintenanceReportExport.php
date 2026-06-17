<?php

namespace App\Exports;

use App\Models\DeviceAssignment;
use App\Models\Office;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class PreventiveMaintenanceReportExport implements FromView, WithEvents
{
    public function __construct(
        protected ?Office $office = null
    ) {}

    public function view(): View
    {
        $assignments = DeviceAssignment::query()
            ->with([
                'device.type',
                'staff.office.college',
            ])
            ->whereNull('returned_at')
            ->whereHas('device.type', function ($query) {
                $query->whereIn('name', [
                    'Desktop',
                    'Laptop',
                    'Monitor',
                    'Printer',
                    'UPS',
                    'AVR',
                    'Other',
                ]);
            })
            ->when($this->office, function ($query) {
                $query->whereHas('staff', function ($staffQuery) {
                    $staffQuery->where('office_id', $this->office->id);
                });
            })
            ->orderBy('staff_id')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Group devices by office and staff
        |--------------------------------------------------------------------------
        | This keeps devices owned by the same staff in the same row:
        | Desktop / Monitor / Printer / UPS / AVR
        */
        $groupedByOffice = $assignments
            ->groupBy(function ($assignment) {
                return $assignment->staff?->office?->name ?? 'No Office';
            })
            ->map(function ($officeAssignments) {
                return $officeAssignments
                    ->groupBy('staff_id')
                    ->map(function ($staffAssignments) {
                        $staff = $staffAssignments->first()?->staff;

                        $findType = function (string $typeName) use ($staffAssignments) {
                            return $staffAssignments->first(function ($assignment) use ($typeName) {
                                return strtolower($assignment->device?->type?->name ?? '') === strtolower($typeName);
                            });
                        };

                        return [
                            'staff' => $staff,
                            'desktop' => $findType('Desktop') ?: $findType('Laptop'),
                            'monitor' => $findType('Monitor'),
                            'printer' => $findType('Printer'),
                            'ups' => $findType('UPS'),
                            'avr' => $findType('AVR'),
                        ];
                    })
                    ->values();
            });

        return view('admin.reports.preventive-maintenance-excel', [
            'groupedByOffice' => $groupedByOffice,
            'reportDate' => now(),
            'office' => $this->office,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();

                /*
                |--------------------------------------------------------------------------
                | Page Setup
                |--------------------------------------------------------------------------
                | Do not force all columns into 1 page width because it makes the
                | Excel file unreadable. Legal landscape gives more room.
                */
                $sheet->getPageSetup()
                    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
                    ->setPaperSize(PageSetup::PAPERSIZE_LEGAL)
                    ->setFitToPage(false)
                    ->setScale(75);

                $sheet->getPageMargins()->setTop(0.25);
                $sheet->getPageMargins()->setRight(0.20);
                $sheet->getPageMargins()->setLeft(0.20);
                $sheet->getPageMargins()->setBottom(0.25);

                /*
                |--------------------------------------------------------------------------
                | Freeze header
                |--------------------------------------------------------------------------
                */
                $sheet->freezePane('A6');

                /*
                |--------------------------------------------------------------------------
                | General Styling
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A1:AR{$highestRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setWrapText(true);

                $sheet->getStyle("A1:AR{$highestRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getStyle('A1:AR5')
                    ->getFont()
                    ->setBold(true);

                /*
                |--------------------------------------------------------------------------
                | Row Heights
                |--------------------------------------------------------------------------
                */
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getRowDimension(3)->setRowHeight(12);
                $sheet->getRowDimension(4)->setRowHeight(28);
                $sheet->getRowDimension(5)->setRowHeight(46);

                for ($row = 6; $row <= $highestRow; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(42);
                }

                /*
                |--------------------------------------------------------------------------
                | Column Widths
                |--------------------------------------------------------------------------
                | Last column is AR.
                | Wider columns make the exported Excel readable.
                */
                $widths = [
                    'A' => 14,  // Date
                    'B' => 8,   // No.
                    'C' => 22,  // Office

                    // Desktop: D to O
                    'D' => 20,  // Name / Model
                    'E' => 14,  // Brand
                    'F' => 24,  // Issued To
                    'G' => 14,  // Acquired
                    'H' => 24,  // Property #
                    'I' => 22,  // Serial #
                    'J' => 14,  // Unit Price
                    'K' => 22,  // MAC
                    'L' => 18,  // OS
                    'M' => 18,  // Storage
                    'N' => 18,  // Form Factor
                    'O' => 16,  // Condition

                    // Monitor: P to V
                    'P' => 22,  // Brand / Model
                    'Q' => 24,  // Issued To
                    'R' => 14,  // Acquired
                    'S' => 24,  // Property #
                    'T' => 22,  // Serial #
                    'U' => 14,  // Unit Price
                    'V' => 16,  // Condition

                    // Printer: W to AC
                    'W' => 22,  // Brand / Model
                    'X' => 24,  // Issued To
                    'Y' => 14,  // Acquired
                    'Z' => 24,  // Property #
                    'AA' => 22, // Serial #
                    'AB' => 14, // Unit Price
                    'AC' => 16, // Condition

                    // UPS: AD to AJ
                    'AD' => 22, // Brand / Model
                    'AE' => 24, // Issued To
                    'AF' => 14, // Acquired
                    'AG' => 24, // Property #
                    'AH' => 22, // Serial #
                    'AI' => 14, // Unit Price
                    'AJ' => 16, // Condition

                    // AVR: AK to AQ
                    'AK' => 22, // Brand / Model
                    'AL' => 24, // Issued To
                    'AM' => 14, // Acquired
                    'AN' => 24, // Property #
                    'AO' => 22, // Serial #
                    'AP' => 14, // Unit Price
                    'AQ' => 16, // Condition

                    // Remarks
                    'AR' => 36,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)->setAutoSize(false);
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                /*
                |--------------------------------------------------------------------------
                | Keep important number-like values as text
                |--------------------------------------------------------------------------
                | Property numbers, serial numbers, and MAC addresses should not be
                | converted by Excel.
                */
                $textColumns = [
                    'H', 'I', 'K',
                    'S', 'T',
                    'Z', 'AA',
                    'AG', 'AH',
                    'AN', 'AO',
                ];

                foreach ($textColumns as $column) {
                    for ($row = 6; $row <= $highestRow; $row++) {
                        $cell = $column . $row;
                        $value = $sheet->getCell($cell)->getValue();

                        if ($value !== null && $value !== '') {
                            $sheet->setCellValueExplicit($cell, (string) $value, DataType::TYPE_STRING);
                        }
                    }
                }

                /*
                |--------------------------------------------------------------------------
                | Better visual formatting
                |--------------------------------------------------------------------------
                */
                $sheet->getStyle("A4:AR5")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setShrinkToFit(false);

                $sheet->getStyle("A6:AR{$highestRow}")
                    ->getAlignment()
                    ->setWrapText(true)
                    ->setShrinkToFit(false);

                /*
                |--------------------------------------------------------------------------
                | Hide extra columns after AR if Excel shows blank space
                |--------------------------------------------------------------------------
                */
                foreach (range('AS', 'AZ') as $column) {
                    $sheet->getColumnDimension($column)->setVisible(false);
                }
            },
        ];
    }
}