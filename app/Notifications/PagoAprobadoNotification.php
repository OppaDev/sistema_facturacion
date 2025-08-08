<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pago;

class PagoAprobadoNotification extends Notification implements ShouldQueue
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
            ->subject('✅ Pago Aprobado - Factura ' . $this->pago->factura->getNumeroFormateado())
            ->greeting('¡Hola ' . $notifiable->name . '!')
            ->line('Te confirmamos que tu pago ha sido **APROBADO** exitosamente.')
            ->line('**Detalles del pago:**')
            ->line('• **Factura:** ' . $this->pago->factura->getNumeroFormateado())
            ->line('• **Monto:** $' . number_format($this->pago->monto, 2))
            ->line('• **Tipo de pago:** ' . ucfirst($this->pago->tipo_pago))
            ->line('• **Fecha de aprobación:** ' . $this->pago->validated_at->format('d/m/Y H:i:s'))
            ->action('Ver mi Factura', url('/facturas/' . $this->pago->factura->id))
            ->line('Tu factura ahora está marcada como **PAGADA** en nuestro sistema.')
            ->line('¡Gracias por tu pago!')
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
            'estado' => 'aprobado',
            'mensaje' => 'Pago aprobado para factura ' . $this->pago->factura->getNumeroFormateado()
        ];
    }
}
