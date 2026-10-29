<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class PartBrandSeeder extends Seeder
{
    public function run(): void
    {
        $filePath = database_path('seeders/data/part_brands.csv');

        if (!File::exists($filePath)) {
            $this->command->error("Файл не найден: {$filePath}");
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $brands = [];
        $skipped = 0;

        foreach ($lines as $line) {
            // 1. Убираем лишние пробелы и приводим к верхнему регистру
            $name = strtoupper(trim($line));

            // 2. Проверяем, что в строке есть только латиница, цифры, пробелы и дефисы
            // Если есть кириллица или кривые символы — пропускаем
            if (!preg_match('/^[A-Z0-9\-\s]+$/', $name)) {
                $skipped++;
                continue;
            }

            // 3. Убираем двойные пробелы (если есть)
            $name = preg_replace('/\s+/', ' ', $name);

            // 4. Пропускаем слишком короткие строки (1-2 символа), они обычно мусорные
            if (strlen($name) < 3) {
                $skipped++;
                continue;
            }

            $brands[] = $name;
        }

        // 5. Убираем дубликаты (оставляем уникальные значения)
        $brands = array_unique($brands);

        // 6. Заливаем в базу (через транзакцию для скорости)
        DB::transaction(function () use ($brands) {
            foreach ($brands as $brandName) {
                // Используем insertOrIgnore, чтобы не падать, если бренд уже есть
                DB::table('global_part_brands')->insertOrIgnore([
                    'name' => $brandName,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        });

        $this->command->info('Импорт завершен!');
        $this->command->info('Добавлено уникальных брендов: ' . count($brands));
        $this->command->info('Пропущено мусорных строк: ' . $skipped);
    }
}
