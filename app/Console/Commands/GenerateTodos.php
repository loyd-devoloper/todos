<?php

namespace App\Console\Commands;

use App\Models\Todo;
use Illuminate\Console\Command;

class GenerateTodos extends Command
{
    protected $signature = 'todos:generate {count=100000}';
    protected $description = 'Generate todo records';

    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Generating {$count} todos...");

        $batchSize = 1000;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i += $batchSize) {
            $batch = min($batchSize, $count - $i);
            $rows = [];

            for ($j = 0; $j < $batch; $j++) {
                $rows[] = [
                    'title' => "Task " . ($i + $j + 1),
                    'description' => str_repeat('A', 50),
                    'completed' => ($i + $j) % 2 === 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            Todo::insert($rows);
            $bar->advance($batch);
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done. Total todos: " . Todo::count());
    }
}
