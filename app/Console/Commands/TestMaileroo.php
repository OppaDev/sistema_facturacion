<?php

namespace App\Console\Commands;

use App\Services\MailerooService;
use Illuminate\Console\Command;

class TestMaileroo extends Command
{
    protected $signature = 'maileroo:test {email?} {--connection}';
    protected $description = 'Test Maileroo email service';

    public function handle()
    {
        $emailTo = $this->argument('email');
        $testConnection = $this->option('connection');

        $mailerooService = new MailerooService();

        if ($testConnection) {
            $this->info('Testing Maileroo connection...');
            
            $result = $mailerooService->testConnection();
            if ($result['success'] ?? false) {
                $this->info('✅ Connection test successful!');
                
                // Show stats if available
                $stats = $mailerooService->getStats();
                if ($stats && ($stats['success'] ?? false)) {
                    $this->info('📊 Account Stats:');
                    $data = $stats['data'] ?? [];
                    $this->line('Emails sent today: ' . ($data['emails_sent_today'] ?? 'N/A'));
                    $this->line('Credit balance: ' . ($data['credit_balance'] ?? 'N/A'));
                }
            } else {
                $this->error('❌ Connection test failed!');
                $this->error('Error: ' . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
        } else {
            if (!$emailTo) {
                $this->error('Email address is required when not testing connection');
                $this->info('Usage: php artisan maileroo:test email@example.com');
                $this->info('       php artisan maileroo:test --connection');
                return 1;
            }

            $this->info("Sending test email to: {$emailTo}");

            $result = $mailerooService->sendEmail(
                $emailTo,
                'Test Email from Sistema de Facturación',
                'Hola! Este es un email de prueba enviado desde nuestro Sistema de Facturación usando Maileroo.',
                '<h1>¡Hola!</h1><p>Este es un email de prueba enviado desde nuestro <strong>Sistema de Facturación</strong> usando Maileroo.</p><p>Si recibes este mensaje, la integración está funcionando correctamente. ✅</p>'
            );

            if ($result['success'] ?? false) {
                $this->info('✅ Test email sent successfully!');
                $this->info('📧 Email sent to: ' . $emailTo);
            } else {
                $this->error('❌ Failed to send test email');
                $this->error('Error: ' . ($result['message'] ?? 'Unknown error'));
                return 1;
            }
        }

        return 0;
    }
}
