<?php

namespace App\Model;
use App\Entity\Importer;
use JsonSerializable;

class UploadRequest implements JsonSerializable
{
    public $file = null;  
    public bool $testOnly = true;  
    public bool $doNotDelete = true; 
    public ?Importer $importer = null; 

    public function jsonSerialize() {
        return [
            'file' => $this->file->getClientOriginalName(),
            'testOnly' => $this->testOnly,
            'doNotDelete' => $this->doNotDelete,
            'importer' => $this->importer->getName()
        ];
    }
}
