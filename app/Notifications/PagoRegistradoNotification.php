<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pago;

class PagoRegistradoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $pago;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pago $pago)
    {
        $this->pago = $pago;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('📋 Pago Registrado - Factura ' . $this->pago->factura->getNumeroFormateado())
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Hemos recibido tu pago y está siendo procesado.')
            ->line('**Detalles del pago registrado:**')
            ->line('• **Factura:** ' . $this->pago->factura->getNumeroFormateado())
            ->line('• **Monto:** $' . number_format($this->pago->monto, 2))
            ->line('• **Tipo de pago:** ' . ucfirst($this->pago->tipo_pago))
            ->line('• **Número de transacción:** ' . ($this->pago->numero_transaccion ?: 'No especificado'))
            ->line('• **Fecha de registro:** ' . $this->pago->created_at->format('d/m/Y H:i:s'))
            ->line('**Estado actual:** ⏳ **PENDIENTE** de validación')
            ->line('Nuestro equipo revisará tu pago y te notificaremos el resultado en las próximas horas.')
            ->action('Ver Estado del Pago', url('/facturas/' . $this->pago->factura->id))
            ->line('Si tienes alguna pregunta, no dudes en contactarnos.')
            ->salutation('Saludos cordiales, ' . config('app.name', 'SowarTech'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'pago_id' => $this->pago->id,
            'factura_id' => $this->pago->factura->id,
            'monto' => $this->pago->monto,
            'estado' => 'registrado',
            'mensaje' => 'Pago registrado para factura ' . $this->pago->factura->getNumeroFormateado()
        ];
    }
}
