<?php

namespace Local\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;

class TypeTable {
    private $tableGateway;
    
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }

    public function fetchAll()
    {        
        $select = $this->tableGateway->getSql()->select();
        $select->columns(['id', 'name']); 

    	$result = $this->tableGateway->selectWith($select);

    	return $result->toArray();
    }
}
