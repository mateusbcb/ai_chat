<?php

namespace App\Services;

use App\Models\KnowledgeBase;
use GuzzleHttp\Client;

class CrmRagService
{
    protected $client;
    protected $baseUrl;
    protected $modelEmbedding;

    public function __construct()
    {
        $this->client = new Client();
        // Endpoint padrão local do LM Studio
        $this->baseUrl = rtrim(env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1'), '/');
        // Configure essa variável no seu .env se o nome for diferente
        $this->modelEmbedding = env('LM_STUDIO_MODEL_EMBEDDING', 'nomic-ai/nomic-embed-text-v1.5-GGUF');
    }

    /**
     * CHUINKING & EMBEDDING: Lê um texto bruto, fragmenta e indexa no MySQL com vetores.
     */
    public function ingestDocument(string $documentName, string $rawText): int
    {
        // Quebra o texto por quebras de linha duplas ou parágrafos reais
        $paragraphs = explode("\n\n", $rawText);
        $insertedCount = 0;

        foreach ($paragraphs as $paragraph) {
            $chunk = trim($paragraph);
            if (empty($chunk) || strlen($chunk) < 10) continue;

            // Dispara contra o LM Studio para gerar as coordenadas matemáticas (Vetor)
            try {
                $response = $this->client->post($this->baseUrl . '/embeddings', [
                    'json' => [
                        'model' => $this->modelEmbedding,
                        'input' => $chunk
                    ],
                    'verify' => false,
                    'http_errors' => true
                ]);

                $data = json_decode($response->getBody(), true);
                // Captura o array de floats gerado (geralmente 768 ou 1536 posições)
                $vector = $data['data'][0]['embedding'] ?? null;

                if ($vector) {
                    KnowledgeBase::create([
                        'document_name' => $documentName,
                        'content_chunk' => $chunk,
                        'embedding_vector' => $vector // O cast JSON do Model cuida do formato
                    ]);
                    $insertedCount++;
                }
            } catch (\Exception $e) {
                // Se falhar em um chunk, continua para os próximos
                logger("Erro ao gerar embedding para chunk: " . $e->getMessage());
            }
        }

        return $insertedCount;
    }

    /**
     * RETRIEVAL: Busca os parágrafos mais parecidos matematicamente com a dúvida do usuário.
     */
    public function searchRelevantContext(string $userQuery, int $limit = 2): string
    {
        try {
            // 1. Gera o vetor matemático da própria dúvida do usuário
            $response = $this->client->post($this->baseUrl . '/embeddings', [
                'json' => [
                    'model' => $this->modelEmbedding,
                    'input' => $userQuery
                ],
                'verify' => false,
                'http_errors' => true
            ]);

            $data = json_decode($response->getBody(), true);
            $queryVector = $data['data'][0]['embedding'] ?? null;

            if (!$queryVector) return "";

            // 2. Busca todas as regras/textos salvos no MySQL para comparar
            $allKnowledge = KnowledgeBase::all();
            $matches = [];

            foreach ($allKnowledge as $item) {
                // Calcula a Similaridade de Cosseno entre o vetor da pergunta e o vetor do banco
                $score = $this->cosineSimilarity($queryVector, $item->embedding_vector);
                
                $matches[] = [
                    'chunk' => $item->content_chunk,
                    'score' => $score
                ];
            }

            // 3. Ordena do maior score (mais parecido) para o menor
            usort($matches, fn($a, $b) => $b['score'] <=> $a['score']);

            // Pega os parágrafos do topo baseados no limite solicitado
            $topMatches = array_slice($matches, 0, $limit);
            
            $contextText = "";
            foreach ($topMatches as $match) {
                // Definimos um piso de aceitação semântica mínima de 0.35 para evitar contextos aleatórios
                if ($match['score'] >= 0.35) {
                    $contextText .= $match['chunk'] . "\n\n";
                }
            }

            return trim($contextText);

        } catch (\Exception $e) {
            logger("Erro na busca semântica do RAG: " . $e->getMessage());
            return "";
        }
    }

    /**
     * FUNÇÃO MATEMÁTICA: Similaridade de Cosseno pura em PHP.
     */
    private function cosineSimilarity(array $vec1, array $vec2): float
    {
        $dotProduct = 0.0;
        $normA = 0.0;
        $normB = 0.0;

        $count = min(count($vec1), count($vec2));
        for ($i = 0; $i < $count; $i++) {
            $dotProduct += $vec1[$i] * $vec2[$i];
            $normA += $vec1[$i] ** 2;
            $normB += $vec2[$i] ** 2;
        }

        if ($normA == 0 || $normB == 0) return 0.0;

        return $dotProduct / (sqrt($normA) * sqrt($normB));
    }
}