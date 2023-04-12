<?php

namespace App\Model;

class FileImportResultExtDto extends FileImportResultDto
{
    public int $deleted = 0;
    public int $inserted = 0;
    public array $content;    


    public function __construct(array $content, int $deleted, int $inserted, bool $isError = false, string $error = '')
    {
        parent::__construct($isError, $error);
        $this->setContent($content)
            ->setDeleted($deleted)
            ->setInserted($inserted);
    }
    
   
    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function setDeleted(int $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function setInserted(int $inserted): self
    {
        $this->inserted = $inserted;

        return $this;
    }   
}
