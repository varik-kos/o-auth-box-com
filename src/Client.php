<?php

namespace app\library\box;

use GuzzleHttp\Psr7\StreamWrapper;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\ClientException;

class Client
{
    /** @var string */
    protected $accessToken;

    /** @var \GuzzleHttp\Client */
    protected $client;

    public function __construct(string $accessToken, GuzzleClient $client = null)
    {
        $this->accessToken = $accessToken;

        $this->client = $client ?? new GuzzleClient([
                'headers' => [
                    'Authorization' => "Bearer {$this->accessToken}",
                ],
            ]);
    }

    /**
     * Gets all of the files, folders, or web links contained within a folder.
     *
     * https://developer.box.com/reference#get-a-folders-items
     *
     * https://api.box.com/2.0/folders/folder_id/items
     */
    public function getFolderItems(string $path = '')
    {
        $folder = 'folders/'.$path.'/items';
        $parameters = [
            'limit' => 100
        ];

        return $this->rpcEndpointRequest($folder, $parameters, 'get');
    }

    /**
     * Create a folder at a given path.
     *
     * @link https://developer.box.com/reference#create-a-new-folder
     */
    public function createFolder(string $name, string $parentID)
    {
        $parameters = [
            'name' => $name,
            'parent.id' => $parentID
        ];

        return $this->rpcEndpointRequest('folders', $parameters);
    }

    /**
     * Download a file from a user's Box.com
     * Retrieves the actual data of the file. An optional version parameter can be set to download a previous version of the file.
     * @param int $fileID
     *
     * @return resource
     *
     * @link https://developer.box.com/reference#download-a-file
     */
    public function download(int $fileID)
    {
        $response = $this->contentEndpointRequest($fileID);

        return StreamWrapper::getResource($response->getBody());
    }

    protected function normalizePath(string $path): string
    {
        $path = trim($path, '/');

        return ($path === '') ? '' : '/'.$path;
    }

    public function rpcEndpointRequest(string $endpoint, array $parameters = null, string $method): array
    {
        try {
            $options = [];

            if ($parameters) {
                $options['json'] = $parameters;
            }

            $response = $this->client->$method("https://api.box.com/2.0/{$endpoint}", $options);
        } catch (ClientException $exception) {
            throw $this->determineException($exception);
        }

        $response = json_decode($response->getBody(), true);

        return $response ?? [];
    }

    public function contentEndpointRequest(int $fileID, $body = ''): ResponseInterface
    {
        if ($body !== '') {
            $headers['Content-Type'] = 'application/json';
        }

        try {
            $response = $this->client->get('https://api.box.com/2.0/files/'.$fileID.'/content', [
                'headers' => $headers,
                'body' => $body,
            ]);
        } catch (ClientException $exception){
            throw $this->determineException($exception);
        }
        return $response;
    }

    protected function determineException(ClientException $exception): \Exception
    {
        if (in_array($exception->getResponse()->getStatusCode(), [400, 409])) {
            return new BadRequest($exception->getResponse());
        }

        return $exception;
    }

}
