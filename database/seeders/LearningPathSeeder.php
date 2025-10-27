<?php

namespace Database\Seeders;

use App\Models\LearningPath;
use App\Models\Course;
use Illuminate\Database\Seeder;

class LearningPathSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear rutas de aprendizaje de ejemplo
        $paths = [
            [
                'title' => 'Desarrollo Web Full Stack',
                'slug' => 'desarrollo-web-fullstack',
                'description' => 'Aprende a crear aplicaciones web completas desde cero, dominando tanto el frontend como el backend.',
                'image' => 'https://via.placeholder.com/1200x400?text=Full+Stack+Developer',
                'duration' => '6 meses',
                'level' => 'Intermedio',
                'objectives' => [
                    'Dominar HTML, CSS y JavaScript moderno',
                    'Construir aplicaciones con React y Node.js',
                    'Trabajar con bases de datos SQL y NoSQL',
                    'Implementar autenticación y autorización',
                    'Desplegar aplicaciones en producción'
                ],
                'requirements' => [
                    'Conocimientos básicos de programación',
                    'Computadora con conexión a internet',
                    'Disposición para practicar diariamente'
                ],
                'is_active' => true,
                'order' => 1
            ],
            [
                'title' => 'Ciencia de Datos con Python',
                'slug' => 'ciencia-datos-python',
                'description' => 'Conviértete en científico de datos dominando Python, análisis estadístico y machine learning.',
                'image' => 'https://via.placeholder.com/1200x400?text=Data+Science',
                'duration' => '4 meses',
                'level' => 'Avanzado',
                'objectives' => [
                    'Analizar datos con Pandas y NumPy',
                    'Crear visualizaciones con Matplotlib y Seaborn',
                    'Implementar modelos de Machine Learning',
                    'Trabajar con big data'
                ],
                'requirements' => [
                    'Conocimientos de Python básico',
                    'Fundamentos de matemáticas y estadística'
                ],
                'is_active' => true,
                'order' => 2
            ],
            [
                'title' => 'Diseño UX/UI Profesional',
                'slug' => 'diseno-ux-ui',
                'description' => 'Aprende a diseñar experiencias de usuario excepcionales con las mejores prácticas de la industria.',
                'image' => 'https://via.placeholder.com/1200x400?text=UX+UI+Design',
                'duration' => '3 meses',
                'level' => 'Principiante',
                'objectives' => [
                    'Entender los principios de diseño UX/UI',
                    'Dominar Figma y herramientas de prototipado',
                    'Realizar research de usuarios',
                    'Crear sistemas de diseño escalables'
                ],
                'requirements' => [
                    'Ninguno, comenzamos desde cero',
                    'Computadora con Figma instalado'
                ],
                'is_active' => true,
                'order' => 3
            ]
        ];

        foreach ($paths as $pathData) {
            $path = LearningPath::create($pathData);

            // Asociar cursos existentes (ajusta los IDs según tus cursos)
            // Ejemplo: si tienes cursos con IDs 1, 2, 3, 4
            if ($path->slug === 'desarrollo-web-fullstack') {
                // Obtener algunos cursos de ejemplo (ajusta según tus datos)
                $courses = Course::take(4)->get();
                foreach ($courses as $index => $course) {
                    $path->courses()->attach($course->id, ['order' => $index + 1]);
                }
            }
        }
    }
}