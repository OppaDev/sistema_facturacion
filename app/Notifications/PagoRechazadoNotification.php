<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Pago;

class PagoRechazadoNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public $pago;
    public $motivo;

    /**
     * Create a new notification instance.
     */
    public function __construct(Pago $pago, $motivo = null)
    {
        $this->pago = $pago;
        $this->motivo = $motivo;
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
        $message = (new MailMessage)
            ->subject('❌ Pago Rechazado - Factura ' . $this->pago->factura->getNumeroFormateado())
            ->greeting('Hola ' . $notifiable->name)
            ->line('Lamentamos informarte que tu pago ha sido **RECHAZADO**.')
            ->line('**Detalles del pago:**')
            ->line('• **Factura:** ' . $this->pago->factura->getNumeroFormateado())
            ->line('• **Monto:** $' . number_format($this->pago->monto, 2))
            ->line('• **Tipo de pago:** ' . ucfirst($this->pago->tipo_pago))
            ->line('• **Fecha de rechazo:** ' . $this->pago->validated_at->format('d/m/Y H:i:s'));

        if ($this->motivo) {
            $message->line('**Motivo del rechazo:**')
                   ->line($this->motivo);
        }

        $message->line('**¿Qué puedes hacer ahora?**')
               ->line('• Revisar los datos de tu pago')
               ->line('• Intentar realizar el pago nuevamente')
               ->line('• Contactar con nuestro equipo si tienes dudas')
               ->action('Intentar Pagar Nuevamente', url('/facturas/' . $this->pago->factura->id))
               ->line('Tu factura sigue **PENDIENTE** de pago, por lo que puedes intentar pagarla nuevamente.')
               ->line('Si tienes alguna duda, no dudes en contactarnos.')
               ->salutation('Saludos cordiales, ' . config('app.name', 'SowarTech'));

        return $message;
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
            'estado' => 'rechazado',
            'motivo' => $this->motivo,
            'mensaje' => 'Pago rechazado para factura ' . $this->pago->factura->getNumeroFormateado()
        ];
    }
}
