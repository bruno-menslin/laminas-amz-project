<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Local\Model\LocalTable;
use Local\Form\LocalForm;
use Local\Model\Local;

class LocalController extends AbstractActionController
{
    private $table;
    
    public function __construct(LocalTable $table)
    {
        $this->table = $table;
    }
    
    public function indexAction()
    {
        $locations = $this->table->fetchAll();
        $view = new ViewModel(['locations' => $locations]);
        return $view;
    }
    
    public function addAction()
    {
        $form = new LocalForm();
        $form->get('submit')->setValue('Add');
        
        $request = $this->getRequest();
        if (! $request->isPost()) {
            return ['form' => $form];
        }
        // se dados foram enviados
        
        $local = new Local();
        $form->setInputFilter($local->getInputFilter()); // passa input filters do Local
        $form->setData($request->getPost());
        
        if (! $form->isValid()) {
            return ['form' => $form];
        }
        
        $local->exchangeArray($form->getData());
        $this->table->saveLocal($local);
        return $this->redirect()->toRoute('local');        
    }
    
    public function editAction()
    {
        $id = (int) $this->params()->fromRoute('id', 0);
        if ($id === 0) {
            return $this->redirect()->toRoute('local', ['action' => 'add']);
        }
        
        try { // verifica se o local com $id existe
            $local = $this->table->getLocal($id); 
        } catch (Exception $e) {
            return $this->redirect()->toRoute('local');
        }
        
        $form = new LocalForm();
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
        
        $this->table->saveLocal($local);
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
                $this->table->deleteLocal($id);
            }
            
            return $this->redirect()->toRoute('local');
        }
        
        return [
            'id' => $id,
            'local' => $this->table->getLocal($id),
        ];
    }
}
