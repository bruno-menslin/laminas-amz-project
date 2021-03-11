<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Http\Request;
use Laminas\Http\Client;
use Laminas\View\Model\ViewModel;
use Local\Form\TypeForm;
use Local\Model\Type;
use Laminas\Json\Json;
use Laminas\Paginator\Paginator;
use Laminas\Paginator\Adapter;

class TypeController extends AbstractActionController
{
    public function indexAction()
    {
        $types = $this->fetchTypes();        
        $paginator = new Paginator(new Adapter\ArrayAdapter((array) $types));
        $page = (int) $this->params()->fromQuery('page', 1);
        $page = ($page < 1) ? 1 : $page;
        $paginator->setCurrentPageNumber($page);
        $paginator->setItemCountPerPage(10);

        return new ViewModel(['paginator' => $paginator]); 
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
        $this->rpc(
            Request::METHOD_POST,
            ['name' => $type->name]
        );
        
        return $this->redirect()->toRoute('localtype');        
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            return $this->redirect()->toRoute('localtype', ['action' => 'add']);
        }
        
        try { // verifica se o tipo com $id existe
            $type = $this->fetchType($id);                        
        } catch (Exception $e) {
            return $this->redirect()->toRoute('localtype');
        }
        
        $form = new TypeForm();
        $form->bind($type);
        $form->get('submit')->setValue('Edit');
        
        $request = $this->getRequest();
        $viewData = ['id' => $id, 'form' => $form];
        
        if (! $request->isPost()) { // nao foram enviados dados
            return $viewData; // usuario nao preencheu form ainda
        }
        
        $form->setInputFilter($type->getInputFilter());
        $form->setData($request->getPost());
        
        if (! $form->isValid()) {
            return $viewData;
        }
        
        $this->rpc(
            Request::METHOD_PATCH,
            ['id' => $type->id, 'name' => $type->name]
        );
        
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
                
                $this->rpc(
                    Request::METHOD_DELETE,
                    ['id' => $id]
                );
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
        $response = $this->rpc(
            Request::METHOD_GET,
            []
        );
        return json_decode($response->getBody());        
    }
    
    private function fetchType($id)
    {
        
        $response = $this->rpc(
            Request::METHOD_GET,
            ['id' => $id]
        );
        
        $data = json_decode($response->getBody());
        
        if (empty($data->id)) {
            return $this->redirect()->toRoute('local');
        }
        
        $type = new Type();
        $type->exchangeArray((array) $data);
        return $type;
    }

    public function rpc($method, $body)    
    {
        $headers = [
            'Content-Type: application/json', 
            'Accept: */*', 'Accept-Encoding: gzip, deflate, br', 
            'Connection: keep-alive'
        ];
        
        $client = new Client();
        $client->setUri('http://0.0.0.0:8080/localtype');
        $client->setMethod($method);
        $client->setHeaders($headers);
        $client->setRawBody(Json::encode($body));        
        $response = $client->send();
        return $response;
    }    
}
