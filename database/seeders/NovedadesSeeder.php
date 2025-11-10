<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Novedad;
use App\Models\User;
use Carbon\Carbon;

class NovedadesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer usuario disponible
        $user = User::first();

        if (!$user) {
            $this->command->error('No hay usuarios en la base de datos. Por favor, crea al menos un usuario primero.');
            return;
        }

        $novedades = [
            [
                'titulo' => 'Bienvenidos a Ficha Digital',
                'contenido' => 'Nos complace presentar la nueva plataforma Ficha Digital de Pieve Salud. Esta herramienta está diseñada para optimizar la gestión de información y mejorar la comunicación interna. A través de este sistema, podrán acceder a información actualizada, gestionar documentación y mantenerse al tanto de las últimas novedades de la organización.',
                'activa' => true,
                'fecha_publicacion' => Carbon::now()->subDays(10),
            ],
            [
                'titulo' => 'Nueva funcionalidad de Novedades',
                'contenido' => 'Hemos implementado el módulo de Novedades que les permitirá estar siempre informados sobre actualizaciones, cambios y anuncios importantes de la plataforma. Las novedades se mostrarán en el dashboard principal y podrán acceder al historial completo desde el menú lateral.',
                'activa' => true,
                'fecha_publicacion' => Carbon::now()->subDays(7),
            ],
            [
                'titulo' => 'Integración con Active Directory',
                'contenido' => 'La plataforma cuenta con integración completa con Active Directory, lo que facilita el acceso mediante las credenciales corporativas existentes. No es necesario crear nuevas contraseñas, simplemente utilicen sus credenciales habituales de red para acceder al sistema.',
                'activa' => true,
                'fecha_publicacion' => Carbon::now()->subDays(5),
            ],
            [
                'titulo' => 'Mantenimiento programado',
                'contenido' => 'Les informamos que se realizará un mantenimiento programado del sistema el próximo domingo de 02:00 a 04:00 AM. Durante este periodo, la plataforma no estará disponible. Agradecemos su comprensión y les recomendamos planificar sus actividades en consecuencia.',
                'activa' => true,
                'fecha_publicacion' => Carbon::now()->subDays(2),
            ],
            [
                'titulo' => 'Próximas funcionalidades',
                'contenido' => 'Estamos trabajando en nuevas funcionalidades que estarán disponibles próximamente. Entre ellas se incluyen: sistema de gestión de fichas médicas, reportes personalizados, módulo de seguimiento de pacientes, y herramientas de análisis de datos. Manténganse atentos a las próximas actualizaciones.',
                'activa' => true,
                'fecha_publicacion' => Carbon::now()->subHours(12),
            ],
        ];

        foreach ($novedades as $novedadData) {
            Novedad::create([
                'titulo' => $novedadData['titulo'],
                'contenido' => $novedadData['contenido'],
                'user_id' => $user->id,
                'activa' => $novedadData['activa'],
                'fecha_publicacion' => $novedadData['fecha_publicacion'],
            ]);
        }

        $this->command->info('Se han creado 5 novedades de prueba exitosamente.');
    }
}
