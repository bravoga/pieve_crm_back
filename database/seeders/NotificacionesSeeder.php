<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notificacion;
use App\Models\User;
use Carbon\Carbon;

class NotificacionesSeeder extends Seeder
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

        $notificaciones = [
            [
                'titulo' => 'Bienvenido a Ficha Digital',
                'mensaje' => 'Has iniciado sesión correctamente en el sistema',
                'tipo' => 'sistema',
                'icono' => 'check_circle',
                'color' => 'positive',
                'leida' => false,
                'url' => '/dashboard',
                'created_at' => Carbon::now()->subMinutes(5),
            ],
            [
                'titulo' => 'Nueva novedad publicada',
                'mensaje' => 'Se ha publicado una nueva novedad: "Próximas funcionalidades"',
                'tipo' => 'novedad',
                'icono' => 'campaign',
                'color' => 'info',
                'leida' => false,
                'url' => '/novedades',
                'created_at' => Carbon::now()->subHours(2),
            ],
            [
                'titulo' => 'Actualización del sistema',
                'mensaje' => 'Se ha actualizado el sistema a la versión 2.0',
                'tipo' => 'info',
                'icono' => 'system_update',
                'color' => 'info',
                'leida' => true,
                'fecha_leida' => Carbon::now()->subHours(1),
                'url' => null,
                'created_at' => Carbon::now()->subHours(3),
            ],
            [
                'titulo' => 'Recordatorio importante',
                'mensaje' => 'Tienes tareas pendientes por completar',
                'tipo' => 'alerta',
                'icono' => 'event',
                'color' => 'warning',
                'leida' => true,
                'fecha_leida' => Carbon::now()->subDays(1),
                'url' => null,
                'created_at' => Carbon::now()->subDay(),
            ],
            [
                'titulo' => 'Mantenimiento programado',
                'mensaje' => 'El sistema tendrá mantenimiento el próximo domingo de 02:00 a 04:00 AM',
                'tipo' => 'alerta',
                'icono' => 'build',
                'color' => 'warning',
                'leida' => false,
                'url' => null,
                'created_at' => Carbon::now()->subDays(2),
            ],
        ];

        foreach ($notificaciones as $notificacionData) {
            $notificacion = Notificacion::create([
                'titulo' => $notificacionData['titulo'],
                'mensaje' => $notificacionData['mensaje'],
                'tipo' => $notificacionData['tipo'],
                'icono' => $notificacionData['icono'],
                'color' => $notificacionData['color'],
                'url' => $notificacionData['url'],
                'created_at' => $notificacionData['created_at'],
                'updated_at' => $notificacionData['created_at'],
            ]);

            // Asociar la notificación con el usuario usando la tabla pivot
            $notificacion->usuarios()->attach($user->id, [
                'leida' => $notificacionData['leida'],
                'fecha_leida' => $notificacionData['fecha_leida'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Se han creado 5 notificaciones de prueba exitosamente.');
    }
}
