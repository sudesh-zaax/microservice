<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\RequestException;

class AuthServiceClient
{
    protected string $baseUrl;
    protected int $timeout;
    protected array $headers;

    public function __construct()
    {
        $this->baseUrl = config('services.auth.url', 'http://auth');
        $this->timeout = config('services.auth.timeout', 30);
        $this->headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
    }

    /**
     * Make a GET request
     *
     * @param string $endpoint
     * @param array $query
     * @return array
     */
    protected function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request
     *
     * @param string $endpoint
     * @return array
     */
    protected function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make a PATCH request
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    protected function patch(string $endpoint, array $data = []): array
    {
        return $this->request('PATCH', $endpoint, ['json' => $data]);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method
     * @param string $endpoint
     * @param array $options
     * @return array
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $url = $this->baseUrl . '/' . ltrim($endpoint, '/');
            
            $response = Http::withHeaders($this->headers)
                ->timeout($this->timeout)
                ->withOptions([
                    'verify' => config('services.auth.verify_ssl', true)
                ])
                ->send($method, $url, $options);

            return [
                'success' => $response->successful(),
                'data' => $response->json(),
                'status' => $response->status()
            ];
        } catch (RequestException $e) {
            return [
                'success' => false,
                'error' => $e->response?->json() ?? $e->getMessage(),
                'status' => $e->response?->status() ?? 500
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'status' => 500
            ];
        }
    }

    /**
     * Set request timeout
     *
     * @param int $seconds
     * @return self
     */
    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;
        return $this;
    }

    /**
     * Set additional headers
     *
     * @param array $headers
     * @return self
     */
    public function withHeaders(array $headers): self
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    public function getPolicies2(string $endpoint, array $query = []): array
    {
        return $this->get($endpoint, $query);
    }

    public function login(string $endpoint, array $query = []): array
    {
        return $this->post($endpoint, $query);
    }
}
