<?php

namespace App\Models;

use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Database\Eloquent\Model;

class EventRecurrence extends Model
{
    protected $fillable = [
        'event_id',
        'frequency',
        'interval',
        'days_of_week',
        'day_of_month',
        'starts_at',
        'ends_at',
        'occurrences_limit',
    ];

    protected $casts = [
        'days_of_week'      => 'array',
        'starts_at'         => 'date',
        'ends_at'           => 'date',
        'interval'          => 'integer',
        'occurrences_limit' => 'integer',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function exceptions()
    {
        return $this->hasMany(EventRecurrenceException::class);
    }

    // ─── Helpers ──────────────────────────────────────────

    // Genera todas las fechas de ocurrencia entre dos fechas
    public function generateOccurrences(Carbon $from, Carbon $to): array
    {
        $occurrences    = [];
        $count          = 0;
        $limit          = $this->occurrences_limit;
        $rangeEnd       = $this->ends_at
            ? $to->min($this->ends_at->endOfDay())
            : $to;

        // Fechas de excepciones canceladas (no se incluyen)
        $cancelledDates = $this->exceptions()
            ->where('type', 'cancelled')
            ->pluck('original_date')
            ->map(fn($d) => Carbon::parse($d)->toDateString())
            ->toArray();

        // Fechas de excepciones modificadas (se reemplazan)
        $modifiedDates  = $this->exceptions()
            ->where('type', 'modified')
            ->with('event')
            ->get()
            ->keyBy(fn($e) => Carbon::parse($e->original_date)->toDateString());

        match ($this->frequency) {
            'daily'   => $this->generateDaily($from, $rangeEnd, $occurrences, $count, $limit, $cancelledDates, $modifiedDates),
            'weekly'  => $this->generateWeekly($from, $rangeEnd, $occurrences, $count, $limit, $cancelledDates, $modifiedDates),
            'monthly' => $this->generateMonthly($from, $rangeEnd, $occurrences, $count, $limit, $cancelledDates, $modifiedDates),
        };

        return $occurrences;
    }

    private function generateWeekly(
        Carbon $from,
        Carbon $to,
        array &$occurrences,
        int &$count,
        ?int $limit,
        array $cancelledDates,
        $modifiedDates
    ): void {
        $daysOfWeek = $this->days_of_week ?? [];
        $current    = $this->starts_at->copy()->max($from);

        while ($current->lte($to)) {
            if ($limit && $count >= $limit) break;

            // Verificar si el día de la semana está en los días configurados
            // Carbon: 0=Lunes...6=Domingo, pero nosotros usamos 0=Dom...6=Sáb
            $dayIndex = $current->dayOfWeek; // 0=Dom en Carbon

            if (in_array($dayIndex, $daysOfWeek)) {
                $dateStr = $current->toDateString();

                if (! in_array($dateStr, $cancelledDates)) {
                    if (isset($modifiedDates[$dateStr])) {
                        // Ocurrencia modificada: usar el evento real
                        $occurrences[] = $this->buildOccurrence(
                            $current,
                            'modified',
                            $modifiedDates[$dateStr]->event
                        );
                    } else {
                        // Ocurrencia virtual
                        $occurrences[] = $this->buildOccurrence($current, 'virtual');
                    }
                    $count++;
                }
            }

            $current->addDay();
        }
    }

    private function generateDaily(
        Carbon $from,
        Carbon $to,
        array &$occurrences,
        int &$count,
        ?int $limit,
        array $cancelledDates,
        $modifiedDates
    ): void {
        $current = $this->starts_at->copy()->max($from);

        while ($current->lte($to)) {
            if ($limit && $count >= $limit) break;

            $dateStr = $current->toDateString();

            if (! in_array($dateStr, $cancelledDates)) {
                $occurrences[] = isset($modifiedDates[$dateStr])
                    ? $this->buildOccurrence($current, 'modified', $modifiedDates[$dateStr]->event)
                    : $this->buildOccurrence($current, 'virtual');
                $count++;
            }

            $current->addDays($this->interval);
        }
    }

    private function generateMonthly(
        Carbon $from,
        Carbon $to,
        array &$occurrences,
        int &$count,
        ?int $limit,
        array $cancelledDates,
        $modifiedDates
    ): void {
        $current = $this->starts_at->copy()->max($from)->startOfMonth();

        while ($current->lte($to)) {
            if ($limit && $count >= $limit) break;

            $occurrence = $current->copy()->setDay($this->day_of_month ?? 1);

            if ($occurrence->between($from, $to)) {
                $dateStr = $occurrence->toDateString();

                if (! in_array($dateStr, $cancelledDates)) {
                    $occurrences[] = isset($modifiedDates[$dateStr])
                        ? $this->buildOccurrence($occurrence, 'modified', $modifiedDates[$dateStr]->event)
                        : $this->buildOccurrence($occurrence, 'virtual');
                    $count++;
                }
            }

            $current->addMonthsNoOverflow($this->interval);
        }
    }

    // Construye la estructura de una ocurrencia
    private function buildOccurrence(Carbon $date, string $status, ?Event $event = null): array
    {
        $templateEvent = $this->event;
        $startTime     = Carbon::parse($templateEvent->start_datetime)->format('H:i:s');
        $endTime       = Carbon::parse($templateEvent->end_datetime)->format('H:i:s');

        return [
            'recurrence_id'  => $this->id,
            'original_date'  => $date->toDateString(),
            'status'         => $status,   // virtual | modified | materialized
            'event'          => $event ?? $templateEvent,
            'start_datetime' => $date->toDateString() . ' ' . $startTime,
            'end_datetime'   => $date->toDateString() . ' ' . $endTime,
        ];
    }
}
