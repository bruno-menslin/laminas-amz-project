<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Local\Form\LocalForm;
use Local\Model\Local;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Json\Json;

class LocalController extends AbstractActionController
{    
    public function indexAction()
    {                
        $locations = $this->fetchLocations();        
        $paginator = new Paginator(new Adapter\ArrayAdapter($locations));
        
        // define a pagina atual para o que foi passado na string,
        // ou 1 se nada estiver definido, ou a pagina é invalida
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        return new ViewModel(['paginator' => $paginator]);  
    }
    
    public function addAction()
    {
        $types = $this->fetchTypes();
        $form = new LocalForm($types);
        $form->get('submit')->setValue('Add');
        
        $req = $this->getRequest();
        if (! $req->isPost()) {
            return ['form' => $form];
        }
        // se dados foram enviados
        
        $local = new Local();
        $form->setInputFilter($local->getInputFilter()); // passa input filters do Local
        $form->setData($req->getPost());
        
        if (! $form->isValid()) {
            return ['form' => $form];
        }
        
        $local->exchangeArray($form->getData());               
        $this->rest(
            'http://0.0.0.0:8080/local', 
            Request::METHOD_POST,
            ['name' => $local->name, 'type_id' => $local->type_id]
        );
        
        return $this->redirect()->toRoute('local');        
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            return $this->redirect()->toRoute('local', ['action' => 'add']);
        }
        
        try { // verifica se o local com $id existe
            $local = $this->fetchLocal($id);                        
        } catch (Exception $e) {
            return $this->redirect()->toRoute('local');
        }
                
        $types = $this->fetchTypes();        
        $form = new LocalForm($types);
        $form->bind($local);
        $form->get('submit')->setValue('Edit');
        
        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];
        
        if (! $request->isPost()) { // nao foram enviados dados
            return $viewData; // usuario nao preencheu form ainda
        }
        
        $form->setInputFilter($local->getInputFilter());
        $form->setData($request->getPost());
        
        if (! $form->isValid()) {
            return $viewData;
        }
        
        $this->rest(
            "http://0.0.0.0:8080/local/{$id}", 
            Request::METHOD_PATCH,
            ['name' => $local->name, 'type_id' => $local->type_id]
        ); 
        
        return $this->redirect()->toRoute('local');
    }
    
    public function deleteAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if (! $id) {
            return $this->redirect()->toRoute('local');
        }
        
        $request = $this->getRequest();
        if ($request->isPost()) { //dados foram enviados
            
            $del = $request->getPost('del', 'No');
            
            if($del === 'Yes') {
                $id = (int) $request->getPost('id');    
                
                $this->rest(
                    "http://0.0.0.0:8080/local/{$id}",
                    Request::METHOD_DELETE,
                    ['']
                );                
            }
            
            return $this->redirect()->toRoute('local');
        }
        
        $local = $this->fetchLocal($id);

        return [
            'id' => $id,
            'local' => $local
        ];
    }
    
    public function fetchLocations()
    {
        $response = $this->rest(
            'http://0.0.0.0:8080/local',
            Request::METHOD_GET,
            []
        );        
        $data = json_decode($response->getBody());
        return $data->_embedded->local;
    }
    
    public function fetchLocal($id)
    {
        $response = $this->rest(
            "http://0.0.0.0:8080/local/{$id}",
            Request::METHOD_GET,
            []
        );        
        $data = json_decode($response->getBody());
        
        if (empty($data->id)) {
            return $this->redirect()->toRoute('local');
        }
        
        $local = new Local();
        $local->exchangeArray((array) $data);
        return $local;
    }
    
    public function fetchTypes()
    {        
        $response = $this->rest(
            'http://0.0.0.0:8080/localtype',
            Request::METHOD_GET,
            []
        );        
        return json_decode($response->getBody());             
    }
    
    public function rest($uri, $method, $body)    
    {
        $headers = [
            'Content-Type: application/json', 
            'Accept: */*', 'Accept-Encoding: gzip, deflate, br', 
            'Connection: keep-alive'
        ];
        
        $client = new Client();
        $client->setUri($uri);
        $client->setMethod($method); //Request::METHOD_POST
        $client->setHeaders($headers);
        $client->setRawBody(Json::encode($body));        
        $response = $client->send();
        return $response;
    }
}
