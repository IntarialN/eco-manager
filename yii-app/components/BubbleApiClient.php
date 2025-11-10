<?php

namespace app\components;

use RuntimeException;
use Yii;
use yii\base\Component;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

class BubbleApiClient extends Component
{
    public string $baseUrl = 'http://localhost:4001/api';
    public string $apiKey = 'demo-key';

    private Client $client;

    public function init(): void
    {
        parent::init();
        $this->client = new Client([
            'baseUrl' => rtrim($this->baseUrl, '/'),
        ]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getContracts(?string $clientExternalId = null): array
    {
        $url = ['contracts'];
        if ($clientExternalId !== null) {
            $url['client_external_id'] = $clientExternalId;
        }
        return $this->send('GET', $url);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getInvoices(string $contractExternalId): array
    {
        return $this->send('GET', ["contracts/{$contractExternalId}/invoices"]);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public function getActs(string $contractExternalId): array
    {
        return $this->send('GET', ["contracts/{$contractExternalId}/acts"]);
    }

    public function getInvoice(string $invoiceExternalId): array
    {
        return $this->send('GET', ["invoices/{$invoiceExternalId}"]);
    }

    /**
     * @param string|array $url
     */
    private function send(string $method, $url, ?array $data = null): array
    {
        $request = $this->createRequest($method, $url);
        if ($data !== null) {
            $request->setData($data);
        }

        $response = $request->send();
        if (!$response->isOk) {
            throw new RuntimeException(sprintf(
                'Bubble API request failed: %s %s (status %s) response=%s',
                $method,
                is_array($url) ? json_encode($url) : $url,
                $response->statusCode,
                $response->content
            ));
        }

        return $response->data ?? [];
    }

    /**
     * @param string|array $url
     */
    private function createRequest(string $method, $url): Request
    {
        return $this->client->createRequest()
            ->setMethod($method)
            ->setUrl($url)
            ->addHeaders([
                'Accept' => 'application/json',
                'X-API-Key' => $this->apiKey,
            ]);
    }
}
