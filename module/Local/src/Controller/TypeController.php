<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\View\Model\ViewModel;

class TypeController extends AbstractActionController
{
    public function indexAction()
    {
        $types = $this->fetchTypes();
        return new ViewModel(['types' => $types]);
    }
    
    private function fetchTypes()
    {
        $request = new Request;
        $request->setMethod(Request::METHOD_GET);
        $request->setUri('http://0.0.0.0:8080/localtype');
        $request->getHeaders()->addHeaders([
            'Accept' => '*/*',
        ]);
        $client = new Client();
        $response = $client->send($request);
        return json_decode($response->getBody());
    }
}
