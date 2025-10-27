<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\LearningPath;
use App\Models\Course;
use Illuminate\Support\Facades\DB;

class CompleteLearningPathSeeder extends Seeder
{
    public function run()
    {
        $this->command->info("🚀 Iniciando creación de cursos y asignación a rutas...\n");

        // Limpia la tabla pivot
        DB::table('learning_path_course')->truncate();

        // Crea y asigna cursos por ruta (sin necesidad de carrera)
        $this->seedFullStackCourses();
        $this->seedDataScienceCourses();
        $this->seedUXUICourses();

        $this->command->info("\n✅ ¡Proceso completado exitosamente!");
    }

    private function seedFullStackCourses()
    {
        $this->command->info("📚 === RUTA: DESARROLLO WEB FULL STACK ===");
        
        $path = LearningPath::where('slug', 'desarrollo-web-fullstack')->first();
        
        if (!$path) {
            $this->command->warn('⚠️  Ruta no encontrada');
            return;
        }

        $courses = [
            // Nivel Básico
            [
                'title' => 'HTML y CSS Desde Cero',
                'slug' => 'html-css-desde-cero',
                'provider' => 'Plataforma Interna',
                'topic' => 'Frontend Básico',
                'career_id' => null,
                'order' => 1,
            ],
            [
                'title' => 'JavaScript Fundamental',
                'slug' => 'javascript-fundamental',
                'provider' => 'Plataforma Interna',
                'topic' => 'Programación Web',
                'career_id' => null,
                'order' => 2,
            ],
            
            // Nivel Intermedio
            [
                'title' => 'React.js Moderno',
                'slug' => 'react-moderno',
                'provider' => 'Plataforma Interna',
                'topic' => 'Frontend Avanzado',
                'career_id' => null,
                'order' => 3,
            ],
            [
                'title' => 'Node.js y Express Backend',
                'slug' => 'nodejs-express-backend',
                'provider' => 'Plataforma Interna',
                'topic' => 'Backend',
                'career_id' => null,
                'order' => 4,
            ],
            [
                'title' => 'Bases de Datos SQL y NoSQL',
                'slug' => 'bases-datos-sql-nosql',
                'provider' => 'Plataforma Interna',
                'topic' => 'Bases de Datos',
                'career_id' => null,
                'order' => 5,
            ],
            
            // Nivel Avanzado
            [
                'title' => 'APIs RESTful Profesionales',
                'slug' => 'apis-restful-profesionales',
                'provider' => 'Plataforma Interna',
                'topic' => 'Backend Avanzado',
                'career_id' => null,
                'order' => 6,
            ],
            [
                'title' => 'Deployment y DevOps',
                'slug' => 'deployment-devops',
                'provider' => 'Plataforma Interna',
                'topic' => 'DevOps',
                'career_id' => null,
                'order' => 7,
            ],
        ];

        $this->createAndAttachCourses($path, $courses, 'Desarrollo Web Full Stack');
    }

    private function seedDataScienceCourses()
    {
        $this->command->info("\n📊 === RUTA: CIENCIA DE DATOS CON PYTHON ===");
        
        $path = LearningPath::where('slug', 'ciencia-datos-python')->first();
        
        if (!$path) {
            $this->command->warn('⚠️  Ruta no encontrada');
            return;
        }

        $courses = [
            [
                'title' => 'Python para Principiantes',
                'slug' => 'python-principiantes',
                'provider' => 'Plataforma Interna',
                'topic' => 'Programación Python',
                'career_id' => null,
                'order' => 1,
            ],
            [
                'title' => 'Matemáticas para Data Science',
                'slug' => 'matematicas-data-science',
                'provider' => 'Plataforma Interna',
                'topic' => 'Fundamentos Matemáticos',
                'career_id' => null,
                'order' => 2,
            ],
            [
                'title' => 'Pandas y NumPy Avanzado',
                'slug' => 'pandas-numpy-avanzado',
                'provider' => 'Plataforma Interna',
                'topic' => 'Análisis de Datos',
                'career_id' => null,
                'order' => 3,
            ],
            [
                'title' => 'Visualización de Datos',
                'slug' => 'visualizacion-datos-python',
                'provider' => 'Plataforma Interna',
                'topic' => 'Data Visualization',
                'career_id' => null,
                'order' => 4,
            ],
            [
                'title' => 'Machine Learning con Scikit-learn',
                'slug' => 'machine-learning-scikit',
                'provider' => 'Plataforma Interna',
                'topic' => 'Machine Learning',
                'career_id' => null,
                'order' => 5,
            ],
            [
                'title' => 'Deep Learning con TensorFlow',
                'slug' => 'deep-learning-tensorflow',
                'provider' => 'Plataforma Interna',
                'topic' => 'Inteligencia Artificial',
                'career_id' => null,
                'order' => 6,
            ],
        ];

        $this->createAndAttachCourses($path, $courses, 'Ciencia de Datos con Python');
    }

    private function seedUXUICourses()
    {
        $this->command->info("\n🎨 === RUTA: DISEÑO UX/UI ===");
        
        $path = LearningPath::where('slug', 'diseno-ux-ui')->first();
        
        if (!$path) {
            $this->command->warn('⚠️  Ruta no encontrada');
            return;
        }

        $courses = [
            [
                'title' => 'Fundamentos de Diseño UI',
                'slug' => 'fundamentos-diseno-ui',
                'provider' => 'Plataforma Interna',
                'topic' => 'Diseño de Interfaces',
                'career_id' => null,
                'order' => 1,
            ],
            [
                'title' => 'Teoría del Color y Tipografía',
                'slug' => 'color-tipografia',
                'provider' => 'Plataforma Interna',
                'topic' => 'Diseño Visual',
                'career_id' => null,
                'order' => 2,
            ],
            [
                'title' => 'Figma de Cero a Experto',
                'slug' => 'figma-cero-experto',
                'provider' => 'Plataforma Interna',
                'topic' => 'Herramientas de Diseño',
                'career_id' => null,
                'order' => 3,
            ],
            [
                'title' => 'Prototipado y Animaciones',
                'slug' => 'prototipado-animaciones',
                'provider' => 'Plataforma Interna',
                'topic' => 'Prototipado',
                'career_id' => null,
                'order' => 4,
            ],
            [
                'title' => 'Design Systems Profesionales',
                'slug' => 'design-systems-pro',
                'provider' => 'Plataforma Interna',
                'topic' => 'Design Systems',
                'career_id' => null,
                'order' => 5,
            ],
            [
                'title' => 'UX Research y Testing',
                'slug' => 'ux-research-testing',
                'provider' => 'Plataforma Interna',
                'topic' => 'Investigación UX',
                'career_id' => null,
                'order' => 6,
            ],
        ];

        $this->createAndAttachCourses($path, $courses, 'Diseño UX/UI');
    }

    private function createAndAttachCourses(LearningPath $path, array $coursesData, string $pathName)
    {
        $coursesToAttach = [];
        $created = 0;
        $existing = 0;

        foreach ($coursesData as $data) {
            $order = $data['order'];
            unset($data['order']);

            // Busca o crea el curso
            $course = Course::where('slug', $data['slug'])->first();
            
            if (!$course) {
                $course = Course::create($data);
                $created++;
                $this->command->info("  ✨ Creado: {$course->title}");
            } else {
                $existing++;
                $this->command->comment("  ✓ Ya existe: {$course->title}");
            }

            $coursesToAttach[$course->id] = [
                'order' => $order,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Asigna cursos a la ruta
        $path->courses()->sync($coursesToAttach);

        $this->command->info("\n  📌 Resumen de {$pathName}:");
        $this->command->info("     • Cursos creados: {$created}");
        $this->command->info("     • Cursos existentes: {$existing}");
        $this->command->info("     • Total en la ruta: " . count($coursesToAttach));
    }
}