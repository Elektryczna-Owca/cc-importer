<?php

namespace App\Model;
use App\Entity\Importer;
use JsonSerializable;

class UploadWithImporterRequest extends UploadRequest
{
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
