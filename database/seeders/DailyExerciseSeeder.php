<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DailyExercise;
use App\Models\Course;

class DailyExerciseSeeder extends Seeder
{
    public function run(): void
    {
        // Asegúrate de tener cursos en la BD antes de ejecutar esto
        $courses = Course::all();

        if ($courses->isEmpty()) {
            $this->command->warn('No hay cursos en la base de datos. Crea cursos primero.');
            return;
        }

        $exercises = [
            [
                'course_id' => $courses->random()->id,
                'title' => 'Variables en JavaScript',
                'description' => '¿Cuál es la diferencia entre var, let y const?',
                'type' => 'theory',
                'content' => [
                    'question' => 'Explica brevemente la diferencia entre var, let y const en JavaScript',
                ],
                'solution' => 'var tiene scope de función, let y const tienen scope de bloque. const no puede ser reasignado.',
                'difficulty' => 2,
                'points' => 10,
                'is_active' => true,
            ],
            [
                'course_id' => $courses->random()->id,
                'title' => 'Función que suma dos números',
                'description' => 'Crea una función que sume dos números',
                'type' => 'code',
                'content' => [
                    'question' => 'Escribe una función llamada "suma" que reciba dos parámetros y retorne la suma',
                    'example' => 'suma(5, 3) debe retornar 8',
                ],
                'solution' => 'function suma(a, b) { return a + b; }',
                'difficulty' => 1,
                'points' => 5,
                'is_active' => true,
            ],
            [
                'course_id' => $courses->random()->id,
                'title' => 'Array Methods',
                'description' => '¿Qué método de array usarías para transformar elementos?',
                'type' => 'quiz',
                'content' => [
                    'question' => '¿Qué método de array se usa para transformar cada elemento y crear un nuevo array?',
                    'options' => ['filter', 'map', 'reduce', 'forEach']
                ],
                'solution' => 'map',
                'difficulty' => 2,
                'points' => 10,
                'is_active' => true,
            ],
            [
                'course_id' => $courses->random()->id,
                'title' => 'Promesas en JavaScript',
                'description' => 'Resuelve este problema usando Promises',
                'type' => 'problem',
                'content' => [
                    'question' => 'Crea una función que simule una petición asíncrona que se resuelve después de 2 segundos',
                ],
                'solution' => 'function asyncFunction() { return new Promise(resolve => setTimeout(() => resolve("done"), 2000)); }',
                'difficulty' => 3,
                'points' => 15,
                'is_active' => true,
            ],
        ];

        foreach ($exercises as $exercise) {
            DailyExercise::create($exercise);
        }

        $this->command->info('Ejercicios diarios creados exitosamente!');
    }
}