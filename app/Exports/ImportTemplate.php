<?php

namespace App\Exports;

use App\Imports\ImportColumn;
use App\Imports\SpreadsheetImport;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Common\Entity\Style\Border;
use OpenSpout\Common\Entity\Style\BorderName;
use OpenSpout\Common\Entity\Style\BorderPart;
use OpenSpout\Common\Entity\Style\CellVerticalAlignment;
use OpenSpout\Common\Entity\Style\Style;
use OpenSpout\Writer\XLSX\Entity\SheetView;
use OpenSpout\Writer\XLSX\Writer;

/**
 * Membuat template .xlsx dari definisi kolom sebuah impor, sehingga urutan
 * kolom di template selalu sama dengan yang dibaca oleh importer.
 */
class ImportTemplate
{
    /** Baris kosong yang diformat sebagai teks agar NIS/NUPTK tidak berubah jadi angka. */
    private const TEXT_ROWS = 500;

    public function build(SpreadsheetImport $import): string
    {
        $path = tempnam(sys_get_temp_dir(), 'template').'.xlsx';
        $columns = $import->columns();
        $width = count($columns);

        $writer = new Writer();
        $writer->openToFile($path);

        $sheet = $writer->getCurrentSheet();
        $sheet->setName('Data '.$import->subject());
        $sheet->setSheetView(new SheetView(freezeRow: 2));
        foreach ($columns as $i => $column) {
            $sheet->setColumnWidth(max(16, mb_strlen($column->example) + 6), $i + 1);
        }

        $header = (new Style())
            ->withFontBold(true)
            ->withFontColor('FFFFFF')
            ->withBackgroundColor('1B2A4E')
            ->withCellVerticalAlignment(CellVerticalAlignment::CENTER)
            ->withBorder(new Border(new BorderPart(BorderName::BOTTOM, '1B2A4E')));
        $text = (new Style())->withFormat('@');

        $writer->addRow(Row::fromValuesWithStyle(
            array_map(fn (ImportColumn $c) => $c->label, $columns), $header, 22,
        ));

        foreach ($import->exampleRows() as $values) {
            $writer->addRow(Row::fromValuesWithStyle($values, $text));
        }

        $blank = array_fill(0, $width, '');
        for ($i = 0; $i < self::TEXT_ROWS; $i++) {
            $writer->addRow(Row::fromValuesWithStyle($blank, $text));
        }

        $this->addGuideSheet($writer, $import);

        $writer->close();

        return $path;
    }

    private function addGuideSheet(Writer $writer, SpreadsheetImport $import): void
    {
        $guide = $writer->addNewSheetAndMakeItCurrent();
        $guide->setName('Petunjuk');
        $guide->setColumnWidth(18, 1);
        $guide->setColumnWidth(70, 2);

        $bold = (new Style())->withFontBold(true);

        $writer->addRow(Row::fromValuesWithStyle(["Cara mengisi template {$import->subject()}"], $bold));
        $writer->addRow(Row::fromValues(['', 'Isi data mulai baris ke-2 di sheet pertama. Jangan ubah urutan kolom.']));
        $writer->addRow(Row::fromValues(['', 'Hapus baris contoh sebelum mengunggah, atau timpa dengan data asli.']));
        $writer->addRow(Row::fromValues(['', 'Data yang sudah ada akan diperbarui, bukan diduplikasi.']));
        $writer->addRow(Row::fromValues(['', 'Kata sandi awal akun baru: '.SpreadsheetImport::DEFAULT_PASSWORD]));
        $writer->addRow(Row::fromValues([]));
        $writer->addRow(Row::fromValuesWithStyle(['Kolom', 'Keterangan'], $bold));

        foreach ($import->columns() as $column) {
            $writer->addRow(Row::fromValues([$column->label, $column->note]));
        }

        $writer->setCurrentSheet($writer->getSheets()[0]);
    }
}
