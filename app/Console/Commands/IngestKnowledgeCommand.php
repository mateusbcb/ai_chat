<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CrmRagService;

class IngestKnowledgeCommand extends Command
{
    /**
     * O nome e a assinatura do comando no terminal.
     * Uso: php artisan wms:ingest {caminho_do_arquivo}
     */
    protected $signature = 'wms:ingest {file : O caminho completo ou relativo do arquivo a ser indexado}';

    /**
     * A descrição do comando que aparece na listagem do artisan.
     */
    protected $description = 'Lê, fragmenta e gera vetores de embeddings (RAG) para arquivos TXT, MD e expansões logísticas';

    /**
     * Executa a lógica do comando no terminal.
     */
    public function handle(CrmRagService $ragService)
    {
        $filePath = $this->argument('file');

        // 1. Valida se o arquivo de fato físico existe na máquina
        if (!file_exists($filePath)) {
            $this->error("Erro: O arquivo especificado no caminho '{$filePath}' não foi localizado.");
            return Command::FAILURE;
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $documentName = pathinfo($filePath, PATHINFO_BASENAME);
        $rawText = "";

        $this->info("Analisando o documento '{$documentName}'...");

        // 2. ESTRUTURA DE PLUGINS: Identifica o driver correto com base na extensão
        switch ($extension) {
            case 'txt':
            case 'md':
                // Leitura nativa ultra-rápida de texto puro e markdown
                $rawText = file_get_contents($filePath);
                break;

            case 'pdf':
                $this->warn("Aviso: Suporte a PDF detectado.");
                $this->line("Para ativar a leitura real de PDFs, instale o pacote via terminal: composer require smalot/pdfparser");
                // Exemplo de como você plugará no futuro:
                // $parser = new \Smalot\PdfParser\Parser();
                // $rawText = $parser->parseFile($filePath)->getText();
                return Command::INVALID;

            case 'doc':
            case 'docx':
                $this->warn("Aviso: Suporte a arquivos Word detectado.");
                $this->line("Para ativar a leitura real de DOCX, instale o pacote via terminal: composer require phpoffice/phpword");
                return Command::INVALID;

            default:
                $this->error("Erro: A extensão '.{$extension}' não possui um driver de leitura mapeado no WMS.");
                return Command::INVALID;
        }

        if (empty(trim($rawText))) {
            $this->error("Erro: O arquivo está completamente vazio ou não pôde ser decodificado.");
            return Command::FAILURE;
        }

        $this->line("Iniciando fragmentação semântica e geração de vetores de Embeddings no LM Studio...");

        // 3. Dispara a orquestração do RAG para quebrar o texto e salvar no MySQL
        $totalChuncks = $ragService->ingestDocument($documentName, $rawText);

        if ($totalChuncks > 0) {
            $this->info("Sucesso! O documento '{$documentName}' foi totalmente processado.");
            $this->info("Foram gerados e salvos no MySQL {$totalChuncks} fragmentos vetorizados de conhecimento.");
            return Command::SUCCESS;
        }

        $this->error("Falha: Nenhum vetor pôde ser gerated. Certifique-se de que o LM Studio está aberto e com o modelo de Embeddings carregado.");
        return Command::FAILURE;
    }
}