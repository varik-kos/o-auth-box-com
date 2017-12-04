<?php

namespace app\library\box\Exceptions;


use Psr\Http\Message\ResponseInterface;

class BoxBadRequest extends \Exception
{
    /**
     * The boxcom error code supplied in the response.
     *
     * @var string|null
     */
    public $boxcomCode;

    public function __construct(ResponseInterface $response)
    {
        $body = json_decode($response->getBody(), true);

        if (isset($body['error']['.tag'])) {
            $this->boxcomCode = $body['error']['.tag'];
        }

        parent::__construct($body['error_summary']);
    }

}