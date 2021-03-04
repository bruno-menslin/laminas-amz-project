<?php

namespace Local\Model;

use Laminas\Db\TableGateway\TableGatewayInterface;
use RuntimeException;
use Laminas\Db\Sql\Select;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Paginator\Adapter\DbSelect;
use Laminas\Paginator\Paginator;

use Local\Model\Type;

class LocalTable {
    private $localTableGateway;
    private $typeTableGateway;    
    
    public function __construct(TableGatewayInterface $localTableGateway, TableGatewayInterface $typeTableGateway)
    {
        $this->localTableGateway = $localTableGateway;
        $this->typeTableGateway = $typeTableGateway;
    }
    
    public function fetchAll($paginated = false)
    {
        $select = $this->typeTableGateway->getSql()->select(); // new Select() nao funciona
        $select->columns(['typeId' => 'id', 'typeName' => 'name']); 
        $select->join('local', 'local_type.id = local.type_id', ['localId' => 'id', 'localName' => 'name', 'type_id']);
        
        if ($paginated) {
            return $this->fetchPaginatedResults($select);
        }

    	$result = $this->typeTableGateway->selectWith($select);
        return $result;    	
    }
    
    private function fetchPaginatedResults($select)
    {    
        // novo result set beaseando no objeto entidade Type, faz parte do adapter
        $resultSetPrototype = new ResultSet();
        $resultSetPrototype->setArrayObjectPrototype(new Type());

        // novo objeto pagination adapter
        $paginatorAdapter = new DbSelect(

            // objeto select configurado
            $select,

            // o adaptador para executa-lo
            $this->typeTableGateway->getAdapter(),

            // result set para hydrate
            $resultSetPrototype
        );

        $paginator = new Paginator($paginatorAdapter); // requere um adapter
        return $paginator; // retorna objetos Album como os resultados sem paginacao
    }
    
    public function getTypes()
    {
        $select = $this->typeTableGateway->getSql()->select();
        $select->columns(['typeId' => 'id', 'typeName' => 'name']); 

    	$result = $this->typeTableGateway->selectWith($select);

    	return $result->toArray();
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
