<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Contracts\Encryption\DecryptException;

trait HasObfuscatedId
{
    /**
     * Obtener el ID obfuscado del modelo
     */
    public function getObfuscatedIdAttribute(): string
    {
        return $this->obfuscateId($this->id);
    }

    /**
     * Obfuscar un ID
     */
    public static function obfuscateId(int $id): string
    {
        return base64_encode(Crypt::encryptString($id));
    }

    /**
     * Desobfuscar un ID
     */
    public static function deobfuscateId(string $obfuscatedId): ?int
    {
        try {
            $decrypted = Crypt::decryptString(base64_decode($obfuscatedId));
            return (int) $decrypted;
        } catch (DecryptException $e) {
            return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Buscar modelo por ID obfuscado
     */
    public static function findByObfuscatedId(string $obfuscatedId)
    {
        $id = static::deobfuscateId($obfuscatedId);
        
        if ($id === null) {
            return null;
        }

        return static::find($id);
    }

    /**
     * Buscar modelo por ID obfuscado o fallar
     */
    public static function findByObfuscatedIdOrFail(string $obfuscatedId)
    {
        $model = static::findByObfuscatedId($obfuscatedId);
        
        if ($model === null) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException();
        }

        return $model;
    }

    /**
     * Override toArray para incluir ID obfuscado en API responses
     */
    public function toArray()
    {
        $array = parent::toArray();
        
        // Solo agregar obfuscated_id en contextos API
        if (request()->is('api/*')) {
            $array['obfuscated_id'] = $this->obfuscated_id;
        }
        
        return $array;
    }
}