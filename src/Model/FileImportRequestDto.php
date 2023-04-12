<?php

namespace App\Model;


class FileImportRequestDto
{
    public ?string $file = null;  
    public ?string $importerName = null;    
    public bool $testOnly = true;  
    public bool $doNotDelete = true;  

}