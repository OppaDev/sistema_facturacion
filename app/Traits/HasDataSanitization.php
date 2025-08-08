<?php

namespace App\Traits;

trait HasDataSanitization
{
    /**
     * Sanitizar string general removiendo HTML, scripts y caracteres peligrosos
     */
    protected function sanitizeString(?string $value): ?string
    {
        if ($value === null) return null;
        
        // Remover HTML tags y scripts
        $value = strip_tags($value);
        
        // Decodificar entidades HTML
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remover caracteres de control (excepto espacios, tabs y saltos de línea)
        $value = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $value);
        
        // Limpiar espacios múltiples
        $value = preg_replace('/\s+/', ' ', $value);
        
        // Trim y normalizar
        return trim($value);
    }

    /**
     * Sanitizar email
     */
    protected function sanitizeEmail(?string $email): ?string
    {
        if ($email === null) return null;
        
        // Limpiar y convertir a minúsculas
        $email = $this->sanitizeString($email);
        $email = strtolower($email);
        
        // Remover caracteres no válidos para email
        $email = preg_replace('/[^a-z0-9@._+-]/', '', $email);
        
        return $email;
    }

    /**
     * Sanitizar número de teléfono
     */
    protected function sanitizePhone(?string $phone): ?string
    {
        if ($phone === null) return null;
        
        // Mantener solo números, espacios, guiones, paréntesis y signo +
        $phone = preg_replace('/[^0-9\s\-\+\(\)]/', '', $phone);
        
        // Limpiar espacios múltiples
        $phone = preg_replace('/\s+/', ' ', $phone);
        
        return trim($phone);
    }

    /**
     * Sanitizar texto alfanumérico (para códigos, referencias, etc.)
     */
    protected function sanitizeAlphanumeric(?string $value): ?string
    {
        if ($value === null) return null;
        
        // Solo letras, números, guiones y guiones bajos
        $value = preg_replace('/[^a-zA-Z0-9\-_]/', '', $value);
        
        return trim($value);
    }

    /**
     * Sanitizar decimal/precio
     */
    protected function sanitizeDecimal($value)
    {
        if ($value === null) return null;
        
        // Convertir a string para manipular
        $value = (string) $value;
        
        // Remover todo excepto números, puntos y comas
        $value = preg_replace('/[^0-9\.,]/', '', $value);
        
        // Convertir comas a puntos (formato europeo a inglés)
        $value = str_replace(',', '.', $value);
        
        // Si hay múltiples puntos, mantener solo el último como decimal
        if (substr_count($value, '.') > 1) {
            $parts = explode('.', $value);
            $decimals = array_pop($parts);
            $value = implode('', $parts) . '.' . $decimals;
        }
        
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Sanitizar entero
     */
    protected function sanitizeInteger($value)
    {
        if ($value === null) return null;
        
        // Remover todo excepto números y signo negativo al principio
        $value = preg_replace('/[^0-9\-]/', '', (string) $value);
        
        // Asegurar que el signo negativo esté solo al principio
        if (substr_count($value, '-') > 1) {
            $value = '-' . str_replace('-', '', $value);
        } elseif (strpos($value, '-') > 0) {
            $value = str_replace('-', '', $value);
        }
        
        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Sanitizar nombre de archivo
     */
    protected function sanitizeFilename(?string $filename): ?string
    {
        if ($filename === null) return null;
        
        // Remover caracteres peligrosos para nombres de archivo
        $filename = preg_replace('/[^a-zA-Z0-9\-_\.]/', '_', $filename);
        
        // Prevenir nombres peligrosos
        $dangerousNames = ['.htaccess', '.htpasswd', 'web.config', 'index.php'];
        if (in_array(strtolower($filename), $dangerousNames)) {
            $filename = 'safe_' . $filename;
        }
        
        // Prevenir extensiones dobles peligrosas
        $filename = preg_replace('/\.php\./i', '.txt.', $filename);
        $filename = preg_replace('/\.asp\./i', '.txt.', $filename);
        $filename = preg_replace('/\.jsp\./i', '.txt.', $filename);
        
        return $filename;
    }

    /**
     * Sanitizar URL
     */
    protected function sanitizeUrl(?string $url): ?string
    {
        if ($url === null) return null;
        
        // Limpiar espacios
        $url = trim($url);
        
        // Validar que sea una URL válida
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }
        
        // Verificar esquema permitido
        $allowedSchemes = ['http', 'https'];
        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (!in_array($scheme, $allowedSchemes)) {
            return null;
        }
        
        return $url;
    }

    /**
     * Sanitizar texto libre (descripciones, comentarios, etc.)
     */
    protected function sanitizeFreeText(?string $text): ?string
    {
        if ($text === null) return null;
        
        // Remover HTML tags pero permitir algunos seguros
        $allowedTags = '<b><i><u><strong><em><br><p>';
        $text = strip_tags($text, $allowedTags);
        
        // Decodificar entidades HTML
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remover caracteres de control peligrosos
        $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/', '', $text);
        
        // Limpiar espacios múltiples pero mantener saltos de línea
        $text = preg_replace('/[ \t]+/', ' ', $text);
        
        return trim($text);
    }

    /**
     * Validar y sanitizar array de datos
     */
    protected function sanitizeArray(array $data, array $rules = []): array
    {
        $sanitized = [];
        
        foreach ($data as $key => $value) {
            // Sanitizar clave
            $sanitizedKey = $this->sanitizeString($key);
            
            if (is_array($value)) {
                $sanitized[$sanitizedKey] = $this->sanitizeArray($value, $rules[$key] ?? []);
            } else {
                // Aplicar regla específica si existe
                $rule = $rules[$key] ?? 'string';
                $sanitized[$sanitizedKey] = $this->sanitizeByRule($value, $rule);
            }
        }
        
        return $sanitized;
    }

    /**
     * Sanitizar por regla específica
     */
    protected function sanitizeByRule($value, string $rule)
    {
        switch ($rule) {
            case 'email':
                return $this->sanitizeEmail($value);
            case 'phone':
                return $this->sanitizePhone($value);
            case 'alphanumeric':
                return $this->sanitizeAlphanumeric($value);
            case 'decimal':
                return $this->sanitizeDecimal($value);
            case 'integer':
                return $this->sanitizeInteger($value);
            case 'filename':
                return $this->sanitizeFilename($value);
            case 'url':
                return $this->sanitizeUrl($value);
            case 'freetext':
                return $this->sanitizeFreeText($value);
            case 'string':
            default:
                return $this->sanitizeString($value);
        }
    }

    /**
     * Detectar posibles intentos de injection
     */
    protected function detectInjectionAttempt(string $value): bool
    {
        $patterns = [
            // SQL Injection
            '/(\b(SELECT|INSERT|UPDATE|DELETE|DROP|CREATE|ALTER|EXEC|UNION)\b)/i',
            '/(\b(OR|AND)\s+\d+\s*=\s*\d+)/i',
            '/(\';|\";\s*)/i',
            
            // XSS
            '/<script[^>]*>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            
            // Path Traversal
            '/\.\.\//',
            '/\.\.\\\\/',
            
            // Command Injection
            '/(\||;|&|`|\$\()/i',
            
            // LDAP Injection
            '/(\*|\(|\)|\\|\/)/',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $value)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Log intento de injection detectado
     */
    protected function logInjectionAttempt(string $value, string $context = ''): void
    {
        \Log::warning('Potential injection attempt detected', [
            'value' => $value,
            'context' => $context,
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'user_id' => auth()->id(),
            'endpoint' => request()->path(),
        ]);
    }
}