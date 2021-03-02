<?php

namespace Local\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Local\Model\LocalTable;

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
}
