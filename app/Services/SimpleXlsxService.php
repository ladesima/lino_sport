<?php
namespace App\Services;

use ZipArchive;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SimpleXlsxService {
    /**
     * Convert 1-based column index to Excel column letters (1 -> A, 27 -> AA, etc.)
     */
    public static function colLetter(int $col): string {
        $letter = '';
        while ($col > 0) {
            $mod = ($col - 1) % 26;
            $letter = chr(65 + $mod) . $letter;
            $col = (int)(($col - $mod) / 26);
        }
        return $letter;
    }

    /**
     * Escape string for XML
     */
    public static function xmlEscape(string $str): string {
        return htmlspecialchars($str, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }

    /**
     * Stream download an XLSX file
     *
     * @param string $filename e.g. "laporan_booking_2026.xlsx"
     * @param array $headers List of header column titles
     * @param array $rows List of row arrays
     * @param string $sheetTitle Title of the sheet
     * @return StreamedResponse
     */
    public static function download(string $filename, array $headers, array $rows, string $sheetTitle = 'Data Booking'): StreamedResponse {
        if (!str_ends_with(strtolower($filename), '.xlsx')) {
            $filename .= '.xlsx';
        }

        $xlsxContent = self::generate($headers, $rows, $sheetTitle);

        return response()->streamDownload(function () use ($xlsxContent) {
            echo $xlsxContent;
        }, $filename, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Cache-Control'       => 'max-age=0, no-cache, no-store, must-revalidate',
            'Pragma'              => 'public',
        ]);
    }

    /**
     * Generate binary content of XLSX file
     */
    public static function generate(array $headers, array $rows, string $sheetTitle = 'Data Booking'): string {
        $tempFile = tempnam(sys_get_temp_dir(), 'xlsx_');
        $zip = new ZipArchive();
        if ($zip->open($tempFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException('Gagal membuat file arsip Excel sementara.');
        }

        $totalCols = max(1, count($headers));
        $totalRows = count($rows) + 1; // +1 for header
        $lastColLetter = self::colLetter($totalCols);

        // 1. [Content_Types].xml
        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
        $zip->addFromString('[Content_Types].xml', $contentTypes);

        // 2. _rels/.rels
        $rels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
        $zip->addFromString('_rels/.rels', $rels);

        // 3. xl/_rels/workbook.xml.rels
        $wbRels = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            . '</Relationships>';
        $zip->addFromString('xl/_rels/workbook.xml.rels', $wbRels);

        // 4. xl/workbook.xml
        $safeSheetName = self::xmlEscape(mb_substr($sheetTitle, 0, 31));
        $workbookXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="25600" windowHeight="14400"/></bookViews>'
            . '<sheets><sheet name="' . $safeSheetName . '" sheetId="1" r:id="rId1"/></sheets>'
            . '</workbook>';
        $zip->addFromString('xl/workbook.xml', $workbookXml);

        // 5. xl/styles.xml (Professional emerald palette)
        $stylesXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<fonts count="3">'
            . '<font><sz val="10"/><name val="Segoe UI"/><color rgb="FF1F2937"/></font>' // 0: Normal
            . '<font><b/><sz val="10.5"/><name val="Segoe UI"/><color rgb="FFFFFFFF"/></font>' // 1: Header Bold White
            . '<font><b/><sz val="10"/><name val="Segoe UI"/><color rgb="FF0F5132"/></font>' // 2: Highlight Green
            . '</fonts>'
            . '<fills count="4">'
            . '<fill><patternFill patternType="none"/></fill>' // 0
            . '<fill><patternFill patternType="gray125"/></fill>' // 1
            . '<fill><patternFill patternType="solid"><fgColor rgb="FF065F46"/><bgColor indexed="64"/></patternFill></fill>' // 2: Emerald Green Header
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFF0FDF4"/><bgColor indexed="64"/></patternFill></fill>' // 3: Zebra Light Green
            . '</fills>'
            . '<borders count="2">'
            . '<border><left/><right/><top/><bottom/></border>' // 0: none
            . '<border>' // 1: thin grey border
            . '<left style="thin"><color rgb="FFE5E7EB"/></left>'
            . '<right style="thin"><color rgb="FFE5E7EB"/></right>'
            . '<top style="thin"><color rgb="FFE5E7EB"/></top>'
            . '<bottom style="thin"><color rgb="FFE5E7EB"/></bottom>'
            . '</border>'
            . '</borders>'
            . '<cellStyleXfs count="1">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0"/>'
            . '</cellStyleXfs>'
            . '<cellXfs count="4">'
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>' // 0: Default
            . '<xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>' // 1: Header
            . '<xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0" applyFont="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>' // 2: Data row regular
            . '<xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0" applyFont="1" applyFill="1" applyBorder="1" applyAlignment="1"><alignment vertical="center"/></xf>' // 3: Data row zebra
            . '</cellXfs>'
            . '</styleSheet>';
        $zip->addFromString('xl/styles.xml', $stylesXml);

        // 6. xl/worksheets/sheet1.xml
        // Calculate dynamic column widths based on contents
        $colWidths = [];
        foreach ($headers as $cIdx => $hText) {
            $colWidths[$cIdx] = max(12, mb_strlen((string)$hText) + 4);
        }
        foreach (array_slice($rows, 0, 50) as $row) {
            foreach (array_values($row) as $cIdx => $val) {
                $len = mb_strlen((string)$val);
                if ($len > ($colWidths[$cIdx] ?? 12)) {
                    $colWidths[$cIdx] = min(40, $len + 3);
                }
            }
        }

        $colsXml = '<cols>';
        foreach ($colWidths as $cIdx => $w) {
            $cNum = $cIdx + 1;
            $colsXml .= '<col min="' . $cNum . '" max="' . $cNum . '" width="' . $w . '" customWidth="1"/>';
        }
        $colsXml .= '</cols>';

        $sheetDataXml = '<sheetData>';

        // Header Row (Row 1)
        $sheetDataXml .= '<row r="1" ht="28" customHeight="1">';
        foreach (array_values($headers) as $cIdx => $header) {
            $cellRef = self::colLetter($cIdx + 1) . '1';
            $sheetDataXml .= '<c r="' . $cellRef . '" t="inlineStr" s="1"><is><t>' . self::xmlEscape((string)$header) . '</t></is></c>';
        }
        $sheetDataXml .= '</row>';

        // Data Rows
        $rowNum = 2;
        foreach ($rows as $row) {
            $styleId = ($rowNum % 2 === 0) ? '2' : '3';
            $sheetDataXml .= '<row r="' . $rowNum . '" ht="22" customHeight="1">';
            $cIdx = 1;
            foreach (array_values($row) as $cellVal) {
                $cellRef = self::colLetter($cIdx) . $rowNum;
                $valStr = (string)$cellVal;

                // If strictly numeric (integers or decimals) and doesn't start with leading zero (unless '0')
                if (is_numeric($valStr) && !str_starts_with($valStr, '+') && (strlen($valStr) === 1 || !str_starts_with($valStr, '0'))) {
                    $sheetDataXml .= '<c r="' . $cellRef . '" s="' . $styleId . '"><v>' . $valStr . '</v></c>';
                } else {
                    $sheetDataXml .= '<c r="' . $cellRef . '" t="inlineStr" s="' . $styleId . '"><is><t>' . self::xmlEscape($valStr) . '</t></is></c>';
                }
                $cIdx++;
            }
            $sheetDataXml .= '</row>';
            $rowNum++;
        }
        $sheetDataXml .= '</sheetData>';

        $sheetXml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n"
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . '<sheetViews><sheetView tabSelected="1" workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            . '<sheetFormatPr defaultRowHeight="20"/>'
            . $colsXml
            . $sheetDataXml
            . '<autoFilter ref="A1:' . $lastColLetter . ($totalRows) . '"/>'
            . '</worksheet>';
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        $content = file_get_contents($tempFile);
        @unlink($tempFile);

        return $content;
    }
}
