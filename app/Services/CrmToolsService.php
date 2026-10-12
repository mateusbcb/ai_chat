<?php

namespace App\Services;

use App\Models\Store;
use App\Models\Supplier;
use App\Models\Product;

class CrmToolsService
{
    /**
     * Retorna as definições em JSON das ferramentas para enviar ao LM Studio.
     */
    public function getToolsManifest(): array
    {
        return [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'consultar_estoque_loja',
                    'description' => 'Busca a lista de produtos e suas quantidades em estoque de uma franquia através do nome da cidade.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'cidade' => [
                                'type' => 'string',
                                'description' => 'O nome da cidade da franquia (ex: São Paulo, Porto Alegre, Curitiba).'
                            ]
                        ],
                        'required' => ['cidade']
                    ]
                ]
            ],
            [
                'type' => 'function',
                'function' => [
                    'name' => 'listar_fornecedores',
                    'description' => 'Retorna a lista completa de fornecedores homologados no sistema com seus respectivos CNPJs e e-mails.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => (object)[]
                    ]
                ]
            ]
        ];
    }

    /**
     * Executa dinamicamente a ferramenta solicitada pela IA.
     */
    public function execute(string $functionName, array $arguments): string
    {
        switch ($functionName) {
            case 'consultar_estoque_loja':
                $cidade = $arguments['cidade'] ?? '';
                $loja = Store::with('products')->where('city', 'like', "%{$cidade}%")->first();

                if ($loja) {
                    $linhas = $loja->products->map(function($p) {
                        return "- {$p->title} (SKU: {$p->sku}) | Preço: R$ {$p->price} | Estoque: {$p->pivot->stock_quantity} un";
                    })->implode("\n");
                    return "Dados do MySQL para a Filial '{$loja->name}':\n" . $linhas;
                }
                return "Aviso técnico: Nenhuma franquia foi localizada para a cidade de '{$cidade}' no banco de dados.";

            case 'listar_fornecedores':
                $fornecedores = Supplier::get()->map(function($s) {
                    return "- Fornecedor: {$s->name} | CNPJ: {$s->cnpj} | E-mail: {$s->contact_email}";
                })->implode("\n");
                return "Dados do MySQL para Fornecedores:\n" . $fornecedores;

            default:
                return "Erro técnico: A ferramenta '{$functionName}' não foi mapeada no back-end do Laravel.";
        }
    }
}