<?php

namespace Local\Model;

use DomainException;
use Laminas\Filter\ToInt;
use Laminas\Filter\ToNull;
use Laminas\Filter\StripTags;
use Laminas\Filter\StringTrim;
use Laminas\Validator\StringLength;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\InputFilter\InputFilterAwareInterface;

class Local implements InputFilterAwareInterface 
{
    public $id; // propriedades que vao p index.phtml
    public $name;
    public $type_id;
    public $type_name; // local_type.name
    
    private $inputFilter;
    
    public function exchangeArray(array $data)
    {
        $this->id = (!empty($data['id'])) ? $data['id'] : null;
        $this->name = (!empty($data['name'])) ? $data['name'] : null;        
        $this->type_id = (!empty($data['type_id'])) ? $data['type_id'] : null;
        $this->type_name = (!empty($data['type_name'])) ? $data['type_name'] : null;
    }
    
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    
    public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new DomainException(sprintf(
            '%s does not allow injection of an alternate input filter',
            __CLASS__                
        ));
    }
    
    public function getInputFilter()
    {
        if ($this->inputFilter) {
            return $this->inputFilter;
        }
        
        $inputFilter = new InputFilter();

        $inputFilter->add([
            'name' => 'id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class], // so aceita inteiros
            ],
        ]);

        $inputFilter->add([
            'name' => 'name',
            'required' => true,
            'filters' => [
                ['name' => StripTags::class], // remover HTMl indesejado 
                ['name' => StringTrim::class], // remover espacos em branco desnecessarios
            ],
            'validators' => [
                [
                    'name' => StringLength::class,
                    'options' => [
                        'encoding' => 'UTF-8',
                        'min' => 1,
                        'max' => 60,
                    ],
                ],
            ],
        ]);
        
        $inputFilter->add([
            'name' => 'type_id',
            'required' => true,
            'filters' => [
                ['name' => ToInt::class],
                ['name' => ToNull::class],
            ],
        ]);

        $this->inputFilter = $inputFilter;
        return $this->inputFilter;        
    }
}
