<?php

namespace Tapp\Airtable\Api;

use Illuminate\Support\Str;
use Tapp\Airtable\Resources\Record;
use GuzzleHttp\Client;
use Carbon\Carbon;

class AirtableApiClient implements ApiClient
{
    private $client;

    private $base;
    private $table;

    private $pageSize = 100;
    private $maxRecords = 100;

    public function __construct($base, $table, $access_token)
    {
        $this->base = $base;
        $this->table = $table;
        $this->client = $this->buildClient($access_token);
    }

    private function buildClient($access_token)
    {
        return new Client([
            'base_uri' => 'https://api.airtable.com',
            'headers' => [
                'Authorization' => "Bearer {$access_token}",
                'content-type' => 'application/json',
            ],
        ]);
    }

    public function get(string $id = null)
    {
        $url = $this->getEndpointUrl($id);

        return $this->jsonToObject($this->client->get($url));
    }

    public function getAllPages()
    {
        $url = $this->getEndpointUrl();

        $response = $this->client->get($url, [
            'query' => [
                'pageSize' => $this->pageSize,
                'maxRecords' => $this->maxRecords,
            ]
        ]);

        //TODO: loop through offset to get more than one page when more than 100 records exist

        return $this->jsonToObject($response);
    }

    public function post($contents = null)
    {
        $url = $this->getEndpointUrl();

        $params = $contents === null ? ['body' => ''] : ['json' => $contents];

        return $this->jsonToObject($this->client->post($url, $params));
    }

    public function put(string $id, $contents = null)
    {
        $url = $this->getEndpointUrl($id);

        $params = $contents === null ? ['body' => ''] : ['json' => $contents];

        return $this->jsonToObject($this->client->put($url, $params));
    }

    public function patch(string $id, $contents = null)
    {
        $url = $this->getEndpointUrl($id);

        $params = $contents === null ? ['body' => ''] : ['json' => $contents];

        return $this->jsonToObject($this->client->patch($url, $params));
    }

    public function delete(string $id)
    {
        $url = $this->getEndpointUrl($id);

        return $this->jsonToObject($this->client->delete($url));
    }

    public function responseToJson($response)
    {
        $body = (string) $response->getBody();

        return $body;
    }

    public function jsonToObject($response)
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return collect([]);
        }

        return collect(json_decode($body));
    }

    public function jsonToArray($response)
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        return json_decode($body, true);
    }

    protected function getEndpointUrl(?string $id = null): string
    {
        if ($id) {
            $url = '/v0/?/?/?';

            return Str::replaceArray('?', [
                $this->base,
                $this->table,
                $id,
            ], $url);
        }

        $url = '/v0/?/?';

        return Str::replaceArray('?', [
            $this->base,
            $this->table,
        ], $url);
    }
}