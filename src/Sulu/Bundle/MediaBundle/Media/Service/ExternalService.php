<?php

namespace Sulu\Bundle\MediaBundle\Media\Service;

use Sulu\Bundle\MediaBundle\Api\Media;
use Guzzle\Http\Client;

class ExternalService implements ServiceInterface
{
    protected $externalService = array();

    protected $serializer;

    protected $client;

    /**
     * @param array
     */
    public function __construct(
        $externalService,
        $serializer
    ) {
        $this->externalService = $externalService;
        $this->serializer = $serializer;
        $this->client = new Client();
    }

    private function makeRequest($JSONstring, $action)
    {
        foreach ($this->externalService as $key => $value) {
            $request = $this->client->post($value[$action]);
            $request->setBody($JSONstring, 'application/json');
            $res = $request->send();
            echo $res->getStatusCode();
        }
    }

    public function add(Media $media)
    {
        $mediaJson = $this->serializer->serialize($media, 'json');
        $this->makeRequest($mediaJson, 'add');
    }

    public function update(Media $media)
    {
    	$mediaJson = $this->serializer->serialize($media, 'json');
        $this->makeRequest($mediaJson, 'update');
    }

    public function delete(Media $media)
    {
        $mediaJson = $this->serializer->serialize($media, 'json');
        $this->makeRequest($mediaJson, 'delete');
    }
}
