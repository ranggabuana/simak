<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;
use ZipArchive;

class ExcelExporter
{
    /**
     * Konversi indeks kolom (1-based) menjadi huruf kolom Excel (e.g. 1 -> A, 27 -> AA)
     */
    public static function getColumnLetter(int $colIndex): string
    {
        $letter = '';
        while ($colIndex > 0) {
            $mod = ($colIndex - 1) % 26;
            $letter = chr(65 + $mod).$letter;
            $colIndex = (int) (($colIndex - $mod) / 26);
        }

        return $letter;
    }

    /**
     * Periksa apakah nama header kolom merupakan kolom kode / identitas unik
     * (NIP, NUPTK, NPSN, NISN, NIK, Kode Wilayah, Telepon, dsb.)
     * yang WAJIB diperlakukan secara ketat sebagai TEKS agar tidak diubah Excel menjadi notasi ilmiah (e.g. 1,99E+17).
     */
    public static function isTextIdentifierHeader(string $headerName): bool
    {
        $headerUpper = strtoupper(trim($headerName));
        $textKeywords = [
            'NIP',
            'NUPTK',
            'NPSN',
            'NISN',
            'NIK',
            'KODE',
            'TELEPON',
            'TELP',
            'HP',
            'NO HP',
            'NOMOR HP',
            'TAHUN AJARAN',
            'NO WA',
            'REKENING',
            'NO_PESERTA',
            'NO PESERTA',
            'KODE WILAYAH',
            'KODE BPS',
            'KODE KECAMATAN',
        ];

        foreach ($textKeywords as $keyword) {
            if ($headerUpper === $keyword || str_contains($headerUpper, $keyword)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tentukan apakah nilai sel harus dirender sebagai angka kalkulasi (<v>)
     * atau sebagai teks (inlineStr).
     */
    public static function shouldRenderAsNumber(mixed $val, bool $isTextColumn): bool
    {
        // 1. Jika kolom adalah identifier (NIP, NUPTK, NPSN, NISN, Kode, Telepon), WAJIB teks!
        if ($isTextColumn) {
            return false;
        }

        if ($val === null || $val === '') {
            return false;
        }

        // 2. Jika bukan int, float, atau numeric string, render sebagai teks
        if (! is_int($val) && ! is_float($val) && ! is_numeric($val)) {
            return false;
        }

        $strVal = trim((string) $val);

        // 3. String berawalan angka 0 lebih dari 1 digit (seperti '00123' atau '081234') adalah ID/telepon -> teks!
        if (str_starts_with($strVal, '0') && strlen($strVal) > 1 && ! str_starts_with($strVal, '0.')) {
            return false;
        }

        // 4. KRITIKAL: Angka >= 12 digit otomatis diubah Excel menjadi notasi ilmiah (1.99E+17) & rounding error!
        // Oleh karena itu angka >= 12 digit WAJIB dijadikan teks murni.
        if (strlen($strVal) >= 12) {
            return false;
        }

        // 5. Jika mengandung karakter desimal selain titik atau mengandung persen
        if (str_contains($strVal, '%') || str_contains($strVal, ',') || str_contains($strVal, ' ')) {
            return false;
        }

        return true;
    }

    /**
     * Generate berkas resmi Microsoft Excel (.xlsx) dengan styling profesional
     *
     * @param  string  $filename  Nama file tanpa ekstensi atau dengan .xlsx
     * @param  string  $sheetTitle  Nama tab sheet
     * @param  array<int, string>  $headers  Header kolom
     * @param  array<int, array<int, mixed>>  $rows  Baris-baris data
     * @param  string|null  $reportTitle  Judul besar banner laporan di atas tabel
     * @param  string|null  $reportSubtitle  Keterangan sumber data & tanggal unduh
     * @param  array<int, string>  $alignments  Array alignment per kolom: 'left', 'center', 'right'
     */
    public function downloadXlsx(
        string $filename,
        string $sheetTitle,
        array $headers,
        array $rows,
        ?string $reportTitle = null,
        ?string $reportSubtitle = null,
        array $alignments = []
    ): StreamedResponse {
        if (! str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($sheetTitle, $headers, $rows, $reportTitle, $reportSubtitle, $alignments) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'simak_xlsx_');
            $zip = new ZipArchive;

            if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return;
            }

            // 1. [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', $this->getContentTypesXml());

            // 2. _rels/.rels
            $zip->addFromString('_rels/.rels', $this->getRootRelsXml());

            // 3. xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getWorkbookRelsXml());

            // 4. xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', $this->getWorkbookXml($sheetTitle));

            // 5. xl/styles.xml
            $zip->addFromString('xl/styles.xml', $this->getStylesXml());

            // 6. xl/worksheets/sheet1.xml
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->getWorksheetXml($headers, $rows, $reportTitle, $reportSubtitle, $alignments));

            $zip->close();

            $handle = fopen($tmpFile, 'rb');
            if ($handle) {
                while (! feof($handle)) {
                    echo fread($handle, 1024 * 64);
                }
                fclose($handle);
            }

            @unlink($tmpFile);
        }, 200, $headersResponse);
    }

    protected function getContentTypesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
    }

    protected function getRootRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
    }

    protected function getWorkbookRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
    }

    protected function getWorkbookXml(string $sheetTitle): string
    {
        $safeSheetTitle = htmlspecialchars(mb_substr($sheetTitle, 0, 31), ENT_XML1, 'UTF-8');

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="14400"/></bookViews>'
            .'<sheets>'
            .'<sheet name="'.$safeSheetTitle.'" sheetId="1" r:id="rId1"/>'
            .'</sheets>'
            .'</workbook>';
    }

    protected function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="4">'
            .'<font><sz val="10"/><color theme="1"/><name val="Segoe UI"/><family val="2"/></font>' // 0: Normal
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Segoe UI"/><family val="2"/></font>' // 1: Header (White Bold)
            .'<font><b/><sz val="14"/><color rgb="FF1E3A8A"/><name val="Segoe UI"/><family val="2"/></font>' // 2: Title (Blue Bold)
            .'<font><i/><sz val="9"/><color rgb="FF64748B"/><name val="Segoe UI"/><family val="2"/></font>' // 3: Subtitle (Slate Italic)
            .'</fonts>'
            .'<fills count="4">'
            .'<fill><patternFill patternType="none"/></fill>' // 0: Default
            .'<fill><patternFill patternType="gray125"/></fill>' // 1: Default
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1E40AF"/></patternFill></fill>' // 2: Header Blue
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' // 3: Zebra Gray
            .'</fills>'
            .'<borders count="2">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>' // 0: None
            .'<border>' // 1: Thin border
            .'<left style="thin"><color rgb="FFCBD5E1"/></left>'
            .'<right style="thin"><color rgb="FFCBD5E1"/></right>'
            .'<top style="thin"><color rgb="FFCBD5E1"/></top>'
            .'<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'</borders>'
            .'<cellStyleXfs count="1">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            .'</cellStyleXfs>'
            .'<cellXfs count="10">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' // 0: Default
            .'<xf numFmtId="0" fontId="2" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>' // 1: Title
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1"><alignment vertical="center"/></xf>' // 2: Subtitle
            .'<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 3: Header
            // 4: Text Left Normal (numFmtId 49 = Text format @)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 5: Text Center Normal (numFmtId 49 = Text format @)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 6: Number Right Normal
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 7: Text Left Zebra (numFmtId 49 = Text format @)
            .'<xf numFmtId="49" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 8: Text Center Zebra (numFmtId 49 = Text format @)
            .'<xf numFmtId="49" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 9: Number Right Zebra
            .'<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyNumberFormat="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            .'</cellXfs>'
            .'</styleSheet>';
    }

    /**
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @param  array<int, string>  $alignments
     */
    protected function getWorksheetXml(
        array $headers,
        array $rows,
        ?string $reportTitle,
        ?string $reportSubtitle,
        array $alignments
    ): string {
        $colCount = max(count($headers), 1);
        $colWidths = array_fill(1, $colCount, 12);
        $textColumns = [];

        // Deteksi kolom yang merupakan identifier / kode berbasis nama header
        foreach ($headers as $idx => $h) {
            $colNum = $idx + 1;
            $isTextCol = self::isTextIdentifierHeader((string) $h);
            $textColumns[$idx] = $isTextCol;

            $len = mb_strlen((string) $h);
            if ($len + 5 > $colWidths[$colNum]) {
                $colWidths[$colNum] = min(max($len + 5, 14), 45);
            }

            // Jika kolom NIP/NUPTK, pastikan lebar kolom cukup lega (min 22)
            if ($isTextCol && in_array(strtoupper(trim((string) $h)), ['NIP', 'NUPTK', 'NIK'])) {
                $colWidths[$colNum] = max($colWidths[$colNum], 24);
            }
        }

        // Cek sample baris pertama untuk penyesuaian lebar
        $sampleRows = array_slice($rows, 0, 50);
        foreach ($sampleRows as $row) {
            foreach ($row as $idx => $val) {
                $colNum = $idx + 1;
                if ($colNum <= $colCount) {
                    $len = mb_strlen((string) $val);
                    if ($len + 3 > $colWidths[$colNum]) {
                        $colWidths[$colNum] = min(max($len + 3, 12), 45);
                    }
                }
            }
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        // Freeze pane di bawah header tabel
        $headerRowNumber = ($reportTitle ? 2 : 0) + ($reportSubtitle ? 1 : 0) + 1;
        $xml .= '<sheetViews>';
        $xml .= '<sheetView tabSelected="1" workbookViewId="0">';
        $xml .= '<pane ySplit="'.$headerRowNumber.'" topLeftCell="A'.($headerRowNumber + 1).'" activePane="bottomLeft" state="frozen"/>';
        $xml .= '</sheetView>';
        $xml .= '</sheetViews>';

        // Definisi lebar kolom
        $xml .= '<cols>';
        foreach ($colWidths as $colNum => $width) {
            $xml .= '<col min="'.$colNum.'" max="'.$colNum.'" width="'.$width.'" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';
        $currentRow = 1;

        // Title row jika ada
        if ($reportTitle) {
            $xml .= '<row r="'.$currentRow.'" ht="26" customHeight="1">';
            $cellRef = 'A'.$currentRow;
            $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="1"><is><t>'.htmlspecialchars($reportTitle, ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '</row>';
            $currentRow++;
        }

        // Subtitle row jika ada
        if ($reportSubtitle) {
            $xml .= '<row r="'.$currentRow.'" ht="18" customHeight="1">';
            $cellRef = 'A'.$currentRow;
            $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="2"><is><t>'.htmlspecialchars($reportSubtitle, ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '</row>';
            $currentRow++;
        }

        // Baris Header Tabel
        $xml .= '<row r="'.$currentRow.'" ht="24" customHeight="1">';
        foreach ($headers as $colIdx => $headerText) {
            $colLetter = self::getColumnLetter($colIdx + 1);
            $cellRef = $colLetter.$currentRow;
            $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="3"><is><t>'.htmlspecialchars((string) $headerText, ENT_XML1, 'UTF-8').'</t></is></c>';
        }
        $xml .= '</row>';
        $currentRow++;

        // Baris-baris Data
        foreach ($rows as $rowIdx => $row) {
            $isZebra = ($rowIdx % 2 === 1);
            $xml .= '<row r="'.$currentRow.'" ht="20" customHeight="1">';

            foreach ($row as $colIdx => $val) {
                $colLetter = self::getColumnLetter($colIdx + 1);
                $cellRef = $colLetter.$currentRow;
                $isTextCol = $textColumns[$colIdx] ?? false;
                $isNumeric = self::shouldRenderAsNumber($val, $isTextCol);

                // Tentukan alignment
                $align = $alignments[$colIdx] ?? null;
                if (! $align) {
                    if ($isNumeric) {
                        $align = 'right';
                    } elseif ($isTextCol) {
                        // Identifier kolom seperti NIP, NUPTK, NPSN, NISN, Kode Wilayah lebih rapi di tengah
                        $align = 'center';
                    } elseif (in_array(strtolower((string) $val), ['pns', 'pppk', 'ya', 'tidak', 'sudah', 'belum', 'l', 'p', 'a', 'b', 'c', 'sd', 'smp', 'paud', 'sma'])) {
                        $align = 'center';
                    } else {
                        $align = 'left';
                    }
                }

                // Pilih style index:
                // 4: Text Left normal, 5: Text Center normal, 6: Number Right normal
                // 7: Text Left zebra, 8: Text Center zebra, 9: Number Right zebra
                if ($align === 'center') {
                    $styleId = $isZebra ? 8 : 5;
                } elseif ($align === 'right') {
                    $styleId = $isZebra ? 9 : 6;
                } else {
                    $styleId = $isZebra ? 7 : 4;
                }

                if ($val === null || $val === '') {
                    $xml .= '<c r="'.$cellRef.'" s="'.$styleId.'"/>';
                } elseif ($isNumeric) {
                    $xml .= '<c r="'.$cellRef.'" s="'.$styleId.'"><v>'.$val.'</v></c>';
                } else {
                    // String identifier (NIP, NUPTK, NPSN, NISN, dsb.) selalu dirender sebagai inlineStr murni
                    $xml .= '<c r="'.$cellRef.'" t="inlineStr" s="'.$styleId.'"><is><t>'.htmlspecialchars((string) $val, ENT_XML1, 'UTF-8').'</t></is></c>';
                }
            }

            $xml .= '</row>';
            $currentRow++;
        }

        $xml .= '</sheetData>';
        $xml .= '</worksheet>';

        return $xml;
    }
}
