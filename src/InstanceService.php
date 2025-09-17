<?php

namespace rstecinfo\yii\EvolutionApi;

use rstecinfo\yii\EvolutionApi\EvolutionApi;

class InstanceService
{
    /**
     * @var evolutionapi
     */
    protected evolutionapi $api;

    /**
     * InstanceService constructor.
     *
     * Inicializa o serviço com uma instância de evolutionapi.
     */
    public function __construct(Evolutionapi $api)
    {
        $this->api = $api;
    }

    /**
     * Cria uma nova instância na API Evolution.
     *
     * @param string $instanceName Nome da instância
     * @param bool $qrcode Definir se o QRCode deve ser gerado
     * @param string $integration Tipo de integração
     *
     * @return array A resposta da API
     */
    public function createInstance(string $instanceName, bool $qrcode = true, bool $integration = false): array
    {
        $data = [
            'instanceName' => $instanceName,
            'integration' => $integration,
            'qrcode' => $qrcode ?? true,
        ];
        return $this->api->post('/instance/create', $data);
    }

    /**
     * Retorna a lista de instâncias criadas.
     *
     * @return array A resposta da API
     */
    public function fetchInstances(): array
    {
        return $this->api->get('/instance/fetchInstances');
    }

    /**
     * Conecta a uma instância específica.
     *
     * @param string $instance ID da instância
     *
     * @return array A resposta da API
     */
    public function connectInstance(string $instance): array
    {
        return $this->api->get("/instance/connect/{$instance}");
    }

    /**
     * Reinicia uma instância.
     *
     * @param string $instance ID da instância
     *
     * @return array A resposta da API
     */
    public function restartInstance(string $instance): array
    {
        return $this->api->post("/instance/restart/{$instance}");
    }

    /**
     * Define o status de presença da instância.
     *
     * @param string $instance ID da instância
     * @param string $presence Status de presença a ser definido
     *
     * @return array A resposta da API
     */
    public function setPresence(string $instance, string $presence): array
    {
        return $this->api->post("/instance/setPresence/{$instance}", [
            'presence' => $presence,
        ]);
    }

    /**
     * Obtém o estado de conexão de uma instância.
     *
     * @param string $instance ID da instância
     *
     * @return array A resposta da API
     */
    public function getConnectionStatus(string $instance): array
    {
        return $this->api->status("/instance/connectionState/{$instance}");
    }

    /**
     * Faz o logout de uma instância.
     *
     * @param string $instance ID da instância
     *
     * @return array A resposta da API
     */
    public function logoutInstance(string $instance): array
    {
        return $this->api->delete("/instance/logout/{$instance}");
    }

    /**
     * Deleta uma instância específica.
     *
     * @param string $instance ID da instância
     *
     * @return array A resposta da API
     */
    public function deleteInstance(string $instance): array
    {
        return $this->api->delete("/instance/delete/{$instance}");
    }
}
