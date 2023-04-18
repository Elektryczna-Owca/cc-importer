<?php

namespace App\Model;

class FileImportResultDto
{
    public UploadRequest $request;

    public bool $isError = false;    
    public ?string $error = null;  
    public int $deleted = 0;
    public int $inserted = 0;
    public array $content;    

    function __construct(bool $isError = false, string $error = '')
    {
        $this->isError = $isError;
        $this->$error = $error;
    }   
}
