<?php

namespace Local\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use RuntimeException;
use Laminas\Db\Sql\Select;

class LocalTable {
    private $localTableGateway;
    private $typeTableGateway;    
    
    public function __construct(TableGatewayInterface $localTableGateway, TableGatewayInterface $typeTableGateway)
    {
        $this->localTableGateway = $localTableGateway;
        $this->typeTableGateway = $typeTableGateway;
    }
    
    public function fetchAll()
    {
        
        $sqlSelect = $this->typeTableGateway->getSql()->select(); 
        $sqlSelect->columns(['TypeId' => 'id', 'TypeName' => 'name']); 
        $sqlSelect->join('local', 'local_type.id = local.type_id', ['LocalId' => 'id', 'LocalName' => 'name', 'type_id']);

    	$resultSet = $this->typeTableGateway->selectWith($sqlSelect);

    	return $resultSet->toArray();
        
    }
    
    public function getLocal($id)
    {
        $id = (int) $id;
        $rowset = $this->localTableGateway->select(['id' => $id]);
        $row = $rowset->current(); // somente primera linha
        if (!$row) {
            throw new RuntimeException(sprintf(
                'Could not find row with identifier %d',
                $id
            ));
        }
        return $row;
    }
    
    public function saveLocal(Local $local) // para add e edit
    {
        $data = [
            'name' => $local->name,
            'type_id' => $local->type_id,
        ];
        
        $id = (int) $local->id;
        
        if ($id === 0) {
            $this->localTableGateway->insert($data);
            return;
        }
        // edit
        
        try { // verificar se ja existe no banco
            $this->getLocal($id);
        } catch (RuntimeException $e) {
            throw new RuntimeException(sprintf(
                'Cannot update local with identifier %d; does not exist',
                $id
            ));
        }
        
        $this->localTableGateway->update($data, ['id' => $id]);  
    }
    
    public function deleteLocal($id)
    {
        $this->localTableGateway->delete(['id' => (int) $id]);
    }
}
