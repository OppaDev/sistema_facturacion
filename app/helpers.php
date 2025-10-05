<?php

use App\Helpers\TaxHelper;

if (!function_exists('iva_rate')) {
    /**
     * Obtener la tasa de IVA (ej: 0.15)
     *
     * @return float
     */
    function iva_rate(): float
    {
        return TaxHelper::getIvaRate();
    }
}

if (!function_exists('iva_percentage')) {
    /**
     * Obtener el porcentaje de IVA (ej: 15)
     *
     * @return int
     */
    function iva_percentage(): int
    {
        return TaxHelper::getIvaPercentage();
    }
}

if (!function_exists('calculate_iva')) {
    /**
     * Calcular el IVA de un subtotal
     *
     * @param float $subtotal
     * @return float
     */
    function calculate_iva(float $subtotal): float
    {
        return TaxHelper::calculateIva($subtotal);
    }
}

if (!function_exists('calculate_total_with_iva')) {
    /**
     * Calcular el total con IVA
     *
     * @param float $subtotal
     * @return float
     */
    function calculate_total_with_iva(float $subtotal): float
    {
        return TaxHelper::calculateTotal($subtotal);
    }
}

if (!function_exists('iva_label')) {
    /**
     * Obtener la etiqueta del IVA (ej: "IVA (15%)")
     *
     * @return string
     */
    function iva_label(): string
    {
        return TaxHelper::getIvaLabel();
    }
}
