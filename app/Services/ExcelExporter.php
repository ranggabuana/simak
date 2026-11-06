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

    protected function getContentTypesXml(bool $hasDrawings = false): string
    {
        $extra = '';
        if ($hasDrawings) {
            $extra = '<Default Extension="png" ContentType="image/png"/>'
                .'<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$extra
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

    protected function getMultiSheetContentTypesXml(int $sheetCount, bool $hasDrawings = false): string
    {
        $extra = '';
        if ($hasDrawings) {
            $extra = '<Default Extension="png" ContentType="image/png"/>'
                .'<Override PartName="/xl/drawings/drawing1.xml" ContentType="application/vnd.openxmlformats-officedocument.drawing+xml"/>';
        }

        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .$extra
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $xml .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }

        $xml .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';

        return $xml;
    }

    protected function getMultiSheetWorkbookRelsXml(int $sheetCount): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

        for ($i = 1; $i <= $sheetCount; $i++) {
            $xml .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
        }

        $styleRId = $sheetCount + 1;
        $xml .= '<Relationship Id="rId'.$styleRId.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';

        return $xml;
    }

    protected function getMultiSheetWorkbookXml(array $sheetNames): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="14400"/></bookViews>'
            .'<sheets>';

        foreach ($sheetNames as $idx => $name) {
            $sheetId = $idx + 1;
            $safeName = htmlspecialchars(mb_substr($name, 0, 31), ENT_XML1, 'UTF-8');
            $xml .= '<sheet name="'.$safeName.'" sheetId="'.$sheetId.'" r:id="rId'.$sheetId.'"/>';
        }

        $xml .= '</sheets></workbook>';

        return $xml;
    }

    protected function getStylesXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<numFmts count="1">'
            .'<numFmt numFmtId="164" formatCode="0.00&quot; %&quot;"/>'
            .'</numFmts>'
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
            // 13: Official Data Decimal/Percent (numFmtId 164 = 0.00 %, Font 0, Border 1, right)
            .'<xf numFmtId="164" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 14: Official Data Text Left (Font 0, Border 1, left)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 15: Official Data Text Center (Font 0, Border 1, center)
            .'<xf numFmtId="49" fontId="0" fillId="0" borderId="1" xfId="0" applyNumberFormat="1" applyFont="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf>'
            // 16: Official Total Row Bold Text (Font 4, Fill 5, Border 2, left)
            .'<xf numFmtId="49" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="left" vertical="center"/></xf>'
            // 17: Official Total Row Bold Integer (numFmtId 3, Font 4, Fill 5, Border 2, right)
            .'<xf numFmtId="3" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
            // 18: Official Total Row Bold Decimal/Percent (numFmtId 164 = 0.00 %, Font 4, Fill 5, Border 2, right)
            .'<xf numFmtId="164" fontId="4" fillId="5" borderId="2" xfId="0" applyNumberFormat="1" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="right" vertical="center"/></xf>'
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
     * Unduh file Excel resmi indikator (format baru sesuai acuan format-baru.jpeg)
     * Menghasilkan lembar kerja per jenjang (PAUD, SD, SMP, SMA) dengan 28 kolom
     * (Kecamatan, Penduduk L/P/Jml, rincian per bentuk sekolah L/P/Jml, dan Capaian % L/P/Jml).
     *
     * @param  array<string, mixed>  $officialData
     */
    public function downloadIndikatorOfficialXlsx(string $filename, array $officialData, array $chartImages = []): StreamedResponse
    {
        if (! str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $headersResponse = [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'max-age=0',
        ];

        return response()->stream(function () use ($officialData) {
            $tmpFile = tempnam(sys_get_temp_dir(), 'simak_official_xlsx_');
            $zip = new ZipArchive;

            if ($zip->open($tmpFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                return;
            }

            $blocks = $officialData['blocks'] ?? [
                'paud' => [
                    'key' => 'paud',
                    'name' => 'PAUD',
                    'title' => 'PAUD (Usia 5-6 Tahun)',
                    'penduduk_header' => 'Penduduk Usia 5-6 Tahun',
                    'siswa_header' => 'Jumlah Siswa Jenjang PAUD',
                    'capaian_header' => 'Capaian PAUD',
                    'forms' => ['PAUD' => 'PAUD', 'TK' => 'TK', 'SD' => 'SD', 'MI' => 'MI'],
                ],
                'sd' => [
                    'key' => 'sd',
                    'name' => 'SD',
                    'title' => 'SD (Usia 7-12 Tahun)',
                    'penduduk_header' => 'Penduduk Usia 7-12 Tahun',
                    'siswa_header' => 'Jumlah Siswa Jenjang SD',
                    'capaian_header' => 'Capaian SD',
                    'forms' => ['SD' => 'SD', 'MI' => 'MI', 'SMP' => 'SMP', 'MTS' => 'MTs', 'SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA'],
                ],
                'smp' => [
                    'key' => 'smp',
                    'name' => 'SMP',
                    'title' => 'SMP (Usia 13-15 Tahun)',
                    'penduduk_header' => 'Penduduk Usia 13-15 Tahun',
                    'siswa_header' => 'Jumlah Siswa Jenjang SMP',
                    'capaian_header' => 'Capaian SMP',
                    'forms' => ['SD' => 'SD', 'MI' => 'MI', 'SMP' => 'SMP', 'MTS' => 'MTs', 'SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA'],
                ],
                'sma' => [
                    'key' => 'sma',
                    'name' => 'SMA / SMK / MA',
                    'title' => 'SMA/SMK/MA (Usia 16-18 Tahun)',
                    'penduduk_header' => 'Penduduk Usia 16-18 Tahun',
                    'siswa_header' => 'Jumlah Siswa Jenjang SMA/SMK/MA',
                    'capaian_header' => 'Capaian SMA/SMK/MA',
                    'forms' => ['SD' => 'SD', 'MI' => 'MI', 'SMP' => 'SMP', 'MTS' => 'MTs', 'SMA' => 'SMA', 'SMK' => 'SMK', 'MA' => 'MA'],
                ],
            ];

            $sheetNames = [
                'PAUD (5-6 Th)',
                'SD (7-12 Th)',
                'SMP (13-15 Th)',
                'SMA-SMK-MA (16-18 Th)',
            ];
            $sheetCount = count($blocks);

            // 1. [Content_Types].xml
            $zip->addFromString('[Content_Types].xml', $this->getMultiSheetContentTypesXml($sheetCount, false));

            // 2. _rels/.rels
            $zip->addFromString('_rels/.rels', $this->getRootRelsXml());

            // 3. xl/_rels/workbook.xml.rels
            $zip->addFromString('xl/_rels/workbook.xml.rels', $this->getMultiSheetWorkbookRelsXml($sheetCount));

            // 4. xl/workbook.xml
            $zip->addFromString('xl/workbook.xml', $this->getMultiSheetWorkbookXml($sheetNames));

            // 5. xl/styles.xml
            $zip->addFromString('xl/styles.xml', $this->getStylesXml());

            // 6. xl/worksheets/sheet1..4.xml
            $sheetIdx = 1;
            foreach ($blocks as $bKey => $bCfg) {
                $sheetXml = $this->getIndikatorBlockWorksheetXml($bKey, $bCfg, $officialData);
                $zip->addFromString("xl/worksheets/sheet{$sheetIdx}.xml", $sheetXml);
                $sheetIdx++;
            }

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
     * Bangun XML Worksheet per blok jenjang (PAUD, SD, SMP, SMA) sesuai acuan format-baru.jpeg
     *
     * @param  array<string, mixed>  $blockConfig
     * @param  array<string, mixed>  $officialData
     */
    protected function getIndikatorBlockWorksheetXml(string $blockKey, array $blockConfig, array $officialData): string
    {
        $forms = $blockConfig['forms'] ?? [];
        $formsCount = count($forms);
        $totalCols = 1 + 3 + ($formsCount * 3) + 3;
        $lastColLetter = self::getColumnLetter($totalCols);

        // Lebar Kolom: A=22, B..D=9.5, Forms=8.0,8.0,9.0, Capaian=10.5,10.5,11.5
        $colWidths = [
            1 => 22.0, // A: Kecamatan
            2 => 9.5,  // B: Pop L
            3 => 9.5,  // C: Pop P
            4 => 10.5, // D: Pop Jml
        ];
        $colIdx = 5;
        foreach ($forms as $fKey => $fLabel) {
            $colWidths[$colIdx++] = 8.0; // L
            $colWidths[$colIdx++] = 8.0; // P
            $colWidths[$colIdx++] = 9.0; // Jml
        }
        $colWidths[$colIdx++] = 10.5; // Capaian L
        $colWidths[$colIdx++] = 10.5; // Capaian P
        $colWidths[$colIdx++] = 11.5; // Capaian Jml

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
        foreach ($colWidths as $cNum => $width) {
            $xml .= '<col min="'.$cNum.'" max="'.$cNum.'" width="'.$width.'" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';

        // Baris 1: Judul Tabel Tebal Sesuai Acuan format-baru.jpeg
        $xml .= '<row r="1" ht="26" customHeight="1">';
        $xml .= '<c r="A1" t="inlineStr" s="11"><is><t>'.htmlspecialchars((string) $blockConfig['title'], ENT_XML1, 'UTF-8').'</t></is></c>';
        for ($c = 2; $c <= $totalCols; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'1" s="11"/>';
        }
        $xml .= '</row>';

        // Baris 2: Kosong
        $xml .= '<row r="2" ht="12"/>';

        // Baris 3: Header Tingkat 1 (Kecamatan, Penduduk Usia, Siswa Usia, Capaian Indikator)
        $xml .= '<row r="3" ht="28" customHeight="1">';
        // A3: Kecamatan
        $xml .= '<c r="A3" t="inlineStr" s="10"><is><t>Kecamatan</t></is></c>';
        // B3..D3: Penduduk
        $xml .= '<c r="B3" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $blockConfig['penduduk_header'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="C3" s="10"/><c r="D3" s="10"/>';
        // E3: Siswa
        $xml .= '<c r="E3" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $blockConfig['siswa_header'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $formLastCol = 4 + ($formsCount * 3);
        for ($c = 6; $c <= $formLastCol; $c++) {
            $xml .= '<c r="'.self::getColumnLetter($c).'3" s="10"/>';
        }
        // Capaian Start Col
        $capStartCol = $formLastCol + 1;
        $xml .= '<c r="'.self::getColumnLetter($capStartCol).'3" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $blockConfig['capaian_header'], ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 1).'3" s="10"/>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 2).'3" s="10"/>';
        $xml .= '</row>';

        // Baris 4: Header Tingkat 2 (Sub-Header Tiap Bentuk Pendidikan)
        $xml .= '<row r="4" ht="22" customHeight="1">';
        $xml .= '<c r="A4" s="10"/><c r="B4" s="10"/><c r="C4" s="10"/><c r="D4" s="10"/>';
        $curCol = 5;
        foreach ($forms as $fKey => $fLabel) {
            $xml .= '<c r="'.self::getColumnLetter($curCol).'4" t="inlineStr" s="10"><is><t>'.htmlspecialchars((string) $fLabel, ENT_XML1, 'UTF-8').'</t></is></c>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 1).'4" s="10"/>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 2).'4" s="10"/>';
            $curCol += 3;
        }
        $xml .= '<c r="'.self::getColumnLetter($capStartCol).'4" s="10"/>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 1).'4" s="10"/>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 2).'4" s="10"/>';
        $xml .= '</row>';

        // Baris 5: Header Tingkat 3 (Sub-Kolom L, P, Jml)
        $xml .= '<row r="5" ht="20" customHeight="1">';
        $xml .= '<c r="A5" s="10"/>';
        // Penduduk
        $xml .= '<c r="B5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="C5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="D5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        // Tiap Bentuk Sekolah
        $curCol = 5;
        foreach ($forms as $fKey => $fLabel) {
            $xml .= '<c r="'.self::getColumnLetter($curCol).'5" t="inlineStr" s="10"><is><t>L</t></is></c>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 1).'5" t="inlineStr" s="10"><is><t>P</t></is></c>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 2).'5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
            $curCol += 3;
        }
        // Capaian
        $xml .= '<c r="'.self::getColumnLetter($capStartCol).'5" t="inlineStr" s="10"><is><t>L</t></is></c>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 1).'5" t="inlineStr" s="10"><is><t>P</t></is></c>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 2).'5" t="inlineStr" s="10"><is><t>Jml</t></is></c>';
        $xml .= '</row>';

        // Baris 6 s.d. 25: Data 20 Kecamatan
        $currentRow = 6;
        foreach ($officialData['rows'] as $r) {
            $bData = $r[$blockKey];
            $namaKec = 'Kec. '.ucwords(strtolower(preg_replace('/^kec(\.|\s+)/i', '', (string) $r['nama'])));

            $xml .= '<row r="'.$currentRow.'" ht="20" customHeight="1">';
            $xml .= '<c r="A'.$currentRow.'" t="inlineStr" s="14"><is><t>'.htmlspecialchars($namaKec, ENT_XML1, 'UTF-8').'</t></is></c>';

            // Penduduk
            $xml .= '<c r="B'.$currentRow.'" s="12"><v>'.$bData['penduduk']['l'].'</v></c>';
            $xml .= '<c r="C'.$currentRow.'" s="12"><v>'.$bData['penduduk']['p'].'</v></c>';
            $xml .= '<c r="D'.$currentRow.'" s="12"><v>'.$bData['penduduk']['jml'].'</v></c>';

            // Forms
            $curCol = 5;
            foreach ($forms as $fKey => $fLabel) {
                $f = $bData['breakdown'][$fKey] ?? ['l' => 0, 'p' => 0, 'jml' => 0];
                $xml .= '<c r="'.self::getColumnLetter($curCol).$currentRow.'" s="12"><v>'.$f['l'].'</v></c>';
                $xml .= '<c r="'.self::getColumnLetter($curCol + 1).$currentRow.'" s="12"><v>'.$f['p'].'</v></c>';
                $xml .= '<c r="'.self::getColumnLetter($curCol + 2).$currentRow.'" s="12"><v>'.$f['jml'].'</v></c>';
                $curCol += 3;
            }

            // Capaian
            $xml .= '<c r="'.self::getColumnLetter($capStartCol).$currentRow.'" s="13"><v>'.$bData['capaian']['l'].'</v></c>';
            $xml .= '<c r="'.self::getColumnLetter($capStartCol + 1).$currentRow.'" s="13"><v>'.$bData['capaian']['p'].'</v></c>';
            $xml .= '<c r="'.self::getColumnLetter($capStartCol + 2).$currentRow.'" s="13"><v>'.$bData['capaian']['jml'].'</v></c>';

            $xml .= '</row>';
            $currentRow++;
        }

        // Baris 26: Jumlah Kabupaten
        $tot = $officialData['total'][$blockKey];
        $xml .= '<row r="'.$currentRow.'" ht="22" customHeight="1">';
        $xml .= '<c r="A'.$currentRow.'" t="inlineStr" s="16"><is><t>Jumlah</t></is></c>';

        // Penduduk Total
        $xml .= '<c r="B'.$currentRow.'" s="17"><v>'.$tot['penduduk']['l'].'</v></c>';
        $xml .= '<c r="C'.$currentRow.'" s="17"><v>'.$tot['penduduk']['p'].'</v></c>';
        $xml .= '<c r="D'.$currentRow.'" s="17"><v>'.$tot['penduduk']['jml'].'</v></c>';

        // Forms Total
        $curCol = 5;
        foreach ($forms as $fKey => $fLabel) {
            $fTot = $tot['breakdown'][$fKey] ?? ['l' => 0, 'p' => 0, 'jml' => 0];
            $xml .= '<c r="'.self::getColumnLetter($curCol).$currentRow.'" s="17"><v>'.$fTot['l'].'</v></c>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 1).$currentRow.'" s="17"><v>'.$fTot['p'].'</v></c>';
            $xml .= '<c r="'.self::getColumnLetter($curCol + 2).$currentRow.'" s="17"><v>'.$fTot['jml'].'</v></c>';
            $curCol += 3;
        }

        // Capaian Total
        $xml .= '<c r="'.self::getColumnLetter($capStartCol).$currentRow.'" s="18"><v>'.$tot['capaian']['l'].'</v></c>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 1).$currentRow.'" s="18"><v>'.$tot['capaian']['p'].'</v></c>';
        $xml .= '<c r="'.self::getColumnLetter($capStartCol + 2).$currentRow.'" s="18"><v>'.$tot['capaian']['jml'].'</v></c>';
        $xml .= '</row>';
        $currentRow++;

        // Baris Sumber Data
        $sumber = $officialData['sumber'] ?? 'Sumber data : DISDUKCAPIL, DAPODIK KEMENDIKBUDRISTEK, EMIS - KEMENAG';
        $xml .= '<row r="'.$currentRow.'" ht="20" customHeight="1">';
        $xml .= '<c r="A'.$currentRow.'" t="inlineStr" s="19"><is><t>'.htmlspecialchars((string) $sumber, ENT_XML1, 'UTF-8').'</t></is></c>';
        $xml .= '</row>';

        $xml .= '</sheetData>';

        // Merged cells
        $merges = [
            'A1:'.$lastColLetter.'1', // Judul
            'A3:A5',                 // Kecamatan
            'B3:D4',                 // Penduduk
            'E3:'.self::getColumnLetter($formLastCol).'3', // Siswa
            self::getColumnLetter($capStartCol).'3:'.self::getColumnLetter($capStartCol + 2).'4', // Capaian
        ];
        $curCol = 5;
        foreach ($forms as $fKey => $fLabel) {
            $merges[] = self::getColumnLetter($curCol).'4:'.self::getColumnLetter($curCol + 2).'4';
            $curCol += 3;
        }

        $xml .= '<mergeCells count="'.count($merges).'">';
        foreach ($merges as $m) {
            $xml .= '<mergeCell ref="'.$m.'"/>';
        }
        $xml .= '</mergeCells>';

        $xml .= '</worksheet>';

        return $xml;
    }

    /**
     * Relasi drawing pada worksheet
     */
    protected function getSheetDrawingRelsXml(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId_drawing" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/drawing" Target="../drawings/drawing1.xml"/>'
            .'</Relationships>';
    }

    /**
     * Relasi media gambar pada drawing1.xml
     */
    protected function getDrawingMediaRelsXml(int $count): string
    {
        $rels = '';
        for ($i = 1; $i <= $count; $i++) {
            $rels .= '<Relationship Id="rId_img'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/image" Target="../media/image'.$i.'.png"/>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .$rels
            .'</Relationships>';
    }

    /**
     * Bangun XML Drawing (DrawingML) untuk menyematkan grafik gambar ke worksheet Excel
     *
     * @param  array<string, string>  $validImages
     */
    protected function getDrawingXml(array $validImages): string
    {
        $anchors = '';
        $imgKeys = array_keys($validImages);

        // Chart 1: Jenjang vs Target (Row 29-52, Col A-L => Col 0-11)
        if (isset($validImages['jenjang'])) {
            $rId = 'rId_img'.(array_search('jenjang', $imgKeys) + 1);
            $anchors .= '
            <xdr:twoCellAnchor editAs="oneCell">
                <xdr:from><xdr:col>0</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>29</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>
                <xdr:to><xdr:col>11</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>52</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>
                <xdr:pic>
                    <xdr:nvPicPr>
                        <xdr:cNvPr id="2" name="Grafik Jenjang vs Target"/>
                        <xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr>
                    </xdr:nvPicPr>
                    <xdr:blipFill>
                        <a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="'.$rId.'"/>
                        <a:stretch><a:fillRect/></a:stretch>
                    </xdr:blipFill>
                    <xdr:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>
                </xdr:pic>
                <xdr:clientData/>
            </xdr:twoCellAnchor>';
        }

        // Chart 2: Komparasi Gender (Row 29-52, Col M-X => Col 12-23)
        if (isset($validImages['gender'])) {
            $rId = 'rId_img'.(array_search('gender', $imgKeys) + 1);
            $anchors .= '
            <xdr:twoCellAnchor editAs="oneCell">
                <xdr:from><xdr:col>12</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>29</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>
                <xdr:to><xdr:col>23</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>52</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>
                <xdr:pic>
                    <xdr:nvPicPr>
                        <xdr:cNvPr id="3" name="Grafik Komparasi Gender"/>
                        <xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr>
                    </xdr:nvPicPr>
                    <xdr:blipFill>
                        <a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="'.$rId.'"/>
                        <a:stretch><a:fillRect/></a:stretch>
                    </xdr:blipFill>
                    <xdr:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>
                </xdr:pic>
                <xdr:clientData/>
            </xdr:twoCellAnchor>';
        }

        // Chart 3: Sebaran 20 Kecamatan (Row 54-78, Col A-AU => Col 0-47)
        if (isset($validImages['kecamatan'])) {
            $rId = 'rId_img'.(array_search('kecamatan', $imgKeys) + 1);
            $anchors .= '
            <xdr:twoCellAnchor editAs="oneCell">
                <xdr:from><xdr:col>0</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>53</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:from>
                <xdr:to><xdr:col>47</xdr:col><xdr:colOff>0</xdr:colOff><xdr:row>78</xdr:row><xdr:rowOff>0</xdr:rowOff></xdr:to>
                <xdr:pic>
                    <xdr:nvPicPr>
                        <xdr:cNvPr id="4" name="Grafik Sebaran 20 Kecamatan"/>
                        <xdr:cNvPicPr><a:picLocks noChangeAspect="1"/></xdr:cNvPicPr>
                    </xdr:nvPicPr>
                    <xdr:blipFill>
                        <a:blip xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships" r:embed="'.$rId.'"/>
                        <a:stretch><a:fillRect/></a:stretch>
                    </xdr:blipFill>
                    <xdr:spPr><a:prstGeom prst="rect"><a:avLst/></a:prstGeom></xdr:spPr>
                </xdr:pic>
                <xdr:clientData/>
            </xdr:twoCellAnchor>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<xdr:wsDr xmlns:xdr="http://schemas.openxmlformats.org/drawingml/2006/spreadsheetDrawing" xmlns:a="http://schemas.openxmlformats.org/drawingml/2006/main">'
            .$anchors
            .'</xdr:wsDr>';
    }
}
