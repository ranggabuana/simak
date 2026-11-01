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
            .'<fonts count="6">'
            .'<font><sz val="10"/><color theme="1"/><name val="Segoe UI"/><family val="2"/></font>' // 0: Normal
            .'<font><b/><sz val="10"/><color rgb="FFFFFFFF"/><name val="Segoe UI"/><family val="2"/></font>' // 1: Header (White Bold)
            .'<font><b/><sz val="14"/><color rgb="FF1E3A8A"/><name val="Segoe UI"/><family val="2"/></font>' // 2: Title (Blue Bold)
            .'<font><i/><sz val="9"/><color rgb="FF64748B"/><name val="Segoe UI"/><family val="2"/></font>' // 3: Subtitle (Slate Italic)
            .'<font><b/><sz val="10"/><color rgb="FF0F172A"/><name val="Segoe UI"/><family val="2"/></font>' // 4: Black Bold 10pt
            .'<font><b/><sz val="11"/><color rgb="FF1E40AF"/><name val="Segoe UI"/><family val="2"/></font>' // 5: Blue Bold 11pt
            .'</fonts>'
            .'<fills count="6">'
            .'<fill><patternFill patternType="none"/></fill>' // 0: Default
            .'<fill><patternFill patternType="gray125"/></fill>' // 1: Default
            .'<fill><patternFill patternType="solid"><fgColor rgb="FF1E40AF"/></patternFill></fill>' // 2: Header Blue
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF8FAFC"/></patternFill></fill>' // 3: Zebra Gray
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFEFF6FF"/></patternFill></fill>' // 4: Light Sky/Cyan Header
            .'<fill><patternFill patternType="solid"><fgColor rgb="FFF1F5F9"/></patternFill></fill>' // 5: Light Slate Summary Fill
            .'</fills>'
            .'<borders count="3">'
            .'<border><left/><right/><top/><bottom/><diagonal/></border>' // 0: None
            .'<border>' // 1: Thin border
            .'<left style="thin"><color rgb="FFCBD5E1"/></left>'
            .'<right style="thin"><color rgb="FFCBD5E1"/></right>'
            .'<top style="thin"><color rgb="FFCBD5E1"/></top>'
            .'<bottom style="thin"><color rgb="FFCBD5E1"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'<border>' // 2: Total border (thin top, double bottom)
            .'<left style="thin"><color rgb="FF94A3B8"/></left>'
            .'<right style="thin"><color rgb="FF94A3B8"/></right>'
            .'<top style="thin"><color rgb="FF94A3B8"/></top>'
            .'<bottom style="double"><color rgb="FF94A3B8"/></bottom>'
            .'<diagonal/>'
            .'</border>'
            .'</borders>'
            .'<cellStyleXfs count="1">'
            .'<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            .'</cellStyleXfs>'
            .'<cellXfs count="20">'
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
            // 10: Official Header Group (Font 4 bold, Fill 4 pale blue, Border 1, center/center, wrapText)
            .'<xf numFmtId="0" fontId="4" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>'
            // 11: Official Title Banner (Font 5 bold 11pt, Border 1, center/center)
            .'<xf numFmtId="0" fontId="5" fillId="4" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 12: Official Data Integer (numFmtId 3 = #,##0, Font 0, Border 1, right)
            .'<xf numFmtId="3" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 13: Official Data Decimal/Percent (numFmtId 2 = 0.00, Font 0, Border 1, right)
            .'<xf numFmtId="2" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 14: Official Data Text Left (Font 0, Border 1, left)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 15: Official Data Text Center (Font 0, Border 1, center)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 16: Official Total Row Bold Text (Font 4, Fill 5, Border 2, left)
            .'<xf numFmtId="49" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 17: Official Total Row Bold Integer (numFmtId 3, Font 4, Fill 5, Border 2, right)
            .'<xf numFmtId="3" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 18: Official Total Row Bold Decimal/Percent (numFmtId 2, Font 4, Fill 5, Border 2, right)
            .'<xf numFmtId="2" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 19: Official Footer Note (Font 3 italic, Border 0, left)
            .'<xf numFmtId="0" fontId="3" fillId="0" borderId="0" xfId="0" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
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

    /**
     * Unduh file Excel resmi dengan multi-level header 3 blok (PAUD, SD, SMP)
     * Mengikuti format dan desain persis file APK.xlsx, APM.xlsx, dan APS.xlsx
     *
     * @param  array<string, mixed>  $officialData
     */
    public function downloadIndikatorOfficialXlsx(string $filename, array $officialData): StreamedResponse
    {
        if (! str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $sheetTitle = strtoupper((string) ($officialData['tipe'] ?? 'INDIKATOR')).' '.(string) ($officialData['tahun'] ?? date('Y'));

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($sheetTitle, $officialData) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'simak_official_xlsx_');
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
            $zip->addFromString('xl/worksheets/sheet1.xml', $this->getIndikatorOfficialWorksheetXml($officialData));

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

    /**
     * Bangun XML Worksheet resmi untuk format 3 blok indikator (PAUD, SD, SMP)
     *
     * @param  array<string, mixed>  $officialData
     */
    protected function getIndikatorOfficialWorksheetXml(array $officialData): string
    {
        $labels = $officialData['labels'];
        $rows = $officialData['rows'];
        $total = $officialData['total'];
        $sumber = $officialData['sumber'] ?? 'Sumber data : DISDUKCAPIL, DAPODIK KEMENDIKBUDRISTEK, EMIS - KEMENAG';

        $colWidths = [
            1 => 4.5,   // A: No
            2 => 20.0,  // B: Kecamatan
            3 => 9.5,   // C: Pop L
            4 => 9.5,   // D: Pop P
            5 => 10.5,  // E: Pop Jml
            6 => 9.5,   // F: Siswa L
            7 => 9.5,   // G: Siswa P
            8 => 10.5,  // H: Siswa Jml
            9 => 11.5,  // I: Capaian L
            10 => 11.5, // J: Capaian P
            11 => 11.5, // K: Capaian Jml
            12 => 3.0,  // L: Spacer
            13 => 4.5,  // M: No
            14 => 20.0, // N: Kecamatan
            15 => 9.5,  // O: Pop L
            16 => 9.5,  // P: Pop P
            17 => 10.5, // Q: Pop Jml
            18 => 9.5,  // R: Siswa L
            19 => 9.5,  // S: Siswa P
            20 => 10.5, // T: Siswa Jml
            21 => 11.5, // U: Capaian L
            22 => 11.5, // V: Capaian P
            23 => 11.5, // W: Capaian Jml
            24 => 3.0,  // X: Spacer
            25 => 4.5,  // Y: No
            26 => 20.0, // Z: Kecamatan
            27 => 9.5,  // AA: Pop L
            28 => 9.5,  // AB: Pop P
            29 => 10.5, // AC: Pop Jml
            30 => 9.5,  // AD: Siswa L
            31 => 9.5,  // AE: Siswa P
            32 => 10.5, // AF: Siswa Jml
            33 => 11.5, // AG: Capaian L
            34 => 11.5, // AH: Capaian P
            35 => 11.5, // AI: Capaian Jml
            36 => 3.0,  // AJ: Spacer
            37 => 4.5,  // AK: No
            38 => 20.0, // AL: Kecamatan
            39 => 9.5,  // AM: Pop L
            40 => 9.5,  // AN: Pop P
            41 => 10.5, // AO: Pop Jml
            42 => 9.5,  // AP: Siswa L
            43 => 9.5,  // AQ: Siswa P
            44 => 10.5, // AR: Siswa Jml
            45 => 11.5, // AS: Capaian L
            46 => 11.5, // AT: Capaian P
            47 => 11.5, // AU: Capaian Jml
        ];

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        // Freeze pane di baris ke-5 (di bawah subheader L/P/Jml)
        $xml .= '<sheetViews>';
        $xml .= '<sheetView tabSelected="1" workbookViewId="0">';
        $xml .= '<pane ySplit="5" topLeftCell="A6" activePane="bottomLeft" state="frozen"/>';
        $xml .= '</sheetView>';
        $xml .= '</sheetViews>';

        // Cols definition
        $xml .= '<cols>';
        foreach ($colWidths as $colNum => $width) {
            $xml .= '<col min="'.$colNum.'" max="'.$colNum.'" width="'.$width.'" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';

        // Baris 1: Kosong
        $xml .= '<row r="1" ht="14"/>';

        // Baris 2: Judul Blok Jenjang (A2:K2, M2:W2, Y2:AI2, AK2:AU2)
        $xml .= '<row r="2" ht="26" customHeight="1">';
        // PAUD Title
        $xml .= '<c r="A2" t="inlineStr" s="11"><is><t>'.htmlspecialchars((string) $labels['paud_title'], ENT_XML1, 'UTF-8').'</t></is></c>';
        for ($c = 2; $c <= 11; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'2" s="11"/>';
        }
        // Spacer L2
        $xml .= '<c r="L2"/>';
        // SD Title
        $xml .= '<c r="M2" t="inlineStr" s="11"><is><t>'.htmlspecialchars((string) $labels['sd_title'], ENT_XML1, 'UTF-8').'</t></is></c>';
        for ($c = 14; $c <= 23; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'2" s="11"/>';
        }
        // Spacer X2
        $xml .= '<c r="X2"/>';
        // SMP Title
        $xml .= '<c r="Y2" t="inlineStr" s="11"><is><t>'.htmlspecialchars((string) $labels['smp_title'], ENT_XML1, 'UTF-8').'</t></is></c>';
        for ($c = 26; $c <= 35; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'2" s="11"/>';
        }
        // Spacer AJ2
        $xml .= '<c r="AJ2"/>';
        // SMA Title
        $xml .= '<c r="AK2" t="inlineStr" s="11"><is><t>'.htmlspecialchars((string) ($labels['sma_title'] ?? 'SMA/SMK'), ENT_XML1, 'UTF-8').'</t></is></c>';
        for ($c = 38; $c <= 47; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'2" s="11"/>';
        }
        $xml .= '</row>';

        // Baris 3: Kosong
        $xml .= '<row r="3" ht="12"/>';

        // Baris 4: Top Group Headers
        $xml .= '<row r="4" ht="36" customHeight="1">';
        // PAUD
        $xml .= '<c r="A4" t="inlineStr" s="10"><is><t>No</t></is></c>';
        $xml .= '<c r="B4" t="inlineStr" s="10"><is><t>Kecamatan</t></is></c>';
        $xml .= '<c r="C4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['paud_penduduk'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="D4" s="10"/><c r="E4" s="10"/>';
        $xml .= '<c r="F4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['paud_siswa'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="G4" s="10"/><c r="H4" s="10"/>';
        $xml .= '<c r="I4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['paud_capaian'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="J4" s="10"/><c r="K4" s="10"/>';
        // Spacer L
        $xml .= '<c r="L4"/>';
        // SD
        $xml .= '<c r="M4" t="inlineStr" s="10"><is><t>No</t></is></c>';
        $xml .= '<c r="N4" t="inlineStr" s="10"><is><t>Kecamatan</t></is></c>';
        $xml .= '<c r="O4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['sd_penduduk'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="P4" s="10"/><c r="Q4" s="10"/>';
        $xml .= '<c r="R4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['sd_siswa'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="S4" s="10"/><c r="T4" s="10"/>';
        $xml .= '<c r="U4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['sd_capaian'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="V4" s="10"/><c r="W4" s="10"/>';
        // Spacer X
        $xml .= '<c r="X4"/>';
        // SMP
        $xml .= '<c r="Y4" t="inlineStr" s="10"><is><t>No</t></is></c>';
        $xml .= '<c r="Z4" t="inlineStr" s="10"><is><t>Kecamatan</t></is></c>';
        $xml .= '<c r="AA4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['smp_penduduk'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AB4" s="10"/><c r="AC4" s="10"/>';
        $xml .= '<c r="AD4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['smp_siswa'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AE4" s="10"/><c r="AF4" s="10"/>';
        $xml .= '<c r="AG4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $labels['smp_capaian'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AH4" s="10"/><c r="AI4" s="10"/>';
        // Spacer AJ
        $xml .= '<c r="AJ4"/>';
        // SMA
        $xml .= '<c r="AK4" t="inlineStr" s="10"><is><t>No</t></is></c>';
        $xml .= '<c r="AL4" t="inlineStr" s="10"><is><t>Kecamatan</t></is></c>';
        $xml .= '<c r="AM4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) ($labels['sma_penduduk'] ?? 'Penduduk 16-18'), ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AN4" s="10"/><c r="AO4" s="10"/>';
        $xml .= '<c r="AP4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) ($labels['sma_siswa'] ?? 'Siswa SMA/SMK'), ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AQ4" s="10"/><c r="AR4" s="10"/>';
        $xml .= '<c r="AS4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) ($labels['sma_capaian'] ?? 'Capaian SMA/SMK'), ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="AT4" s="10"/><c r="AU4" s="10"/>';
        $xml .= '</row>';

        // Baris 5: Sub-headers L, P, Jml
        $xml .= '<row r="5" ht="20" customHeight="1">';
        $xml .= '<c r="A5" s="10"/><c r="B5" s="10"/>';
        // PAUD subheaders
        $xml .= '<c r="C5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="D5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="E5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="F5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="G5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="H5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="I5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="J5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="K5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="L5"/>';
        // SD subheaders
        $xml .= '<c r="M5" s="10"/><c r="N5" s="10"/>';
        $xml .= '<c r="O5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="P5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="Q5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="R5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="S5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="T5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="U5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="V5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="W5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="X5"/>';
        // SMP subheaders
        $xml .= '<c r="Y5" s="10"/><c r="Z5" s="10"/>';
        $xml .= '<c r="AA5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AB5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AC5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="AD5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AE5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AF5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="AG5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AH5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AI5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        // SMA subheaders
        $xml .= '<c r="AJ5"/>';
        $xml .= '<c r="AK5" s="10"/><c r="AL5" s="10"/>';
        $xml .= '<c r="AM5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AN5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AO5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="AP5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AQ5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AR5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '<c r="AS5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="AT5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="AU5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '</row>';

        // Baris 6 s.d. 25: Data 20 Kecamatan
        $currentRow = 6;
        foreach ($rows as $r) {
            $xml .= '<row r="'.$currentRow.'" ht="20" customHeight="1">';

            // --- Blok 1: PAUD ---
            $xml .= '<c r="A'.$currentRow.'" s="15"><v>'.$r['no'].'</v></c>';
            $xml .= '<c r="B'.$currentRow.'" t="inlineStr" s="14"><is><t>'.htmlspecialchars((string) $r['nama'], ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '<c r="C'.$currentRow.'" s="12"><v>'.$r['paud']['penduduk']['l'].'</v></c>';
            $xml .= '<c r="D'.$currentRow.'" s="12"><v>'.$r['paud']['penduduk']['p'].'</v></c>';
            $xml .= '<c r="E'.$currentRow.'" s="12"><v>'.$r['paud']['penduduk']['jml'].'</v></c>';
            $xml .= '<c r="F'.$currentRow.'" s="12"><v>'.$r['paud']['siswa']['l'].'</v></c>';
            $xml .= '<c r="G'.$currentRow.'" s="12"><v>'.$r['paud']['siswa']['p'].'</v></c>';
            $xml .= '<c r="H'.$currentRow.'" s="12"><v>'.$r['paud']['siswa']['jml'].'</v></c>';
            $xml .= '<c r="I'.$currentRow.'" s="13"><v>'.$r['paud']['capaian']['l'].'</v></c>';
            $xml .= '<c r="J'.$currentRow.'" s="13"><v>'.$r['paud']['capaian']['p'].'</v></c>';
            $xml .= '<c r="K'.$currentRow.'" s="13"><v>'.$r['paud']['capaian']['jml'].'</v></c>';

            // Spacer L
            $xml .= '<c r="L'.$currentRow.'"/>';

            // --- Blok 2: SD ---
            $xml .= '<c r="M'.$currentRow.'" s="15"><v>'.$r['no'].'</v></c>';
            $xml .= '<c r="N'.$currentRow.'" t="inlineStr" s="14"><is><t>'.htmlspecialchars((string) $r['nama'], ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '<c r="O'.$currentRow.'" s="12"><v>'.$r['sd']['penduduk']['l'].'</v></c>';
            $xml .= '<c r="P'.$currentRow.'" s="12"><v>'.$r['sd']['penduduk']['p'].'</v></c>';
            $xml .= '<c r="Q'.$currentRow.'" s="12"><v>'.$r['sd']['penduduk']['jml'].'</v></c>';
            $xml .= '<c r="R'.$currentRow.'" s="12"><v>'.$r['sd']['siswa']['l'].'</v></c>';
            $xml .= '<c r="S'.$currentRow.'" s="12"><v>'.$r['sd']['siswa']['p'].'</v></c>';
            $xml .= '<c r="T'.$currentRow.'" s="12"><v>'.$r['sd']['siswa']['jml'].'</v></c>';
            $xml .= '<c r="U'.$currentRow.'" s="13"><v>'.$r['sd']['capaian']['l'].'</v></c>';
            $xml .= '<c r="V'.$currentRow.'" s="13"><v>'.$r['sd']['capaian']['p'].'</v></c>';
            $xml .= '<c r="W'.$currentRow.'" s="13"><v>'.$r['sd']['capaian']['jml'].'</v></c>';

            // Spacer X
            $xml .= '<c r="X'.$currentRow.'"/>';

            // --- Blok 3: SMP ---
            $xml .= '<c r="Y'.$currentRow.'" s="15"><v>'.$r['no'].'</v></c>';
            $xml .= '<c r="Z'.$currentRow.'" t="inlineStr" s="14"><is><t>'.htmlspecialchars((string) $r['nama'], ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '<c r="AA'.$currentRow.'" s="12"><v>'.$r['smp']['penduduk']['l'].'</v></c>';
            $xml .= '<c r="AB'.$currentRow.'" s="12"><v>'.$r['smp']['penduduk']['p'].'</v></c>';
            $xml .= '<c r="AC'.$currentRow.'" s="12"><v>'.$r['smp']['penduduk']['jml'].'</v></c>';
            $xml .= '<c r="AD'.$currentRow.'" s="12"><v>'.$r['smp']['siswa']['l'].'</v></c>';
            $xml .= '<c r="AE'.$currentRow.'" s="12"><v>'.$r['smp']['siswa']['p'].'</v></c>';
            $xml .= '<c r="AF'.$currentRow.'" s="12"><v>'.$r['smp']['siswa']['jml'].'</v></c>';
            $xml .= '<c r="AG'.$currentRow.'" s="13"><v>'.$r['smp']['capaian']['l'].'</v></c>';
            $xml .= '<c r="AH'.$currentRow.'" s="13"><v>'.$r['smp']['capaian']['p'].'</v></c>';
            $xml .= '<c r="AI'.$currentRow.'" s="13"><v>'.$r['smp']['capaian']['jml'].'</v></c>';

            // Spacer AJ
            $xml .= '<c r="AJ'.$currentRow.'"/>';

            // --- Blok 4: SMA/SMK ---
            $smaData = $r['sma'] ?? ['penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0], 'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0], 'capaian' => ['l' => 0, 'p' => 0, 'jml' => 0]];
            $xml .= '<c r="AK'.$currentRow.'" s="15"><v>'.$r['no'].'</v></c>';
            $xml .= '<c r="AL'.$currentRow.'" t="inlineStr" s="14"><is><t>'.htmlspecialchars((string) $r['nama'], ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '<c r="AM'.$currentRow.'" s="12"><v>'.$smaData['penduduk']['l'].'</v></c>';
            $xml .= '<c r="AN'.$currentRow.'" s="12"><v>'.$smaData['penduduk']['p'].'</v></c>';
            $xml .= '<c r="AO'.$currentRow.'" s="12"><v>'.$smaData['penduduk']['jml'].'</v></c>';
            $xml .= '<c r="AP'.$currentRow.'" s="12"><v>'.$smaData['siswa']['l'].'</v></c>';
            $xml .= '<c r="AQ'.$currentRow.'" s="12"><v>'.$smaData['siswa']['p'].'</v></c>';
            $xml .= '<c r="AR'.$currentRow.'" s="12"><v>'.$smaData['siswa']['jml'].'</v></c>';
            $xml .= '<c r="AS'.$currentRow.'" s="13"><v>'.$smaData['capaian']['l'].'</v></c>';
            $xml .= '<c r="AT'.$currentRow.'" s="13"><v>'.$smaData['capaian']['p'].'</v></c>';
            $xml .= '<c r="AU'.$currentRow.'" s="13"><v>'.$smaData['capaian']['jml'].'</v></c>';

            $xml .= '</row>';
            $currentRow++;
        }

        // Baris Jumlah (Kabupaten)
        $xml .= '<row r="'.$currentRow.'" ht="22" customHeight="1">';
        // PAUD Total
        $xml .= '<c r="A'.$currentRow.'" s="16"/>';
        $xml .= '<c r="B'.$currentRow.'" t="inlineStr" s="16"><is><t>Jumlah</t></is></c>';
        $xml .= '<c r="C'.$currentRow.'" s="17"><v>'.$total['paud']['penduduk']['l'].'</v></c>';
        $xml .= '<c r="D'.$currentRow.'" s="17"><v>'.$total['paud']['penduduk']['p'].'</v></c>';
        $xml .= '<c r="E'.$currentRow.'" s="17"><v>'.$total['paud']['penduduk']['jml'].'</v></c>';
        $xml .= '<c r="F'.$currentRow.'" s="17"><v>'.$total['paud']['siswa']['l'].'</v></c>';
        $xml .= '<c r="G'.$currentRow.'" s="17"><v>'.$total['paud']['siswa']['p'].'</v></c>';
        $xml .= '<c r="H'.$currentRow.'" s="17"><v>'.$total['paud']['siswa']['jml'].'</v></c>';
        $xml .= '<c r="I'.$currentRow.'" s="18"><v>'.$total['paud']['capaian']['l'].'</v></c>';
        $xml .= '<c r="J'.$currentRow.'" s="18"><v>'.$total['paud']['capaian']['p'].'</v></c>';
        $xml .= '<c r="K'.$currentRow.'" s="18"><v>'.$total['paud']['capaian']['jml'].'</v></c>';

        // Spacer L
        $xml .= '<c r="L'.$currentRow.'"/>';

        // SD Total
        $xml .= '<c r="M'.$currentRow.'" s="16"/>';
        $xml .= '<c r="N'.$currentRow.'" t="inlineStr" s="16"><is><t>Jumlah</t></is></c>';
        $xml .= '<c r="O'.$currentRow.'" s="17"><v>'.$total['sd']['penduduk']['l'].'</v></c>';
        $xml .= '<c r="P'.$currentRow.'" s="17"><v>'.$total['sd']['penduduk']['p'].'</v></c>';
        $xml .= '<c r="Q'.$currentRow.'" s="17"><v>'.$total['sd']['penduduk']['jml'].'</v></c>';
        $xml .= '<c r="R'.$currentRow.'" s="17"><v>'.$total['sd']['siswa']['l'].'</v></c>';
        $xml .= '<c r="S'.$currentRow.'" s="17"><v>'.$total['sd']['siswa']['p'].'</v></c>';
        $xml .= '<c r="T'.$currentRow.'" s="17"><v>'.$total['sd']['siswa']['jml'].'</v></c>';
        $xml .= '<c r="U'.$currentRow.'" s="18"><v>'.$total['sd']['capaian']['l'].'</v></c>';
        $xml .= '<c r="V'.$currentRow.'" s="18"><v>'.$total['sd']['capaian']['p'].'</v></c>';
        $xml .= '<c r="W'.$currentRow.'" s="18"><v>'.$total['sd']['capaian']['jml'].'</v></c>';

        // Spacer X
        $xml .= '<c r="X'.$currentRow.'"/>';

        // SMP Total
        $xml .= '<c r="Y'.$currentRow.'" s="16"/>';
        $xml .= '<c r="Z'.$currentRow.'" t="inlineStr" s="16"><is><t>Jumlah</t></is></c>';
        $xml .= '<c r="AA'.$currentRow.'" s="17"><v>'.$total['smp']['penduduk']['l'].'</v></c>';
        $xml .= '<c r="AB'.$currentRow.'" s="17"><v>'.$total['smp']['penduduk']['p'].'</v></c>';
        $xml .= '<c r="AC'.$currentRow.'" s="17"><v>'.$total['smp']['penduduk']['jml'].'</v></c>';
        $xml .= '<c r="AD'.$currentRow.'" s="17"><v>'.$total['smp']['siswa']['l'].'</v></c>';
        $xml .= '<c r="AE'.$currentRow.'" s="17"><v>'.$total['smp']['siswa']['p'].'</v></c>';
        $xml .= '<c r="AF'.$currentRow.'" s="17"><v>'.$total['smp']['siswa']['jml'].'</v></c>';
        $xml .= '<c r="AG'.$currentRow.'" s="18"><v>'.$total['smp']['capaian']['l'].'</v></c>';
        $xml .= '<c r="AH'.$currentRow.'" s="18"><v>'.$total['smp']['capaian']['p'].'</v></c>';
        $xml .= '<c r="AI'.$currentRow.'" s="18"><v>'.$total['smp']['capaian']['jml'].'</v></c>';

        // Spacer AJ
        $xml .= '<c r="AJ'.$currentRow.'"/>';

        // SMA Total
        $smaTotal = $total['sma'] ?? ['penduduk' => ['l' => 0, 'p' => 0, 'jml' => 0], 'siswa' => ['l' => 0, 'p' => 0, 'jml' => 0], 'capaian' => ['l' => 0, 'p' => 0, 'jml' => 0]];
        $xml .= '<c r="AK'.$currentRow.'" s="16"/>';
        $xml .= '<c r="AL'.$currentRow.'" t="inlineStr" s="16"><is><t>Jumlah</t></is></c>';
        $xml .= '<c r="AM'.$currentRow.'" s="17"><v>'.$smaTotal['penduduk']['l'].'</v></c>';
        $xml .= '<c r="AN'.$currentRow.'" s="17"><v>'.$smaTotal['penduduk']['p'].'</v></c>';
        $xml .= '<c r="AO'.$currentRow.'" s="17"><v>'.$smaTotal['penduduk']['jml'].'</v></c>';
        $xml .= '<c r="AP'.$currentRow.'" s="17"><v>'.$smaTotal['siswa']['l'].'</v></c>';
        $xml .= '<c r="AQ'.$currentRow.'" s="17"><v>'.$smaTotal['siswa']['p'].'</v></c>';
        $xml .= '<c r="AR'.$currentRow.'" s="17"><v>'.$smaTotal['siswa']['jml'].'</v></c>';
        $xml .= '<c r="AS'.$currentRow.'" s="18"><v>'.$smaTotal['capaian']['l'].'</v></c>';
        $xml .= '<c r="AT'.$currentRow.'" s="18"><v>'.$smaTotal['capaian']['p'].'</v></c>';
        $xml .= '<c r="AU'.$currentRow.'" s="18"><v>'.$smaTotal['capaian']['jml'].'</v></c>';
        $xml .= '</row>';
        $currentRow++;

        // Baris Sumber Data
        $xml .= '<row r="'.$currentRow.'" ht="20" customHeight="1">';
        $xml .= '<c r="A'.$currentRow.'" t="inlineStr" s="19"><is><t>'.htmlspecialchars((string) $sumber, ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '</row>';

        $xml .= '</sheetData>';

        // Merged cells (24 merges total)
        $xml .= '<mergeCells count="24">';
        $xml .= '<mergeCell ref="A2:K2"/>';
        $xml .= '<mergeCell ref="M2:W2"/>';
        $xml .= '<mergeCell ref="Y2:AI2"/>';
        $xml .= '<mergeCell ref="AK2:AU2"/>';
        $xml .= '<mergeCell ref="A4:A5"/>';
        $xml .= '<mergeCell ref="B4:B5"/>';
        $xml .= '<mergeCell ref="C4:E4"/>';
        $xml .= '<mergeCell ref="F4:H4"/>';
        $xml .= '<mergeCell ref="I4:K4"/>';
        $xml .= '<mergeCell ref="M4:M5"/>';
        $xml .= '<mergeCell ref="N4:N5"/>';
        $xml .= '<mergeCell ref="O4:Q4"/>';
        $xml .= '<mergeCell ref="R4:T4"/>';
        $xml .= '<mergeCell ref="U4:W4"/>';
        $xml .= '<mergeCell ref="Y4:Y5"/>';
        $xml .= '<mergeCell ref="Z4:Z5"/>';
        $xml .= '<mergeCell ref="AA4:AC4"/>';
        $xml .= '<mergeCell ref="AD4:AF4"/>';
        $xml .= '<mergeCell ref="AG4:AI4"/>';
        $xml .= '<mergeCell ref="AK4:AK5"/>';
        $xml .= '<mergeCell ref="AL4:AL5"/>';
        $xml .= '<mergeCell ref="AM4:AO4"/>';
        $xml .= '<mergeCell ref="AP4:AR4"/>';
        $xml .= '<mergeCell ref="AS4:AU4"/>';
        $xml .= '</mergeCells>';

        $xml .= '</worksheet>';

        return $xml;
    }
}
