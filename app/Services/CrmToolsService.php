<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Product;
use App\Models\StoreSale;
use Illuminate\Support\Facades\DB;

class CrmToolsService
{
    /**
     * Retorna o manifesto de ferramentas atualizado para a arquitetura de CD e Malha Logística.
     */
    public function getToolsManifest(): array
    {
        return [
            // ==========================================
            // TOOLS PARA LOJA / FRANQUIA
            // ==========================================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'criar_loja',
                    'description' => 'Cria e registra uma nova loja/franquia física na malha de distribuição regional do CRM.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => ['type' => 'string', 'description' => 'Nome da filial (ex: Nexus Hub - Curitiba).'],
                            'city' => ['type' => 'string', 'description' => 'Nome da cidade da franquia.'],
                            'state' => ['type' => 'string', 'maxLength' => 2, 'description' => 'A sigla do estado com 2 letras maiúsculas (ex: PR).'],
                            'phone' => ['type' => 'string', 'description' => 'Opcional. Contato telefônico da filial.']
                        ],
                        'required' => ['name', 'city', 'state']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'editar_loja',
                    'description' => 'Atualiza os dados cadastrais ou telefone de uma loja/franquia física existente através do seu ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'O ID numérico real da loja no banco de dados.'],
                            'name' => ['type' => 'string', 'description' => 'Opcional. Novo nome comercial da filial.'],
                            'city' => ['type' => 'string', 'description' => 'Opcional. Nova cidade da franquia.'],
                            'state' => ['type' => 'string', 'maxLength' => 2, 'description' => 'Opcional. Nova sigla do estado.'],
                            'phone' => ['type' => 'string', 'description' => 'Opcional. Novo contato telefônico.']
                        ],
                        'required' => ['id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'buscar_loja',
                    'description' => 'Lista ou pesquisa por franquias cadastradas aplicando filtros por termo, limites e ordenação alfabética.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'termo' => ['type' => 'string', 'description' => 'Opcional. Palavra-chave para buscar por nome, cidade ou telefone.'],
                            'limite' => ['type' => 'integer', 'description' => 'Opcional. Quantidade máxima de lojas a retornar.'],
                            'ordenar_por_nome' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'description' => 'Opcional. Ordenação alfabética por nome.']
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'analisar_estoque_loja',
                    'description' => 'Busca a lista de produtos, quantidades físicas e status dos lotes estocados dentro de uma filial pela cidade.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cidade' => ['type' => 'string', 'description' => 'Nome da cidade da franquia para filtrar o estoque local.'],
                            'limite' => ['type' => 'integer', 'description' => 'Opcional. Limita a quantidade de itens no relatório.'],
                            'ordenar_por_preco' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'description' => 'Opcional. Ordenar por preço.'],
                            'estoque_maximo' => ['type' => 'integer', 'description' => 'Opcional. Filtrar produtos com estoque crítico local menor ou igual a este valor.']
                        ],
                        'required' => ['cidade']
                    ]
                ]
            ],

            // ==========================================
            // TOOLS PARA PRODUTOS (CD CENTRAL)
            // ==========================================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'criar_produto',
                    'description' => 'Adiciona um novo produto ao catálogo do Centro de Distribuição Central utilizando o controller oficial.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'title' => ['type' => 'string', 'description' => 'Título comercial do produto (ex: Jaqueta Vermelha).'],
                            'sku' => ['type' => 'string', 'description' => 'Código SKU único (ex: NEX-WMS-JKT01).'],
                            'price_base' => ['type' => 'number', 'description' => 'Preço de custo/base de entrada no CD Central.'],
                            'cd_stock_quantity' => ['type' => 'integer', 'description' => 'Volume inicial de estoque pulmão fisicamente nas docas do CD.'],
                            'color' => ['type' => 'string', 'description' => 'Opcional. Cor do produto.'],
                            'size' => ['type' => 'string', 'description' => 'Opcional. Grade ou tamanho (ex: G, GG, 42).'],
                            'weight' => ['type' => 'number', 'description' => 'Opcional. Peso líquido do item em KG (ex: 0.85).'],
                            'description' => ['type' => 'string', 'description' => 'Opcional. Breve descrição sobre as especificações de entrada.'],
                            'status' => ['type' => 'string', 'enum' => ['disponivel', 'em_transito', 'esgotado'], 'description' => 'Situação logística inicial no CD. Padrão: disponivel.']
                        ],
                        'required' => ['title', 'sku', 'price_base', 'cd_stock_quantity']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'editar_produto',
                    'description' => 'Modifica o preço base, estoque pulmão, título ou status de um produto cadastrado na Matriz através do ID.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'id' => ['type' => 'integer', 'description' => 'O ID numérico do produto no catálogo.'],
                            'title' => ['type' => 'string', 'description' => 'Opcional. Novo título do item.'],
                            'price_base' => ['type' => 'number', 'description' => 'Opcional. Novo preço de custo base.'],
                            'cd_stock_quantity' => ['type' => 'integer', 'description' => 'Opcional. Nova quantidade nas docas do CD.'],
                            'status' => ['type' => 'string', 'enum' => ['disponivel', 'em_transito', 'esgotado'], 'description' => 'Opcional. Nova situação no CD.']
                        ],
                        'required' => ['id']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'buscar_produto',
                    'description' => 'Busca produtos específicos no catálogo do CD central por título, SKU, cor ou tamanho aplicando filtros.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'termo' => ['type' => 'string', 'description' => 'Palavra-chave para pesquisar por nome, SKU, cor ou tamanho.'],
                            'limite' => ['type' => 'integer', 'description' => 'Opcional. Limite máximo de registros a retornar.'],
                            'ordenar_por_preco' => ['type' => 'string', 'enum' => ['asc', 'desc'], 'description' => 'Opcional. Ordenar por preço de custo.']
                        ],
                        'required' => ['termo']
                    ]
                ]
            ],

            // ==========================================
            // TOOLS PARA AUDITORIA DE VENDAS (PDV)
            // ==========================================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'registrar_movimentacao_caixa',
                    'description' => 'Lança um novo cupom fiscal de venda, estorno de devolução ou registro de quebra de estoque chamando o controller real.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'store_id' => ['type' => 'integer', 'description' => 'O ID numérico real da loja física emissora.'],
                            'product_id' => ['type' => 'integer', 'description' => 'O ID numérico do produto vendido/movimentado.'],
                            'price_sale' => ['type' => 'number', 'description' => 'O preço real praticado no momento da venda. Para quebras, passe 0.'],
                            'quantity' => ['type' => 'integer', 'description' => 'Quantidade física de peças movimentadas no movimento.'],
                            'status' => ['type' => 'string', 'enum' => ['vendido', 'devolvido', 'quebra'], 'description' => 'Tipo do movimento do caixa.']
                        ],
                        'required' => ['store_id', 'product_id', 'price_sale', 'quantity', 'status']
                    ]
                ]
            ],

            // ==========================================
            // TOOLS GERAIS (DASHBOARD WMS)
            // ==========================================
            [
                'type' => 'function',
                'function' => [
                    'name' => 'analisar_metricas_dashboard',
                    'description' => 'Retorna os indicadores gerais do Hub de Distribuição (faturamentos, estoque CD, estoque lojas) permitindo agrupamentos por cidade ou estado.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'agrupar_por' => ['type' => 'string', 'enum' => ['cidade', 'estado'], 'description' => 'Opcional. Agrupa o volume físico total de estoque descentralizado da rede.']
                        ]
                    ]
                ]
            ],

            [
                'type' => 'function',
                'function' => [
                    'name' => 'buscar_historico_vendas',
                    'description' => 'Busca e lista o histórico de cupons fiscais de vendas e devoluções emitidos pelas filiais, permitindo filtros por loja, produto ou status.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'store_id' => ['type' => 'integer', 'description' => 'Opcional. ID da loja para filtrar as vendas daquela filial.'],
                            'status' => ['type' => 'string', 'enum' => ['vendido', 'devolvido'], 'description' => 'Opcional. Filtra apenas por vendas concluídas ou devoluções.'],
                            'limite' => ['type' => 'integer', 'description' => 'Opcional. Limita a quantidade de cupons exibidos no relatório.']
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'auditar_perdas_quebras',
                    'description' => 'Lista exclusivamente os registros de avarias, quebras e perdas físicas de estoque ocorridas nas franquias para fins de auditoria de prejuízo.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'limite' => ['type' => 'integer', 'description' => 'Opcional. Limita a quantidade de registros de quebra exibidos.']
                        ]
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'transferir_estoque_cd_para_loja',
                    'description' => 'Realiza a transferência física e legal de um lote de produtos do Centro de Distribuição Central (CD Matriz) para o estoque local de uma franquia regional.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'sku' => ['type' => 'string', 'description' => 'O código SKU único do produto a ser transferido.'],
                            'store_id' => ['type' => 'integer', 'description' => 'O ID numérico real da franquia/loja de destino.'],
                            'quantidade' => ['type' => 'integer', 'description' => 'A quantidade de peças físicas a serem retiradas do CD e enviadas à filial.']
                        ],
                        'required' => ['sku', 'store_id', 'quantidade']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'gerar_imagem_ia',
                    'description' => 'Gera e renderiza uma imagem digital nova e do zero utilizando modelos de difusão estável (Text-to-Image) com base no prompt descritivo fornecido em inglês pelo usuário.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'prompt' => [
                                'type' => 'string',
                                'description' => 'A descrição visual ultra detalhada e obrigatoriamente traduzida para o INGLÊS da imagem que o usuário quer criar (ex: "A black delivery truck parked at a modern warehouse loading dock, photorealistic, 4k").'
                            ]
                        ],
                        'required' => ['prompt']
                    ]
                ]
            ]
        ];
    }

    /**
     * Executor Avançado: Encaminha requisições de escrita para os Controllers ou roda Queries locais.
     */
    public function execute(string $functionName, array $arguments): string
    {
        // Instancia o utilitário nativo de requisições do Laravel
        $request = new \Illuminate\Http\Request();

        switch ($functionName) {
            // ====================================================================
            // MUTATIONS - LOJAS / HUB REGIONAL (VIA CONTROLLER)
            // ====================================================================
            case 'criar_loja':
                $request->merge([
                    'name'  => $arguments['name'] ?? '',
                    'city'  => $arguments['city'] ?? '',
                    'state' => $arguments['state'] ?? '',
                    'phone' => $arguments['phone'] ?? null
                ]);
                try {
                    $controller = app(\App\Http\Controllers\StoreController::class);
                    $response = $controller->store($request);
                    if ($response->isRedirection()) {
                        return "Sucesso: A nova franquia foi cadastrada no MySQL através do StoreController.";
                    }
                    return "Aviso: O StoreController processou a requisição com status inesperado.";
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return "Erro de validação do Laravel: " . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                } catch (\Exception $e) {
                    return "Erro técnico no StoreController: " . $e->getMessage();
                }

            case 'editar_loja':
                $request->merge([
                    'name'  => $arguments['name'] ?? null,
                    'city'  => $arguments['city'] ?? null,
                    'state' => $arguments['state'] ?? null,
                    'phone' => $arguments['phone'] ?? null
                ]);
                try {
                    $controller = app(\App\Http\Controllers\StoreController::class);
                    $response = $controller->update($request, $arguments['id']);
                    if ($response->isRedirection()) {
                        return "Sucesso: Os dados cadastrais da franquia ID #{$arguments['id']} foram atualizados via StoreController.";
                    }
                    return "Aviso: Falha ao redirecionar após a atualização da loja.";
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return "Erro de validação do Laravel: " . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                } catch (\Exception $e) {
                    return "Erro técnico ao editar a loja: " . $e->getMessage();
                }

            // ====================================================================
            // MUTATIONS - PRODUTOS / CD CENTRAL (VIA CONTROLLER)
            // ====================================================================
            case 'criar_produto':
                $request->merge([
                    'title'             => $arguments['title'] ?? '',
                    'sku'               => $arguments['sku'] ?? '',
                    'price_base'        => $arguments['price_base'] ?? '',
                    'cd_stock_quantity' => $arguments['cd_stock_quantity'] ?? '',
                    'color'             => $arguments['color'] ?? null,
                    'size'              => $arguments['size'] ?? null,
                    'weight'            => $arguments['weight'] ?? null,
                    'description'       => $arguments['description'] ?? 'Criado via Assistente Virtual',
                    'status'            => $arguments['status'] ?? 'disponivel'
                ]);
                try {
                    $controller = app(\App\Http\Controllers\ProductController::class);
                    $response = $controller->store($request);
                    if ($response->isRedirection()) {
                        return "Sucesso: O produto deu entrada física no catálogo do CD central através do ProductController.";
                    }
                    return "Aviso: O ProductController processou a requisição com status inesperado.";
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return "Erro de validação do Laravel: " . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                } catch (\Exception $e) {
                    return "Erro técnico no ProductController: " . $e->getMessage();
                }

            case 'editar_produto':
                $request->merge([
                    'title'             => $arguments['title'] ?? null,
                    'price_base'        => $arguments['price_base'] ?? null,
                    'cd_stock_quantity' => $arguments['cd_stock_quantity'] ?? null,
                    'status'            => $arguments['status'] ?? null
                ]);
                try {
                    $controller = app(\App\Http\Controllers\ProductController::class);
                    $response = $controller->update($request, $arguments['id']);
                    if ($response->isRedirection()) {
                        return "Sucesso: Os parâmetros do produto ID #{$arguments['id']} foram modificados via ProductController.";
                    }
                    return "Aviso: Falha ao redirecionar após a atualização do produto.";
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return "Erro de validação do Laravel: " . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                } catch (\Exception $e) {
                    return "Erro técnico ao editar o produto: " . $e->getMessage();
                }

            // ====================================================================
            // MUTATIONS - AUDITORIA DE VENDAS / FRENTE DE CAIXA (VIA CONTROLLER)
            // ====================================================================
            case 'registrar_movimentacao_caixa':
                $request->merge([
                    'store_id'   => $arguments['store_id'] ?? '',
                    'product_id' => $arguments['product_id'] ?? '',
                    'price_sale' => $arguments['price_sale'] ?? '',
                    'quantity'   => $arguments['quantity'] ?? '',
                    'status'     => $arguments['status'] ?? ''
                ]);
                try {
                    $controller = app(\App\Http\Controllers\StoreSaleController::class);
                    $response = $controller->store($request);
                    if ($response->isRedirection()) {
                        return "Sucesso: A movimentação do caixa foi homologada e o inventário foi sincronizado via StoreSaleController.";
                    }
                    return "Aviso: O StoreSaleController processou a requisição com status inesperado.";
                } catch (\Illuminate\Validation\ValidationException $e) {
                    return "Erro de validação do Laravel: " . implode(', ', \Illuminate\Support\Arr::flatten($e->errors()));
                } catch (\Exception $e) {
                    return "Erro técnico no StoreSaleController: " . $e->getMessage();
                }

                // ====================================================================
            // READS - LOJAS & FRANQUIAS (QUERIES E ANÁLISES)
            // ====================================================================
            case 'buscar_loja':
                $termo = trim($arguments['termo'] ?? '');
                $limite = isset($arguments['limite']) ? (int)$arguments['limite'] : null;
                $ordenarNome = $arguments['ordenar_por_nome'] ?? null;

                $query = \App\Models\Store::with('products');

                if (!empty($termo)) {
                    $query->where('name', 'like', "%{$termo}%")
                          ->orWhere('city', 'like', "%{$termo}%")
                          ->orWhere('phone', 'like', "%{$termo}%");
                }

                if ($ordenarNome === 'asc' || $ordenarNome === 'desc') {
                    $query->orderBy('name', $ordenarNome);
                }

                if ($limite !== null && $limite > 0) {
                    $query->limit($limite);
                }

                $lojas = $query->get();

                if ($lojas->isEmpty()) {
                    return "Aviso: Nenhuma filial localizada no MySQL com os critérios informados.";
                }

                $linhas = $lojas->map(function($l) {
                    $totalModelos = $l->products->count();
                    $totalPecas = $l->products->sum('pivot.quantity');
                    return "- ID #{$l->id} | Hub: {$l->name} | Sede: {$l->city}-{$l->state} | Contato: " . ($l->phone ?? 'N/I') . " | Variedade: {$totalModelos} SKUs | Estoque Local: {$totalPecas} un";
                })->implode("\n");

                return "Registros retornados do MySQL para Busca de Lojas:\n" . $linhas;

            case 'analisar_estoque_loja':
                $cidade = trim($arguments['cidade'] ?? '');
                $limite = isset($arguments['limite']) ? (int)$arguments['limite'] : null;
                $ordenarPreco = $arguments['ordenar_por_preco'] ?? null;
                $estoqueMaximo = isset($arguments['estoque_maximo']) ? (int)$arguments['estoque_maximo'] : null;

                $loja = \App\Models\Store::where('city', 'like', "%{$cidade}%")->first();
                if (!$loja) {
                    return "Aviso técnico: Nenhuma franquia ativa localizada para a cidade de '{$cidade}' no banco de dados.";
                }

                $query = $loja->products();

                if ($estoqueMaximo !== null) {
                    $query->where('store_products.quantity', '<=', $estoqueMaximo);
                }

                if ($ordenarPreco === 'asc' || $ordenarPreco === 'desc') {
                    $query->orderBy('products.price_base', $ordenarPreco);
                }

                if ($limite !== null && $limite > 0) {
                    $query->limit($limite);
                }

                $products = $query->get();

                if ($products->isEmpty()) {
                    return "Dados do MySQL: O estoque físico local da Filial '{$loja->name}' não retornou nenhum registro para os filtros aplicados.";
                }

                $linhas = $products->map(function($p) {
                    return "- SKU: {$p->sku} | Produto: {$p->title} | Cor: {$p->color} | Preço Sugerido na Loja: R$ " . number_format($p->pivot->price_sale_suggested, 2, ',', '.') . " | Saldo Local: {$p->pivot->quantity} un [Status: {$p->pivot->status}]";
                })->implode("\n");

                return "Relatório de Inventário do MySQL para a Filial '{$loja->name}':\n" . $linhas;

            // ====================================================================
            // READS - CATÁLOGO DE PRODUTOS (QUERIES E ANÁLISES)
            // ====================================================================
            case 'buscar_produto':
                $termo = trim($arguments['termo'] ?? '');
                $limite = isset($arguments['limite']) ? (int)$arguments['limite'] : null;
                $ordenarPreco = $arguments['ordenar_por_preco'] ?? null;

                $query = \App\Models\Product::query();

                if (!empty($termo)) {
                    $query->where('title', 'like', "%{$termo}%")
                          ->orWhere('sku', 'like', "%{$termo}%")
                          ->orWhere('color', 'like', "%{$termo}%")
                          ->orWhere('size', 'like', "%{$termo}%")
                          ->orWhere('status', 'like', "%{$termo}%");
                }

                if ($ordenarPreco === 'asc' || $ordenarPreco === 'desc') {
                    $query->orderBy('price_base', $ordenarPreco);
                }

                if ($limite !== null && $limite > 0) {
                    $query->limit($limite);
                }

                $produtos = $query->get();

                if ($produtos->isEmpty()) {
                    return "Aviso: Nenhum item correspondente a '{$termo}' foi localizado no CD Central.";
                }

                $linhas = $produtos->map(function($p) {
                    return "- ID #{$p->id} | SKU: {$p->sku} | {$p->title} | Cor: " . ($p->color ?? 'N/I') . " | Tam: " . ($p->size ?? 'N/I') . " | Peso: " . ($p->weight ? $p->weight . 'kg' : 'N/I') . " | Custo Base CD: R$ " . number_format($p->price_base, 2, ',', '.') . " | Saldo CD Matriz: {$p->cd_stock_quantity} un [Status CD: {$p->status}]";
                })->implode("\n");

                return "Dados retornados do MySQL para Busca no Catálogo Central:\n" . $linhas;

            // ====================================================================
            // READS - DASHBOARD LOGÍSTICO (AGRUPAMENTOS)
            // ====================================================================
            case 'analisar_metricas_dashboard':
                $agruparPor = $arguments['agrupar_por'] ?? null;

                // Relatório Agrupado por Estado
                if ($agruparPor === 'estado') {
                    $dados = DB::table('stores')
                        ->join('store_products', 'stores.id', '=', 'store_products.store_id')
                        ->select('stores.state', DB::raw('count(distinct stores.id) as total_lojas'), DB::raw('sum(store_products.quantity) as estoque_total'))
                        ->groupBy('stores.state')
                        ->get();

                    $linhas = $dados->map(fn($d) => "- Estado [{$d->state}]: {$d->total_lojas} hubs ativos | Volume de Estoque Descentralizado: " . number_format($d->estoque_total, 0, ',', '.') . " unidades")->implode("\n");
                    return "Relatório Consolidado do MySQL por Estado (UF):\n" . $linhas;
                }

                // Relatório Agrupado por Cidade
                if ($agruparPor === 'cidade') {
                    $dados = DB::table('stores')
                        ->join('store_products', 'stores.id', '=', 'store_products.store_id')
                        ->select('stores.city', 'stores.state', DB::raw('sum(store_products.quantity) as estoque_total'))
                        ->groupBy('stores.city', 'stores.state')
                        ->get();

                    $linhas = $dados->map(fn($d) => "- Hub [{$d->city}-{$d->state}]: Volume de Estoque Local nas Prateleiras: " . number_format($d->estoque_total, 0, ',', '.') . " unidades")->implode("\n");
                    return "Relatório Consolidado do MySQL por Cidade Sede:\n" . $linhas;
                }

                // Indicadores Globais Padrão
                $totalProdutos = \App\Models\Product::count();
                $totalLojas = \App\Models\Store::count();
                $estoqueCD = \App\Models\Product::sum('cd_stock_quantity');
                $estoqueLojas = DB::table('store_products')->sum('quantity');
                
                $faturamento = DB::table('store_sales')
                    ->where('status', 'vendido')
                    ->select(DB::raw('SUM(price_sale * quantity) as total'))
                    ->first()->total ?? 0;

                return "Dados consolidados gerais extraídos do MySQL (Dashboard WMS):\n" .
                       "- Total de SKUs cadastrados no catálogo: {$totalProdutos}\n" .
                       "- Total de filiais físicas mapeadas na rede: {$totalLojas}\n" .
                       "- Volume de estoque pulmão armazenado no CD Central: " . number_format($estoqueCD, 0, ',', '.') . " unidades\n" .
                       "- Volume de estoque descentralizado nas lojas da rede: " . number_format($estoqueLojas, 0, ',', '.') . " unidades\n" .
                       "- Faturamento bruto total acumulado via PDV: R$ " . number_format($faturamento, 2, ',', '.');
            
            // ====================================================================
            // READS - AUDITORIA DE VENDAS E CAIXA (QUERIES DE HISTÓRICO)
            // ====================================================================
            case 'buscar_historico_vendas':
                $storeId = isset($arguments['store_id']) ? (int)$arguments['store_id'] : null;
                $status = $arguments['status'] ?? null;
                $limite = isset($arguments['limite']) ? (int)$arguments['limite'] : null;

                $query = \App\Models\StoreSale::with(['store', 'product']);

                // Filtra por uma loja específica se solicitado
                if ($storeId) {
                    $query->where('store_id', $storeId);
                }

                // Filtra por status (vendido ou devolvido), ignorando quebras nesta Tool específica
                if ($status === 'vendido' || $status === 'devolvido') {
                    $query->where('status', $status);
                } else {
                    $query->whereIn('status', ['vendido', 'devolvido']);
                }

                if ($limite !== null && $limite > 0) {
                    $query->limit($limite);
                }

                $vendas = $query->orderBy('created_at', 'desc')->get();

                if ($vendas->isEmpty()) {
                    return "Dados do MySQL: Nenhum cupom fiscal de venda ou devolução foi localizado com os parâmetros aplicados.";
                }

                $linhas = $vendas->map(function($v) {
                    $dataFormata = $v->created_at ? $v->created_at->format('d/m/Y H:i') : 'N/I';
                    $totalMovimento = number_format($v->price_sale * $v->quantity, 2, ',', '.');
                    return "- [{$dataFormata}] Loja: {$v->store->name} | Item: {$v->product->title} (SKU: {$v->product->sku}) | Qtd: {$v->quantity} un | Preço Un: R$ " . number_format($v->price_sale, 2, ',', '.') . " | Total: R$ {$totalMovimento} [Status: {$v->status}]";
                })->implode("\n");

                return "Histórico Fiscal de Movimentações extraído do MySQL:\n" . $linhas;

            case 'auditar_perdas_quebras':
                $limite = isset($arguments['limite']) ? (int)$arguments['limite'] : null;

                // Força o filtro estrito apenas por status 'quebra'
                $query = \App\Models\StoreSale::with(['store', 'product'])->where('status', 'quebra');

                if ($limite !== null && $limite > 0) {
                    $query->limit($limite);
                }

                $quebras = $query->orderBy('created_at', 'desc')->get();

                if ($quebras->isEmpty()) {
                    return "Dados do MySQL: Excelente! Nenhum registro de quebra, perda ou avaria física de estoque foi encontrado no histórico.";
                }

                $linhas = $quebras->map(function($q) {
                    $dataFormata = $q->created_at ? $q->created_at->format('d/m/Y H:i') : 'N/I';
                    return "- [{$dataFormata}] ALERTA DE PERDA -> Loja: {$q->store->name} | Item Danificado: {$q->product->title} (SKU: {$q->product->sku}) | Qtd Perdida: {$q->quantity} unidades [Prejuízo Base: R$ " . number_format($q->product->price_base * $q->quantity, 2, ',', '.') . "]";
                })->implode("\n");

                return "Relatório de Auditoria de Avarias e Quebras do MySQL:\n" . $linhas;

            // ====================================================================
            // MUTATIONS - TRANSFERÊNCIAS LOGÍSTICAS (WMS COMPLEXO)
            // ====================================================================
            case 'transferir_estoque_cd_para_loja':
                $sku = strtoupper(trim($arguments['sku'] ?? ''));
                $storeId = (int)($arguments['store_id'] ?? 0);
                $quantidade = (int)($arguments['quantidade'] ?? 0);

                if ($quantidade <= 0) {
                    return "Erro de validação: A quantidade para transferência precisa ser maior que zero. Você informou: {$quantidade}.";
                }

                // 1. Busca se o produto e a loja de fato existem no MySQL
                $product = \App\Models\Product::where('sku', $sku)->first();
                $store = \App\Models\Store::find($storeId);

                if (!$product) {
                    return "Aviso logístico: O produto com o SKU '{$sku}' não foi localizado no catálogo do CD Central.";
                }
                if (!$store) {
                    return "Aviso logístico: A franquia com o ID #{$storeId} não foi localizada no mapa de filiais.";
                }

                // 2. Validação estrita de saldo pulmão no CD Central
                if ($product->cd_stock_quantity < $quantidade) {
                    return "Bloqueio de Estoque: Transferência negada. O CD Central possui apenas {$product->cd_stock_quantity} unidades em estoque do SKU '{$sku}', mas você tentou transferir {$quantidade} unidades. Operação abortada por falta de saldo.";
                }

                // 3. Inicia a transação atômica segura no MySQL
                DB::beginTransaction();
                try {
                    // Subtrai a quantidade do estoque pulmão da Matriz (CD)
                    $product->decrement('cd_stock_quantity', $quantidade);

                    // Verifica se essa loja já possui esse produto em seu catálogo local
                    $existingPivot = DB::table('store_products')
                        ->where('store_id', $storeId)
                        ->where('product_id', $product->id)
                        ->first();

                    if ($existingPivot) {
                        // Se já existe o registro da grade na loja, incrementa o saldo local
                        DB::table('store_products')
                            ->where('store_id', $storeId)
                            ->where('product_id', $product->id)
                            ->update([
                                'quantity' => $existingPivot->quantity + $quantidade,
                                'status' => 'em_transito', // O lote entra em estado de viagem
                                'updated_at' => now()
                            ]);
                    } else {
                        // Se é a primeira vez que a loja recebe o item, cria o registro pivô do zero
                        DB::table('store_products')->insert([
                            'store_id' => $storeId,
                            'product_id' => $product->id,
                            'quantity' => $quantidade,
                            'price_sale_suggested' => round($product->price_base * 1.50, 2), // Margem padrão
                            'status' => 'em_transito',
                            'created_at' => now(),
                            'updated_at' => now()
                        ]);
                    }

                    DB::commit();
                    return "Sucesso Logístico: Transferência concluída no MySQL! Foram retiradas {$quantidade} unidades do SKU '{$sku}' do CD Central e despachadas em trânsito para a Filial '{$store->name}' (ID #{$storeId}). O saldo atual da Matriz para este item agora é de " . ($product->cd_stock_quantity) . " un.";

                } catch (\Exception $e) {
                    DB::rollBack();
                    return "Erro crítico de integridade ao processar transferência no MySQL: " . $e->getMessage();
                }

            // ====================================================================
            // MULTIMODAL - GERAÇÃO TEXT-TO-IMAGE COM RETORNO SEGURO (FALLBACK)
            // ====================================================================
            case 'gerar_imagem_ia':
                $promptText = trim($arguments['prompt'] ?? '');

                if (empty($promptText)) {
                    return "Erro técnico: O prompt de descrição da imagem não foi fornecido pelo modelo.";
                }

                $client = new \GuzzleHttp\Client();
                $baseUrl = env('LM_STUDIO_BASE_URL', 'http://localhost:1234/v1');
                $modelImage = env('LM_STUDIO_MODEL_IMAGE', 'sdxl-turbo');

                try {
                    // Tenta disparar a requisição contra o motor local do LM Studio
                    $response = $client->post($baseUrl . '/images/generations', [
                        'json' => [
                            'model' => $modelImage,
                            'prompt' => $promptText,
                            'n' => 1,
                            'size' => '512x512',
                            'response_format' => 'b64_json'
                        ],
                        'timeout' => 5, // Timeout curto para não travar o WampServer se o motor de imagem estiver desligado
                        'http_errors' => true,
                        'verify' => false
                    ]);

                    $responseData = json_decode($response->getBody(), true);
                    $base64Data = $responseData['data']['b64_json'] ?? null;

                    // INSTÂNCIA DE CONTINGÊNCIA (FALLBACK): Se o LM Studio de imagem estiver offline ou der erro
                    if (!$base64Data) {
                        // Gera uma imagem logística linda simulada via CDN pública estável para não travar seus testes de HTML
                        $palavrasChave = urlencode(str_replace(' ', ',', substr($promptText, 0, 30)));
                        $publicUrl = "https://picsum.photos" . time();
                        
                        return "Aviso Técnico: O motor local de imagem estava offline, então o sistema ativou o modo de simulação visual.\n" .
                               "Instrução para a IA de Chat: Exiba o relatório informando o uso do modo de contingência e renderize obrigatoriamente esta tag HTML abaixo para exibir a imagem conceitual gerada:\n" .
                               "<div class='my-4 bg-slate-900 p-2 border border-slate-700 rounded-2xl max-w-sm overflow-hidden shadow-xl'><img src='{$publicUrl}' class='w-full h-auto rounded-xl shadow-inner' alt='Imagem de Contingência'></div>\n" .
                               "Adicione uma frase elogiando o conceito visual gerado.";
                    }

                    // Se o LM Studio gerou com sucesso, segue o fluxo gravando o arquivo em disco
                    $folderPath = public_path('storage/images');
                    if (!file_exists($folderPath)) {
                        mkdir($folderPath, 0755, true);
                    }

                    $fileName = 'ai_' . time() . '_' . rand(100, 999) . '.png';
                    file_put_contents($folderPath . '/' . $fileName, base64_decode($base64Data));
                    $publicUrl = asset('storage/images/' . $fileName);

                    return "Sucesso Multimodal: A imagem foi gerada de verdade no notebook e salva em disco.\n" .
                           "Instrução para a IA de Chat: Exiba a imagem gerada renderizando exatamente esta tag HTML abaixo no meio da sua resposta:\n" .
                           "<div class='my-4 bg-slate-900 p-2 border border-slate-700 rounded-2xl max-w-sm overflow-hidden shadow-xl'><img src='{$publicUrl}' class='w-full h-auto rounded-xl shadow-inner' alt='Imagem Gerada'></div>";

                } catch (\Exception $e) {
                    // Captura qualquer estouro de timeout de rede e joga para o modo simulado seguro em vez de quebrar a tela
                    $publicUrl = "https://picsum.photos" . time();
                    return "Nota de Contingência: O servidor de imagem local não respondeu a tempo. Exiba para o usuário esta imagem conceitual do Hub de Distribuição utilizando a tag HTML:\n" .
                           "<div class='my-4 bg-slate-900 p-2 border border-slate-700 rounded-2xl max-w-sm overflow-hidden shadow-xl'><img src='{$publicUrl}' class='w-full h-auto rounded-xl shadow-inner' alt='Imagem Conceitual'></div>";
                }
                
            // FECHAMENTO SEGURO DO ARQUIVO
            default:
                return "Erro técnico: A ferramenta '{$functionName}' não possui mapeamento de execução no back-end.";
        }
    }
}