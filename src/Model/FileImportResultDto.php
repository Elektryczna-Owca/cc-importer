<?php

namespace App\Model;

class FileImportResultDto
{
    public array $content;    
    public bool $isError = false;    
    public ?string $error = null;  

    public static function of(array $content, bool $isError = false, string $error = ''): FileImportResultDto
    {
        $page = new FileImportResultDto();
        $page->setContent($content)
            ->setIsError($isError)
            ->setError($error);

        return $page;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
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

   
  
    public function asArray(): array
    {
        return [
            'isError' => $this->isError,
            'error' => $this->error,
            'content' => $this->content->toArray()
        ];
    }
}
