<?php

namespace rstecinfo\yii;

use rstecinfo\yii\EvolutionApi;

class ProfileSettings
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
     * Busca as configurações atuais da instância na API Evolution.
     *
     * @return array A resposta da API
     */
    public function fetchProfile(): array
    {
        return $this->api->get("/chat/fetchProfile/{$this->instance}");
    }
}
