<?php

namespace Local\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use RuntimeException;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;

class LocalTable {
    private $tableGateway;
    
    public function __construct(TableGatewayInterface $tableGateway)
    {
        $this->tableGateway = $tableGateway;
    }
    
    public function fetchAll($paginated = false)
    {
        $select = $this->tableGateway->getSql()->select(); // new Select() nao funciona
        $select->columns(['id', 'name', 'type_id']); 
        $select->join('local_type', 'local_type.id = local.type_id', ['type_name' => 'name']);
        
        if ($paginated) {
            return $this->fetchPaginatedResults($select);
        }

    	$result = $this->tableGateway->selectWith($select);
        return $result;    	
    }
    
    private function fetchPaginatedResults($select)
    {    
        // novo result set beaseando no objeto entidade Type, faz parte do adapter
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Local());

        // novo objeto pagination adapter
        $paginatorAdapter = new DbSelect(

            // objeto select configurado
            $select,

            // o adaptador para executa-lo
            $this->tableGateway->getAdapter(),

            // result set para hydrate
            $resultSetPrototype
        );

        $paginator = new Paginator($paginatorAdapter); // requere um adapter
        return $paginator; // retorna objetos Album como os resultados sem paginacao
    }    
    
    public function getLocal($id)
    {
        $id = (int) $id;
        $rowset = $this->tableGateway->select(['id' => $id]);
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
            $this->tableGateway->insert($data);
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
        
        $this->tableGateway->update($data, ['id' => $id]);  
    }
    
    public function deleteLocal($id)
    {
        $this->tableGateway->delete(['id' => (int) $id]);
    }
}
