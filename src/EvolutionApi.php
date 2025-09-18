<?php

/**
 * @copyright Copyright &copy; Rogerio Soares, 2025
 * @package yii2-evolution-api
 * @version 0.0.1
 */

namespace rstecinfo\yii\EvolutionApi;

use yii\base\Widget;
use yii\httpclient\Client;

class EvolutionApi extends Widget {

    /**
     * @var string $baseUrl URL base da API Evolution
     */
    protected string $baseUrl;

    /**
     * @var string $apiKey Chave de API para autenticação
     */
    protected string $apiKey;

    /**
     * @var Client $client Cliente Guzzle HTTP usado para fazer as requisições
     */
    protected Client $client;

    /**
     * evolutionapi constructor.
     *
     * Inicializa a classe evolutionapi com a URL base e a chave de API.
     * Também instancia o cliente Guzzle e armazena como uma propriedade da classe.
     *
     * @param string $baseUrl URL base da API Evolution
     * @param string $apiKey Chave de API fornecida pela Evolution
     */
    public function __construct(string $baseUrl, string $apiKey) {
        // Se não forem passados valores, utiliza as configurações
        $this->baseUrl = $baseUrl;
        $this->apiKey = $apiKey;

        // Instancia o cliente Guzzle com a URL base e os headers padrões (API Key)
        $this->client = new Client([
            'baseUrl' => $this->baseUrl,
        ]);
    }

    // Novo método para definir o cliente Guzzle (apenas para fins de teste)
    public function setClient(Client $client): void {
        $this->client = $client;
    }

    /**
     * Faz uma requisição GET para a API Evolution.
     *
     * @param string $endpoint O endpoint relativo da API (ex: '/instance/fetchInstances')
     * @param array $params Parâmetros de consulta (query) a serem enviados na URL
     *
     * @return array A resposta da API decodificada para um array PHP
     * @throws Exception
     */
    public function get(string $endpoint, array $params = []): array {
        try {
            // Faz uma requisição GET passando os parâmetros na URL (query string)
            $response = $this->client->get($endpoint,
                    ['query' => $params],
                    ['apikey' => $this->apiKey]
            );

            // Retorna o corpo da resposta decodificado como array
            $ret = $response->getData();
            return is_array($ret) ? $ret : [$ret];
        } catch (Exception $e) {
            // Lidar com exceções de forma apropriada
            //throw new \Exception("Erro ao fazer requisição GET: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Faz uma requisição POST para a API Evolution.
     *
     * @param string $endpoint O endpoint relativo da API (ex: '/instance/create')
     * @param array $data Dados a serem enviados como JSON no corpo da requisição
     *
     * @return array A resposta da API decodificada para um array PHP
     * @throws Exception
     */
    public function post(string $endpoint, array $data = []): array {
        try {
            // Faz uma requisição POST enviando os dados como JSON no corpo
            $response = $this->client->post($endpoint,
                    ['json' => $data],
                    ['apikey' => $this->apiKey]
            );
            $ret = $response->getData();
            return is_array($ret) ? $ret : [$ret];
        } catch (Exception $e) {
            // Lidar com exceções de forma apropriada
            //throw new \Exception("Erro ao fazer requisição POST: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Faz uma requisição DELETE para a API Evolution.
     *
     * @param string $endpoint O endpoint relativo da API (ex: '/instance/delete')
     * @param array $data (Opcional) Dados a serem enviados como JSON no corpo da requisição DELETE
     *
     * @return array A resposta da API decodificada para um array PHP
     * @throws Exception
     */
    public function delete(string $endpoint, array $data = []): array {
        try {
            // Faz uma requisição DELETE enviando os dados como JSON no corpo, se necessário
            $response = $this->client->delete($endpoint,
                    ['json' => $data],
                    ['apikey' => $this->apiKey]
            );
            // Retorna o corpo da resposta decodificado como array
            $ret = $response->getData();
            return is_array($ret) ? $ret : [$ret];
        } catch (Exception $e) {
            // Lidar com exceções de forma apropriada
            //throw new \Exception("Erro ao fazer requisição DELETE: " . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }

    /**
     * Faz uma requisição GET para a API Evolution.
     *
     * @param string $endpoint O endpoint relativo da API (ex: '/instance/fetchInstances')
     * @param array $params Parâmetros de consulta (query) a serem enviados na URL
     *
     * @return array A resposta da API decodificada para um array PHP
     * @throws Exception
     */
    public function status(string $endpoint): array {
        try {
            // Faz uma requisição GET 
            $response = $this->client->get($endpoint, [],
                    ['apikey' => $this->apiKey]
            );
            // Retorna o corpo da resposta decodificado como array
            $ret = $response->getData();
            return is_array($ret) ? $ret : [$ret];
        } catch (Exception $e) {
            $response = $e?->getResponse();
            if ($response == null) {
                return [null];
            }
            return json_decode($response?->getData(), true);
        }
    }
}
