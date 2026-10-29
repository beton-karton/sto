<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CarCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $jsonPath = database_path('seeders/data/cars-base.json');

        if (!File::exists($jsonPath)) {
            $this->command->error("Файл не найден: {$jsonPath}");
            return;
        }

        $json = File::get($jsonPath);
        $data = json_decode($json, true);

        if (!isset($data['data'])) {
            $this->command->error("Неверная структура JSON (нет ключа 'data').");
            return;
        }

        $this->command->info('Очищаем старые данные справочника...');

        // Отключаем проверку внешних ключей на время очистки
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('models')->truncate();
        DB::table('brands')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('Начинаем импорт марок и моделей...');

        $brands = [];
        $models = [];
        $now = now();

        foreach ($data['data'] as $brand) {
            // Игнорируем "Промо-авто" и подобный мусор, если нужно
            // if ($brand['id'] === 'PROMO_AUTO') continue;

            $brands[] = [
                'id' => $brand['id'],
                'name' => $brand['name'],
                'created_at' => $now,
                'updated_at' => $now,
            ];

            if (isset($brand['models']) && is_array($brand['models'])) {
                foreach ($brand['models'] as $model) {
                    $models[] = [
                        'id' => $model['id'],
                        'brand_id' => $model['mark_id'],
                        'name' => $model['name'],
                        'year_from' => $model['year_from'] ?? null,
                        'year_to' => $model['year_to'] ?? null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
            }
        }

        // Вставляем пачками по 1000 штук, чтобы не превысить лимиты MySQL
        foreach (array_chunk($brands, 1000) as $chunk) {
            DB::table('brands')->insert($chunk);
        }

        foreach (array_chunk($models, 1000) as $chunk) {
            DB::table('models')->insert($chunk);
        }

        $this->command->info('Импорт завершен!');
        $this->command->info('Марок: ' . count($brands));
        $this->command->info('Моделей: ' . count($models));
    }
}
