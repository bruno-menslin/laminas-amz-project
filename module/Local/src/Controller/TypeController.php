<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\View\Model\ViewModel;
use Local\Form\TypeForm;
use Local\Model\Type;
use Laminas\Json\Json;

class TypeController extends AbstractActionController
{
    public function indexAction()
    {
        $types = $this->fetchTypes();
        return new ViewModel(['types' => $types]);
    }
    
    public function addAction()
    {
        $form = new TypeForm();
        $form->get('submit')->setValue('Add');
        
        $req = $this->getRequest();
        if (! $req->isPost()) {
            return ['form' => $form];
        }
        // se dados foram enviados   
        
        $type = new Type();
        $form->setInputFilter($type->getInputFilter());
        $form->setData($req->getPost());
        
        if (!$form->isValid()) {
            return ['form' => $form];
        }
        
        $type->exchangeArray($form->getData());
        $this->saveType($type);
        return $this->redirect()->toRoute('localtype');        
    }
    
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (! $id) {
            return $this->redirect()->toRoute('localtype');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) { //dados foram enviados
            
            $del = $request->getPost('del', 'No');
            
            if($del === 'Yes') {
                $id = (int) $request->getPost('id'); // post, no submit
                
                // deletar tipo
                $client = new Client();
                $uri = 'http://0.0.0.0:8080/localtype';
                $client->setUri($uri);
                $client->setMethod(Request::METHOD_DELETE);                
                $headers = [
                    'Content-Type: application/json', 
                    'Accept: */*', 'Accept-Encoding: gzip, deflate, br', 
                    'Connection: keep-alive'
                ];
                $client->setHeaders($headers);
                $client->setRawBody(Json::encode(['id' => $id]));        
                $client->send();
                // deletar tipo
            }
            
            return $this->redirect()->toRoute('localtype');
        }
        
        $type = $this->fetchType($id);

        return [
            'id' => $id,
            'type' => $type
        ];
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
    
    private function fetchType($id)
    {
        $client = new Client();
        $uri = 'http://0.0.0.0:8080/localtype';
        $client->setUri($uri);
        $client->setMethod(Request::METHOD_GET);        
        $headers = [
            'Content-Type: application/json', 
            'Accept: */*', 'Accept-Encoding: gzip, deflate, br', 
            'Connection: keep-alive'
        ];
        $client->setHeaders($headers);
        $client->setRawBody(Json::encode(['id' => $id]));        
        $response = $client->send();
        return json_decode($response->getBody());
    }
    
    private function saveType($type)
    {
        $client = new Client();
        $uri = 'http://0.0.0.0:8080/localtype';
        $client->setMethod(Request::METHOD_POST);
        $client->setUri($uri);
        $headers = [
            'Content-Type: application/json', 
            'Accept: */*', 'Accept-Encoding: gzip, deflate, br', 
            'Connection: keep-alive'
        ];
        $client->setHeaders($headers);
        $client->setRawBody(Json::encode(['name' => $type->name]));        
        $client->send();
    }    
}
