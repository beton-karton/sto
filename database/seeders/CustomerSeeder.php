<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        $faker = \Faker\Factory::create('ru_RU');

        // 50 физических лиц
        for ($i = 0; $i < 50; $i++) {
            $phone = '+7' . $faker->numerify('9#########');
            $email = $faker->userName . '@' . $faker->word . '.ru';

            // Меняем порядок на Фамилия Имя Отчество
            $name = $faker->lastName . ' ' . $faker->firstName . ' ' . $faker->middleName;

            Customer::create([
                'type' => 'person',
                'name' => $name,
                'phone' => $phone,
                'email' => $email,
            ]);
        }

        // 20 юридических лиц (без Faker->company)
        $companies = [
            'ООО "АвтоПлюс"', 'ООО "Гараж 77"', 'ООО "ТурбоСервис"', 'ООО "Дизель-Мастер"',
            'ООО "Кузовной Центр"', 'ООО "Шинная Империя"', 'ООО "Форсаж"', 'ООО "Автодок"',
            'ООО "Механика"', 'ООО "Пит-Стоп"', 'ООО "Кардан"', 'ООО "Экспресс-Авто"',
            'ООО "Гидравлика"', 'ООО "Электрик-Про"', 'ООО "Мотор-Ремонт"', 'ООО "Аккумулятор плюс"',
            'ООО "Развал-Схождение"', 'ООО "Тюнинг-Ателье"', 'ООО "Альфа-Сервис"', 'ООО "Городской Таксопарк"'
        ];

        foreach ($companies as $companyName) {
            $phone = '+7' . $faker->numerify('4#########');
            $email = 'info@' . $faker->word . '.ru';

            Customer::create([
                'type' => 'company',
                'name' => $companyName,
                'phone' => $phone,
                'email' => $email,
            ]);
        }
    }
}
