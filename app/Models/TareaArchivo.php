<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class TareaArchivo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tarea_id',
        'user_id',
        'nombre_original',
        'ruta_archivo',
        'extension',
        'mime_type',
        'tamanio',
        'descripcion',
        'estado',
        'visto',
        'revisado_equipo_por',
        'revisado_equipo_at',
        'revision_cliente_estado',
        'revisado_cliente_por',
        'revisado_cliente_at',
    ];

    protected $casts = [
        'tamanio' => 'integer',
        'revisado_equipo_at' => 'datetime',
        'revisado_cliente_at' => 'datetime',
    ];

    public function isOnDrive(): bool
    {
        return str_starts_with((string) $this->ruta_archivo, 'drive:');
    }

    public function driveFileId(): ?string
    {
        return $this->isOnDrive() ? substr($this->ruta_archivo, 6) : null;
    }

    public function driveWebUrl(): ?string
    {
        return $this->isOnDrive()
            ? 'https://drive.google.com/file/d/'.rawurlencode($this->driveFileId()).'/view'
            : null;
    }

    public function mediaUrl(bool $download = false, ?string $format = null): string
    {
        if (! $this->isOnDrive()) {
            return Storage::url($this->ruta_archivo);
        }

        return URL::temporarySignedRoute('tareas.archivos.media', now()->addDays(7), [
            'archivo' => $this->id,
            'download' => $download ? 1 : 0,
            'format' => $format,
        ]);
    }

    public function tarea()
    {
        return $this->belongsTo(Tarea::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function revisorEquipo()
    {
        return $this->belongsTo(User::class, 'revisado_equipo_por')->withTrashed();
    }

    public function revisorCliente()
    {
        return $this->belongsTo(User::class, 'revisado_cliente_por')->withTrashed();
    }

    public function comentarios()
    {
        return $this->morphMany(TareaComentario::class, 'comentable');
    }
}
