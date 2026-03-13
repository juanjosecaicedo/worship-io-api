<?php

namespace App\Console\Commands;

use App\Jobs\SendEventReminder;
use App\Models\Reminder;
use Illuminate\Console\Command;

class ProcessReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:process-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and send reminders for upcoming events';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $reminders = Reminder::pending()
            ->with('event.attendees.user')
            ->get()
            ->filter(fn($r) => $r->isDue());

        if ($reminders->isEmpty()) {
            $this->info('No hay recordatorios pendientes.');
            return;
        }

        $this->info("Procesando {$reminders->count()} recordatorio(s)...");

        foreach ($reminders as $reminder) {
            SendEventReminder::dispatch($reminder);
        }

        $this->info('Recordatorios despachados correctamente.');
    }
    //# Agregar al crontab del servidor
    //* * * * * cd /var/www/worship-io-api && php artisan schedule:run >> /dev/null 2>&1

}
