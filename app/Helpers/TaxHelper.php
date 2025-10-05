<?php

namespace App\Helpers;

class TaxHelper
{
    /**
     * Obtener la tasa de IVA configurada
     * 
     * @return float
     */
    public static function getIvaRate(): float
    {
        return (float) config('app.iva_rate', 0.15);
    }

    /**
     * Obtener el porcentaje de IVA (ej: 15 para 0.15)
     * 
     * @return int
     */
    public static function getIvaPercentage(): int
    {
        return (int) (self::getIvaRate() * 100);
    }

    /**
     * Calcular el IVA de un subtotal
     * 
     * @param float $subtotal
     * @return float
     */
    public static function calculateIva(float $subtotal): float
    {
        return round($subtotal * self::getIvaRate(), 2);
    }

    /**
     * Calcular el total con IVA
     * 
     * @param float $subtotal
     * @return float
     */
    public static function calculateTotal(float $subtotal): float
    {
        return round($subtotal + self::calculateIva($subtotal), 2);
    }

    /**
     * Obtener el texto formateado del IVA (ej: "IVA (15%)")
     * 
     * @return string
     */
    public static function getIvaLabel(): string
    {
        return 'IVA (' . self::getIvaPercentage() . '%)';
    }

    /**
     * Obtener solo el porcentaje formateado (ej: "15%")
     * 
     * @return string
     */
    public static function getIvaPercentageLabel(): string
    {
        return self::getIvaPercentage() . '%';
    }

    /**
     * Extraer el subtotal desde un total con IVA
     * 
     * @param float $total
     * @return float
     */
    public static function extractSubtotal(float $total): float
    {
        return round($total / (1 + self::getIvaRate()), 2);
    }

    /**
     * Extraer el IVA desde un total que ya incluye IVA
     * 
     * @param float $total
     * @return float
     */
    public static function extractIva(float $total): float
    {
        $subtotal = self::extractSubtotal($total);
        return round($total - $subtotal, 2);
    }
}
