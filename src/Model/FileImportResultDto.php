<?php

namespace App\Model;

class FileImportResultDto
{
    public FileImportRequestDto $request;

    public bool $isError = false;    
    public ?string $error = null;  

    function __construct(bool $isError = false, string $error = '')
    {
        $this->setIsError($isError)
            ->setError($error);
    }

    public function setIsError(bool $isError): self
    {
        $this->isError = $isError;

        return $this;
    }

    public function setError(?string $error): self
    {
        $this->error = $error;

        return $this;
    }

    public function setRequest(FileImportRequestDto $request)
    {
        $this->request = $request;
    }
     
}
