<?php

if (!function_exists('formatRupiah')) {
    /**
     * Format a number as Indonesian Rupiah currency.
     * 
     * @param float|int $amount The amount to format
     * @param bool $withPrefix Whether to include "Rp" prefix
     * @return string Formatted currency string
     */
    function formatRupiah($amount, bool $withPrefix = true): string
    {
        $formatted = number_format($amount, 0, ',', '.');
        return $withPrefix ? 'Rp ' . $formatted : $formatted;
    }
}

if (!function_exists('formatRupiahShort')) {
    /**
     * Format a number as shortened Indonesian Rupiah (e.g., 12 Jt, 1.5 M).
     * 
     * @param float|int $amount The amount to format
     * @return string Shortened currency string
     */
    function formatRupiahShort($amount): string
    {
        if ($amount >= 1000000000) {
            return 'Rp ' . number_format($amount / 1000000000, 1, ',', '.') . ' M';
        } elseif ($amount >= 1000000) {
            return 'Rp ' . number_format($amount / 1000000, 1, ',', '.') . ' Jt';
        } elseif ($amount >= 1000) {
            return 'Rp ' . number_format($amount / 1000, 0) . ' Rb';
        }
        return 'Rp ' . number_format($amount, 0, ',', '.');
    }
}
