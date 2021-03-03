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
        return new ViewModel([
            'locations' => $this->table->fetchAll(),
        ]);
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
        $form->setData($request->getPost());
        
        if (! $form->isValid()) {
            return ['form' => $form];
        }
        
        $local->exchangeArray($form->getData());
        $this->table->saveLocal($local);
        return $this->redirect()->toRoute('local');        
    }
}
