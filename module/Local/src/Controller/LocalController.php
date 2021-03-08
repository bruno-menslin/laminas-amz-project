<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Local\Model\LocalTable;
use Local\Model\TypeTable;
use Local\Form\LocalForm;
use Local\Model\Local;
use Laminas\Http\Client;
use Laminas\Http\Request;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter;
use Laminas\Json\Json;

class LocalController extends AbstractActionController
{
    private $localTable;
    private $typeTable;
    
    public function __construct(LocalTable $localTable, TypeTable $typeTable)
    {
        $this->localTable = $localTable;
        $this->typeTable = $typeTable;
    }
    
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
        
        $request = new Request();
        $request->setMethod(Request::METHOD_POST);
        $request->setUri('/local');
        $request->getHeaders()->addHeaders([            
            'Content-Type' => 'application/json',
            'Host' => '0.0.0.0:8080',
            'Accept' => '*/*',
            'Accept-Encoding' => 'gzip, deflate, br',
            'Connection' => 'keep-alive',
            
        ]);
        $request->getPost()->name = $local->name;
        $request->getPost()->type_id = $local->type_id;
        $request->setContent($request->getPost()->toString());
        
        $client = new Client();
        $client->send($request);
                
//        $uri = 'http://0.0.0.0:8080/local';
//        $client->setUri($uri);
//        $client->setMethod(Request::METHOD_POST);                
//        $client->setRawBody(Json::encode(['name' => $local->name, 'type_id' => $local->type_id]));                
//        $response = $client->send();
//        $content = $response->getBody();
        
//        return ['a' => $content, 'form' => $form];
        
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
        // dados validos
        
        $this->localTable->saveLocal($local);
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
                
                // deletar local
                $request = new Request();
                $request->setMethod(Request::METHOD_DELETE);
                $request->setUri('http://0.0.0.0:8080/local/' . $id);
                $request->getHeaders()->addHeaders([
                    'Accept' => '*/*',
                ]);
                $client = new Client();
                $client->send($request);                
                // deletar local
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
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setUri('http://0.0.0.0:8080/local');
        $request->getHeaders()->addHeaders([
            'Accept' => '*/*',
        ]);
        $client = new Client();
        $response = $client->send($request);
        $locations = json_decode($response->getBody());
        return $locations->_embedded->local;
    }
    
    public function fetchLocal($id)
    {
        $request = new Request();
        $request->setMethod(Request::METHOD_GET);
        $request->setUri('http://0.0.0.0:8080/local/' . $id);
        $request->getHeaders()->addHeaders([
            'Accept' => '*/*',
        ]);
        $client = new Client();
        $response = $client->send($request);
        $data = json_decode($response->getBody());
        $local = new Local();
        $local->exchangeArray((array) $data);
        return $local;
    }
    
    public function fetchTypes()
    {        
        $request = new Request();
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
