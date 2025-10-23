<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImportDocuments extends Command
{
    protected $signature = 'documents:import';
    protected $description = 'Импорт существующих PDF из storage/app/public/docs в базу данных';

    public function handle()
    {
        $files = Storage::disk('public')->files('docs');
        $count = 0;

        foreach ($files as $file) {
            $name = pathinfo($file, PATHINFO_FILENAME);

            if (!Document::where('path', $file)->exists()) {
                Document::create([
                    'id'   => (string) Str::uuid(),
                    'name' => $name,
                    'path' => $file,
                ]);
                $count++;
            }
        }

        $this->info("Импортировано {$count} документов.");
    }
}
