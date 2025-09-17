<?php

namespace rstecinfo\yii;

use rstecinfo\yii\Evolutionapi;

class WebhookSet
{
    /**
     * @var evolutionapi
     */
    protected evolutionapi $api;

    /**
     * @var string Nome da Instância API Evolution
     */
    protected string $instance;

    /**
     * GroupService constructor.
     *
     * Inicializa o serviço com uma instância de evolutionapi.
     *
     * @param evolutionapi $api
     * @param string $instance
     */
    public function __construct(Evolutionapi $api, string $instance)
    {
        $this->api = $api;
        $this->instance = $instance;
    }

    /**
     * Define a instância da API Evolution.
     *
     * @param string $instance ID da instância
     * @return $this
     * @throws \InvalidArgumentException Se o ID da instância for inválido
     */
    public function setInstance(string $instance)
    {
        // Verificação básica se a instância não é uma string vazia
        if (empty($instance)) {
            throw new \InvalidArgumentException("A instância não pode ser vazio.");
        }

        $this->instance = $instance;
        return $this;
    }

    /**
     * Define as configurações padrão da instância na API Evolution.
     *
     * @param array $settings Dados das configurações a serem aplicadas*
     * @return array A resposta da API
     */
    public function webhookSet(array $settings): array
    {
        $data = [
            'webhook' => $settings
        ];
        return $this->api->post("/webhook/set/{$this->instance}", $data);
    }

    /**
     * Busca as configurações atuais da instância na API Evolution.
     *
     * @return array A resposta da API
     */
    public function webhookFind(): array
    {
        return $this->api->get("/webhook/find/{$this->instance}");
    }
}
