<?php

namespace App\Console\Commands;

use App\Models\Todo;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;

class RedisBenchmark extends Command
{
    protected $signature = 'redis:benchmark {count=100000}';
    protected $description = 'Benchmark Redis with large dataset';

    public function handle()
    {
        $count = (int) $this->argument('count');

        $this->info("Benchmarking Redis with {$count} entries...");

        // flush
        Redis::flushall();
        $this->line('Flushed Redis.');

        // --- WRITE ---
        $start = microtime(true);
        $batchSize = 1000;
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            Redis::set("todo:{$i}", json_encode([
                'id' => $i,
                'title' => "Task {$i}",
                'description' => str_repeat('A', 50),
                'completed' => $i % 2 === 0,
                'created_at' => now()->toISOString(),
            ]));
            if ($i % $batchSize === 0) {
                $bar->advance($batchSize);
            }
        }
        $bar->finish();
        $writeTime = round(microtime(true) - $start, 4);
        $this->newLine();
        $this->info("Write: {$writeTime}s (" . round($count / $writeTime) . " ops/s)");

        // --- READ RANDOM 1000 ---
        $start = microtime(true);
        $sample = 1000;
        for ($i = 0; $i < $sample; $i++) {
            Redis::get("todo:" . rand(0, $count - 1));
        }
        $readSampleTime = round((microtime(true) - $start) / $sample * 1_000_000, 2);
        $this->info("Read (avg of {$sample} random): {$readSampleTime}µs per key");

        // --- SIZE ---
        $dbsize = Redis::dbsize();
        $this->info("Redis DB size: {$dbsize} keys");

        // --- MEMORY ---
        $info = Redis::info('memory');
        $usedMemory = $info['used_memory_human'] ?? 'N/A';
        $this->info("Memory used: {$usedMemory}");

        $this->newLine();
        $this->line('--- Cache benchmark (Eloquent JSON) ---');

        $this->line('Loading from DB...');
        $start = microtime(true);
        $todos = Todo::with('files')->latest()->get();
        $dbTime = round(microtime(true) - $start, 4);
        $this->info("DB load: {$dbTime}s");

        $this->line('Caching to Redis as JSON...');
        $start = microtime(true);
        Redis::set('todos.benchmark', json_encode($todos->map(fn ($todo) => [
            'id' => $todo->id,
            'title' => $todo->title,
            'completed' => $todo->completed,
        ])));
        $cacheWriteTime = round(microtime(true) - $start, 4);
        $this->info("Redis write: {$cacheWriteTime}s");

        $this->line('Loading from Redis...');
        $start = microtime(true);
        $cached = Redis::get('todos.benchmark');
        collect(json_decode($cached));
        $cacheReadTime = round(microtime(true) - $start, 4);
        $this->info("Redis read:  {$cacheReadTime}s");

        $ratio = $cacheReadTime > 0 ? round($dbTime / $cacheReadTime, 1) : 'N/A';
        $this->info("Speedup: {$ratio}x faster");
    }
}
