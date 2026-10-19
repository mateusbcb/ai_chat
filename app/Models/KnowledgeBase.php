<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KnowledgeBase extends Model
{
    use HasFactory;

    protected $table = 'knowledge_bases';

    protected $fillable = ['document_name', 'content_chunk', 'embedding_vector'];

    // Força o Laravel a converter a string do banco em array automaticamente
    protected $casts = [
        'embedding_vector' => 'array',
    ];
}
