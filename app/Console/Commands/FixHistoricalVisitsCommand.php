<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\UserGpsTrace;
use Illuminate\Support\Facades\Storage;

class FixHistoricalVisitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gps:fix-historical-visits {--undo : Undo the most recent historical fix}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fixes historical GPS traces that span multiple days by splitting them into separate visits. Backup is generated automatically. Use --undo to revert.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $backupFile = 'historical_visits_backup.json';

        if ($this->option('undo')) {
            if (!Storage::disk('local')->exists($backupFile)) {
                $this->error('No backup file found at storage/app/' . $backupFile);
                return 1;
            }

            $this->info('Restoring from backup...');
            $backupContent = json_decode(Storage::disk('local')->get($backupFile), true);

            if (!is_array($backupContent)) {
                $this->error('Backup file is invalid or corrupted.');
                return 1;
            }

            $restoredCount = 0;
            DB::transaction(function () use ($backupContent, &$restoredCount) {
                foreach ($backupContent as $record) {
                    UserGpsTrace::where('id', $record['id'])->update([
                        'visit_id' => $record['original_visit_id'],
                        'status' => $record['original_status']
                    ]);
                    $restoredCount++;
                }
            });

            $this->info("Successfully restored {$restoredCount} traces to their original state.");
            Storage::disk('local')->delete($backupFile);
            
            return 0;
        }

        if (Storage::disk('local')->exists($backupFile)) {
            $this->warn('A backup file already exists (from a previous fix).');
            $this->warn('If you want to run the fix again, please run `php artisan gps:fix-historical-visits --undo` first, or manually delete storage/app/historical_visits_backup.json');
            return 1;
        }

        $this->info('Finding historical visits that span multiple days...');

        $problematicVisits = DB::table('user_gps_traces')
            ->select('visit_id')
            ->whereNotNull('visit_id')
            ->groupBy('visit_id')
            ->havingRaw('COUNT(DISTINCT DATE(created_at)) > 1')
            ->pluck('visit_id');

        $backupData = [];
        $fixedCount = 0;

        DB::transaction(function () use ($problematicVisits, &$backupData, &$fixedCount) {
            foreach ($problematicVisits as $visitId) {
                $dates = DB::table('user_gps_traces')
                    ->selectRaw('DATE(created_at) as visit_date')
                    ->where('visit_id', $visitId)
                    ->groupBy('visit_date')
                    ->orderBy('visit_date')
                    ->pluck('visit_date');

                // The first day keeps the original visit_id.
                $dates->shift();

                foreach ($dates as $date) {
                    // Generate a new global visit ID
                    $newVisitId = UserGpsTrace::max('visit_id') + 1;

                    // Get the records before we update them, to save in the backup
                    $tracesToUpdate = UserGpsTrace::where('visit_id', $visitId)
                        ->whereDate('created_at', $date)
                        ->get();

                    foreach ($tracesToUpdate as $trace) {
                        $backupData[] = [
                            'id' => $trace->id,
                            'original_visit_id' => $trace->visit_id,
                            'original_status' => $trace->status,
                        ];

                        $trace->update([
                            'visit_id' => $newVisitId,
                            'status' => 'inactive'
                        ]);
                        $fixedCount++;
                    }
                }
            }

            // Also find active traces older than today and deactivate them to stop bleeding
            $oldActiveTraces = UserGpsTrace::where('status', 'active')
                ->whereDate('created_at', '<', now()->toDateString())
                ->get();

            foreach ($oldActiveTraces as $trace) {
                // Ignore if we already backed this up in the dates loop
                if (!collect($backupData)->contains('id', $trace->id)) {
                    $backupData[] = [
                        'id' => $trace->id,
                        'original_visit_id' => $trace->visit_id,
                        'original_status' => $trace->status,
                    ];
                    $fixedCount++;
                }

                $trace->update([
                    'status' => 'inactive'
                ]);
            }
        });

        if (count($backupData) > 0) {
            Storage::disk('local')->put($backupFile, json_encode($backupData));
            $this->info("Created a backup at storage/app/{$backupFile}.");
            $this->info("Successfully fixed {$fixedCount} historical traces.");
        } else {
            $this->info("No historical traces needed fixing.");
        }

        return 0;
    }
}
