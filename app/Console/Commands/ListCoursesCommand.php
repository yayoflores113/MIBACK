<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;

class ListCoursesCommand extends Command
{
    protected $signature = 'courses:list';
    protected $description = 'Lista todos los cursos disponibles';

    public function handle()
    {
        $courses = Course::select('id', 'title', 'slug', 'is_free', 'difficulty')
            ->orderBy('id')
            ->get();

        if ($courses->isEmpty()) {
            $this->error('No hay cursos en la base de datos');
            return;
        }

        $this->info("Total de cursos: {$courses->count()}\n");

        $headers = ['ID', 'Título', 'Slug', 'Tipo', 'Dificultad'];
        $rows = $courses->map(function($course) {
            return [
                $course->id,
                $course->title,
                $course->slug,
                $course->is_free ? '🟢 Gratis' : '🟡 Premium',
                $course->difficulty ?? 'N/A'
            ];
        })->toArray();

        $this->table($headers, $rows);
    }
}