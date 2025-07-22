<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\FacturaSRIService;
use App\Models\Cliente;
use App\Models\FacturaDetalle;
use App\Models\User;

/**
 * @property int $id
 * @property int $cliente_id
 * @property int|null $usuario_id
 * @property int|null $factura_original_id
 * @property string $ruc_emisor
 * @property string $razon_social_emisor
 * @property string $direccion_emisor
 * @property string|null $num_autorizacion_sri
 * @property string|null $secuencial
 * @property string $establecimiento
 * @property string $punto_emision
 * @property string|null $numero_factura
 * @property string|null $cua
 * @property string|null $firma_digital
 * @property string|null $codigo_qr
 * @property string|null $forma_pago
 * @property string|null $fecha_autorizacion
 * @property numeric $subtotal
 * @property numeric $iva
 * @property numeric $total
 * @property string $estado
 * @property string|null $motivo_anulacion
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property string|null $numero_secuencial
 * @property \Illuminate\Support\Carbon|null $fecha_emision
 * @property string|null $hora_emision
 * @property string $ambiente
 * @property string $tipo_emision
 * @property string $tipo_documento
 * @property string|null $mensaje_autorizacion
 * @property string|null $contenido_qr
 * @property string|null $imagen_qr
 * @property string $estado_firma
 * @property \Illuminate\Support\Carbon|null $fecha_firma
 * @property string $estado_emision
 * @property \Illuminate\Support\Carbon|null $fecha_emision_email
 * @property-read User|null $actualizador
 * @property-read Cliente $cliente
 * @property-read User|null $creador
 * @property-read \Illuminate\Database\Eloquent\Collection<int, FacturaDetalle> $detalles
 * @property-read int|null $detalles_count
 * @property-read Factura|null $facturaOriginal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Factura> $facturasModificadas
 * @property-read int|null $facturas_modificadas_count
 * @property-read User|null $usuario
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura autorizadas()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura conDatosSRI()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereAmbiente($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereClienteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereCodigoQr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereContenidoQr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereCua($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereDireccionEmisor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereEstablecimiento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereEstado($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereEstadoEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereEstadoFirma($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFacturaOriginalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFechaAutorizacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFechaEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFechaEmisionEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFechaFirma($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFirmaDigital($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereFormaPago($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereHoraEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereImagenQr($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereIva($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereMensajeAutorizacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereMotivoAnulacion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereNumAutorizacionSri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereNumeroFactura($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereNumeroSecuencial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura wherePuntoEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereRazonSocialEmisor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereRucEmisor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereSecuencial($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereSubtotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereTipoDocumento($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereTipoEmision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereUpdatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura whereUsuarioId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Factura withoutTrashed()
 * @mixin \Eloquent
 */
class Factura extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'cliente_id', 
        'usuario_id',
        'factura_original_id',
        'subtotal', 
        'iva', 
        'total', 
        'estado', 
        'motivo_anulacion',
        'created_by', 
        'updated_by',
        // Campos SRI
        'numero_secuencial',
        'cua',
        'firma_digital',
        'mensaje_autorizacion',
        'fecha_emision',
        'hora_emision',
        'ambiente',
        'tipo_emision',
        'tipo_documento',
        'forma_pago',
        'contenido_qr',
        'imagen_qr',
        // Nuevos campos para estado de firma y emisión
        'estado_firma',
        'fecha_firma',
        'estado_emision',
        'fecha_emision_email'
    ];

    protected $dates = ['deleted_at', 'fecha_emision'];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'iva' => 'decimal:2',
        'total' => 'decimal:2',
        'fecha_emision' => 'date',
        'fecha_firma' => 'datetime',
        'fecha_emision_email' => 'datetime'
    ];

    public function cliente()
    {
        return $this->belongsTo(Cliente::class);
    }

    public function facturaOriginal()
    {
        return $this->belongsTo(Factura::class, 'factura_original_id');
    }

    public function facturasModificadas()
    {
        return $this->hasMany(Factura::class, 'factura_original_id');
    }

    public function creador()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function actualizador()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function detalles()
    {
        return $this->hasMany(FacturaDetalle::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    /**
     * Verificar si la factura está anulada
     */
    public function isAnulada()
    {
        return $this->estado === 'anulada';
    }

    /**
     * Verificar si la factura está activa
     */
    public function isActiva()
    {
        return $this->estado === 'activa';
    }

    /**
     * Generar datos SRI para la factura
     */
    public function generarDatosSRI()
    {
        $service = new FacturaSRIService();
        $datosSRI = $service->prepararDatosSRI((float) $this->subtotal);
        
        $this->fill($datosSRI);
        $this->save();
        
        return $this;
    }

    /**
     * Generar firma digital y QR para la factura
     */
    public function generarFirmaYQR()
    {
        $service = new FacturaSRIService();
        $datosFirmaQR = $service->generarFirmaYQR($this);
        $this->fill($datosFirmaQR);
        $this->save();
        return $this;
    }

    /**
     * Generar contenido QR para la factura
     */
    public function generarQR()
    {
        $service = new FacturaSRIService();
        $this->contenido_qr = $service->generarQRParaFactura($this);
        $this->save();
        
        return $this->contenido_qr;
    }

    /**
     * Verificar integridad de la firma digital
     */
    public function verificarFirmaDigital()
    {
        $service = new FacturaSRIService();
        return $service->verificarFirmaDigital($this, $this->firma_digital);
    }

    /**
     * Obtener datos del emisor
     */
    public function getDatosEmisor()
    {
        $service = new FacturaSRIService();
        return $service->getDatosEmisor();
    }

    /**
     * Formatear número secuencial para mostrar
     */
    public function getNumeroFormateado()
    {
        return $this->numero_secuencial ?? 'Pendiente';
    }

    /**
     * Formatear CUA para mostrar
     */
    public function getCUAFormateado()
    {
        return $this->cua ?? 'Pendiente';
    }

    /**
     * Verificar si la factura tiene datos SRI completos
     */
    public function tieneDatosSRI()
    {
        return !empty($this->numero_secuencial) && 
               !empty($this->cua) && 
               !empty($this->firma_digital);
    }

    /**
     * Obtener el estado de autorización SRI
     */
    public function getEstadoAutorizacion()
    {
        return $this->mensaje_autorizacion ?? 'PENDIENTE';
    }

    /**
     * Verificar si la factura está firmada
     */
    public function isFirmada()
    {
        return $this->estado_firma === 'FIRMADA';
    }

    /**
     * Verificar si la factura está emitida
     */
    public function isEmitida()
    {
        return $this->estado_emision === 'EMITIDA';
    }

    /**
     * Verificar si la factura está pendiente de firma
     */
    public function isPendienteFirma()
    {
        return $this->estado_firma === 'PENDIENTE' || empty($this->estado_firma);
    }

    /**
     * Verificar si la factura está pendiente de emisión
     */
    public function isPendienteEmision()
    {
        return $this->estado_emision === 'PENDIENTE' || empty($this->estado_emision);
    }

    /**
     * Firmar la factura digitalmente
     */
    public function firmarDigitalmente()
    {
        $service = new FacturaSRIService();
        $datosFirmaQR = $service->generarFirmaYQR($this);
        
        $this->fill($datosFirmaQR);
        $this->estado_firma = 'FIRMADA';
        $this->fecha_firma = now();
        $this->save();
        
        return $this;
    }

    /**
     * Emitir la factura (enviar por email)
     */
    public function emitir()
    {
        $this->estado_emision = 'EMITIDA';
        $this->fecha_emision_email = now();
        $this->save();
        
        // Enviar email al cliente
        try {
            $emailService = new \App\Services\EmailService();
            $clienteEmail = $this->cliente->email ?? null;
            
            if ($clienteEmail) {
                $asunto = 'Factura #' . $this->getNumeroFormateado() . ' - SowarTech';
                $mensaje = "Adjunto la factura #{$this->getNumeroFormateado()} por un total de $" . number_format((float) $this->total, 2) . ".\n\nGracias por su compra.\n\nSaludos cordiales,\nEquipo de SowarTech";
                
                $enviado = $emailService->enviarFactura($this, $clienteEmail, $asunto, $mensaje);
                
                if (!$enviado) {
                    \Log::warning("No se pudo enviar email para factura #{$this->id} a {$clienteEmail}");
                }
            } else {
                \Log::warning("Cliente de factura #{$this->id} no tiene email configurado");
            }
        } catch (\Exception $e) {
            \Log::error("Error enviando email para factura #{$this->id}: " . $e->getMessage());
        }
        
        return $this;
    }

    /**
     * Obtener el estado visual de la factura
     */
    public function getEstadoVisual()
    {
        if ($this->isAnulada()) {
            return ['texto' => 'ANULADA', 'clase' => 'danger', 'icono' => 'fas fa-ban'];
        }
        
        if ($this->isEmitida()) {
            return ['texto' => 'EMITIDA', 'clase' => 'success', 'icono' => 'fas fa-check-circle'];
        }
        
        if ($this->isFirmada()) {
            return ['texto' => 'FIRMADA', 'clase' => 'warning', 'icono' => 'fas fa-signature'];
        }
        
        return ['texto' => 'PENDIENTE', 'clase' => 'info', 'icono' => 'fas fa-clock'];
    }

    /**
     * Scope para facturas con datos SRI
     */
    public function scopeConDatosSRI($query)
    {
        return $query->whereNotNull('numero_secuencial')
                    ->whereNotNull('cua')
                    ->whereNotNull('firma_digital');
    }

    /**
     * Scope para facturas autorizadas
     */
    public function scopeAutorizadas($query)
    {
        return $query->where('mensaje_autorizacion', 'AUTORIZADO');
    }
}
