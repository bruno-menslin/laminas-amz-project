<?php

namespace Local\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class LocalTable {
    private $tableGateway;
    
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll()
    {
        return $this->tableGateway->select();
        
    }
}
