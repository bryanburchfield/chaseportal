<?php

namespace App\Services;

use Codedge\Fpdf\Fpdf\Fpdf;

class PDF extends Fpdf
{
    // Colored table
    public function FancyTable($header, $data)
    {

        // Calc relative col widths based on data
        $cols = count($header);

        $this->SetFont('Arial', '', 8);

        // this should really be based on maxwidth, but we could end up
        // with different pages being different font sizes
        // Probably should make it a report param
        // if($cols > 12) {
        //     $this->SetFont('Arial','',6);
        // }

        $maxwidth = 340;
        $totwidth = 0;
        $colwidth = [];

        // find max len of each col
        foreach ($header as $k => $v) {
            $w = $this->GetStringWidth($v);

            if (!isset($colwidth[$k])) {
                $colwidth[$k] = 0;
            }
            if ($w > $colwidth[$k]) {
                $colwidth[$k] = $w;
            }
        }
        foreach ($data as $row) {
            foreach ($row as $k => $v) {
                $w = $this->GetStringWidth($v);
                if ($w > $colwidth[$k]) {
                    $colwidth[$k] = $w;
                }
            }
        }

        // Figure percentage of max width
        foreach ($colwidth as $w) {
            $totwidth += $w;
        }
        foreach ($colwidth as &$w) {
            $w = $w / $totwidth * $maxwidth;
        }

        // Colors, line width and bold font
        $this->SetFillColor(32, 48, 71);
        $this->SetTextColor(255);
        $this->SetDrawColor(16, 24, 35);
        $this->SetLineWidth(.3);
        $this->SetFont('', 'B');

        // Header
        for ($i = 0; $i < count($header); $i++) {
            $this->CellFitScale($colwidth[$i], 7, $header[$i], 1, 0, 'C', true);
        }
        $this->Ln();

        // Color and font restoration
        $this->SetFillColor(224, 235, 255);
        $this->SetTextColor(0);
        $this->SetFont('');
        // Data
        $fill = false;
        foreach ($data as $row) {
            foreach ($row as $k => $col) {
                $this->CellFitScale($colwidth[$k], 6, trim($col), 'LR', 0, 'L', $fill);
            }
            $this->Ln();
            $fill = !$fill;
        }
    }

    // Cell with horizontal scaling if text is too wide
    private function CellFit($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '', $scale = false, $force = true)
    {
        // Get string width
        $str_width = $this->GetStringWidth($txt);

        // Calculate ratio to fit cell
        if ($w == 0) {
            $w = $this->w - $this->rMargin - $this->x;
        }
        $ratio = $str_width == 0 ? 0 : ($w - $this->cMargin * 2) / $str_width;

        $fit = ($ratio < 1 || ($ratio > 1 && $force));
        if ($fit) {
            if ($scale) {
                // Calculate horizontal scaling
                $horiz_scale = $ratio * 100.0;
                //Set horizontal scaling
                $this->_out(sprintf('BT %.2F Tz ET', $horiz_scale));
            } else {
                // Calculate character spacing in points
                $char_space = ($w - $this->cMargin * 2 - $str_width) / max($this->MBGetStringLength($txt) - 1, 1) * $this->k;
                // Set character spacing
                $this->_out(sprintf('BT %.2F Tc ET', $char_space));
            }
            // Override user alignment (since text will fill up cell)
            $align = '';
        }

        // Pass on to Cell method
        $this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);

        // Reset character spacing/horizontal scaling
        if ($fit) {
            $this->_out('BT ' . ($scale ? '100 Tz' : '0 Tc') . ' ET');
        }
    }

    // Cell with horizontal scaling only if necessary
    public function CellFitScale($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = false, $link = '')
    {
        $this->CellFit($w, $h, $txt, $border, $ln, $align, $fill, $link, true, false);
    }

    private function MBGetStringLength($s)
    {
        if ($this->CurrentFont['type'] == 'Type0') {
            $len = 0;
            $nbbytes = strlen($s);
            for ($i = 0; $i < $nbbytes; $i++) {
                if (ord($s[$i]) < 128) {
                    $len++;
                } else {
                    $len++;
                    $i++;
                }
            }
            return $len;
        }

        return strlen($s);
    }
}
