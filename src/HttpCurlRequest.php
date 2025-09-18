<?php
namespace rstecinfo\yii\EvolutionApi;

use Yii;

/**
 * Componente para realizar requisições HTTP usando cURL
 * Suporta métodos GET, POST, PUT, DELETE, PATCH
 */
class HttpCurlRequest 
{
    /**
     * @var int Timeout da requisição em segundos
     */
    public $timeout = 30;
    
    /**
     * @var bool Verificar SSL
     */
    public $verifySSL = true;
    
    /**
     * @var bool Seguir redirecionamentos
     */
    public $followRedirects = true;
    
    /**
     * @var int Número máximo de redirecionamentos
     */
    public $maxRedirects = 5;
    
    /**
     * @var array Headers padrão para todas as requisições
     */
    public $defaultHeaders = [
        'Content-Type' => 'application/json',
        'Accept' => 'application/json',
    ];
    
    /**
     * @var array Opções padrão do cURL
     */
    private $defaultCurlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 5,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 10,
    ];
    
    /**
     * Realiza uma requisição HTTP
     * 
     * @param string $url URL da requisição
     * @param string $method Método HTTP (GET, POST, PUT, DELETE, etc.)
     * @param array $data Dados para enviar
     * @param array $headers Headers adicionais
     * @param array $curlOptions Opções adicionais do cURL
     * @return array Resposta da requisição
     * @throws \Exception
     */
    public function request($url, $method = 'GET', $data = [], $headers = [], $curlOptions = [])
    {
        if (!function_exists('curl_init')) {
            throw new \Exception('cURL não está disponível no servidor');
        }
        
        $ch = curl_init();
        
        // Configura método e dados
        $this->setMethod($ch, $method, $data);
        
        // Configura URL
        if ($method === 'GET' && !empty($data)) {
            $url = $this->buildUrlWithQuery($url, $data);
        }
        curl_setopt($ch, CURLOPT_URL, $url);
        
        // Configura headers
        $allHeaders = array_merge($this->defaultHeaders, $headers);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->formatHeaders($allHeaders));
        
        // Configura opções padrão
        $this->setDefaultOptions($ch);
        
        // Configura opções personalizadas
        foreach ($curlOptions as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
        
        // Executa a requisição
        $response = curl_exec($ch);
        
        // Verifica erros
        if ($response === false) {
            $error = curl_error($ch);
            $errno = curl_errno($ch);
            curl_close($ch);
            throw new \Exception("cURL Error ({$errno}): {$error}");
        }
        
        // Processa a resposta
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        
        $responseHeaders = substr($response, 0, $headerSize);
        $responseBody = substr($response, $headerSize);
        
        curl_close($ch);
        
        return [
            'status' => $httpCode,
            'headers' => $this->parseHeaders($responseHeaders),
            'body' => $responseBody,
            'success' => $httpCode >= 200 && $httpCode < 300
        ];
    }
    
    /**
     * Requisição GET
     * 
     * @param string $url URL da requisição
     * @param array $queryParams Parâmetros de query
     * @param array $headers Headers adicionais
     * @return array Resposta da requisição
     */
    public function get($url, $queryParams = [], $headers = [])
    {
        return $this->request($url, 'GET', $queryParams, $headers);
    }
    
    /**
     * Requisição POST
     * 
     * @param string $url URL da requisição
     * @param array $data Dados para enviar
     * @param array $headers Headers adicionais
     * @return array Resposta da requisição
     */
    public function post($url, $data = [], $headers = [])
    {
        return $this->request($url, 'POST', $data, $headers);
    }
    
    /**
     * Requisição PUT
     * 
     * @param string $url URL da requisição
     * @param array $data Dados para enviar
     * @param array $headers Headers adicionais
     * @return array Resposta da requisição
     */
    public function put($url, $data = [], $headers = [])
    {
        return $this->request($url, 'PUT', $data, $headers);
    }
    
    /**
     * Requisição DELETE
     * 
     * @param string $url URL da requisição
     * @param array $data Dados para enviar
     * @param array $headers Headers adicionais
     * @return array Resposta da requisição
     */
    public function delete($url, $data = [], $headers = [])
    {
        return $this->request($url, 'DELETE', $data, $headers);
    }
    
    /**
     * Requisição PATCH
     * 
     * @param string $url URL da requisição
     * @param array $data Dados para enviar
     * @param array $headers Headers adicionais
     * @return array Resposta da requisição
     */
    public function patch($url, $data = [], $headers = [])
    {
        return $this->request($url, 'PATCH', $data, $headers);
    }
    
    /**
     * Configura o método HTTP e os dados
     * 
     * @param resource $ch Handle do cURL
     * @param string $method Método HTTP
     * @param array $data Dados
     */
    private function setMethod(&$ch, $method, $data)
    {
        $method = strtoupper($method);
        
        switch ($method) {
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                break;
            case 'PUT':
            case 'DELETE':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
                break;
            default:
                curl_setopt($ch, CURLOPT_HTTPGET, true);
                break;
        }
        
        // Configura dados para métodos que enviam corpo
        if (in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE']) && !empty($data)) {
            $contentType = $this->detectContentType();
            $postData = $this->formatData($data, $contentType);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
    }
    
    /**
     * Formata os dados baseado no content-type
     * 
     * @param array $data Dados
     * @param string $contentType Tipo de conteúdo
     * @return mixed Dados formatados
     */
    private function formatData($data, $contentType)
    {
        if ($contentType === 'application/json') {
            return json_encode($data);
        } elseif ($contentType === 'application/x-www-form-urlencoded') {
            return http_build_query($data);
        } elseif ($contentType === 'multipart/form-data') {
            return $data; // cURL trata multipart automaticamente
        }
        
        return $data;
    }
    
    /**
     * Detecta o content-type baseado nos headers
     * 
     * @return string Content-Type
     */
    private function detectContentType()
    {
        foreach ($this->defaultHeaders as $key => $value) {
            if (strtolower($key) === 'content-type') {
                return $value;
            }
        }
        return 'application/json';
    }
    
    /**
     * Constrói URL com parâmetros de query
     * 
     * @param string $url URL base
     * @param array $queryParams Parâmetros de query
     * @return string URL completa
     */
    private function buildUrlWithQuery($url, $queryParams)
    {
        $parsedUrl = parse_url($url);
        
        if (isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $existingParams);
            $queryParams = array_merge($existingParams, $queryParams);
        }
        
        $queryString = http_build_query($queryParams);
        
        $url = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : '';
        $url .= isset($parsedUrl['host']) ? $parsedUrl['host'] : '';
        $url .= isset($parsedUrl['port']) ? ':' . $parsedUrl['port'] : '';
        $url .= isset($parsedUrl['path']) ? $parsedUrl['path'] : '';
        $url .= $queryString ? '?' . $queryString : '';
        
        return $url;
    }
    
    /**
     * Formata headers para o formato do cURL
     * 
     * @param array $headers Headers
     * @return array Headers formatados
     */
    private function formatHeaders($headers)
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "{$key}: {$value}";
        }
        return $formatted;
    }
    
    /**
     * Configura opções padrão do cURL
     * 
     * @param resource $ch Handle do cURL
     */
    private function setDefaultOptions(&$ch)
    {
        $options = $this->defaultCurlOptions;
        
        // Configurações da instância
        $options[CURLOPT_FOLLOWLOCATION] = $this->followRedirects;
        $options[CURLOPT_MAXREDIRS] = $this->maxRedirects;
        $options[CURLOPT_TIMEOUT] = $this->timeout;
        
        // SSL verification
        if (!$this->verifySSL) {
            $options[CURLOPT_SSL_VERIFYPEER] = false;
            $options[CURLOPT_SSL_VERIFYHOST] = false;
        }
        
        foreach ($options as $option => $value) {
            curl_setopt($ch, $option, $value);
        }
    }
    
    /**
     * Parseia headers de resposta
     * 
     * @param string $headersString String de headers
     * @return array Headers parseados
     */
    private function parseHeaders($headersString)
    {
        $headers = [];
        $lines = explode("\r\n", $headersString);
        
        foreach ($lines as $line) {
            if (strpos($line, ':') !== false) {
                list($key, $value) = explode(':', $line, 2);
                $headers[trim($key)] = trim($value);
            }
        }
        
        return $headers;
    }
}