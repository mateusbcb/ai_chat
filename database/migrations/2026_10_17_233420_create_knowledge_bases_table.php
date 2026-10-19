<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKnowledgeBasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('knowledge_bases', function (Blueprint $table) {
            $table->id();
            $table->string('document_name'); // Nome do arquivo fonte (ex: manual_cd.txt)
            $table->text('content_chunk');    // O fragmento/parágrafo do texto real
            
            // Armazena o vetor gerado. Como os vetores são arrays imensos de floats (ex: 768 posições),
            // guardamos no MySQL como um campo 'longText' e faremos o Laravel ler como array usando cast JSON.
            $table->longText('embedding_vector'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('knowledge_bases');
    }
}
