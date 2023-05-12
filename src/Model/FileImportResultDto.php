<?php

namespace App\Model;
use App\Entity\Importer;

class FileImportResultDto
{
    public UploadRequest $request;
    public string $importer;

    public bool $isError = false;    
    public ?string $error = null;  
    public int $deleted = 0;
    public int $inserted = 0;
    public array $content;    

    function __construct(bool $isError = false, string $error = '')
    {
        $this->isError = $isError;
        $this->error = $error;
    } 
    
    public function AsHTMLString()
    {
      


        $tableHtml = '';
        for ($i = 0; $i < count($this->content); $i++) {
            $tableHtml .= '<tr>';
            $tableHtml .= '<td>'.($i+1).'</td>';
            $tableHtml .= '<td>'.$this->content[$i]->data.'</td>';
            $tableHtml .= '<td>'.$this->content[$i]->count.'</td>';
            $tableHtml .= '<td>'.$this->content[$i]->error.'</td>';
            $tableHtml .= '</tr>';
        }
//        <link rel=\"stylesheet\" href=\"https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css\" integrity=\"sha384-Vkoo8x4CGsO3+Hhxv8T/Q5PaXtkKtu6ug5TOeNV6gBiFeWPGFN9MuhOf23Q9Ifjh\" crossorigin=\"anonymous\">

        return 
        "<html>
        <head>
        <style>
            table, td, th {  
                border: 1px solid #ddd;
                text-align: left;
            }
            
            table {
                border-collapse: collapse;
                width: 100%;
            }
              
            th, td {
                padding: 15px;
            }
            fieldset {
                border-top : 2px solid #ddd;;
                border-bottom: 0;
                border-left: 0;
                border-right: 0;
                boxing: 15px;
                margin-bottom: 15px;
                }
        </style>
        </head>
        <body>
        <fieldset>
        <legend>Reqested with:</legend>"
        .(property_exists($this->request, 'importer') ? ("<div><label>Importer:<b>".$this->request->importer->getName()."</b></label></div>") : "").
        "<div><label>File: <b>".$this->request->file->getClientOriginalName()."</b></label></div>
        <div><label>Test only: <b>".($this->request->testOnly ? "Yes":"No")."</b></label></div>
        <div><label>Do not delete:<b>".($this->request->doNotDelete ? "Yes":"No")."</b></label></div>
        </fieldset>  
        <fieldset>
        <legend>Result with:".($this->isError?"<b> Error ".($this->error== null?"":" (".$this->error.")")."</b>":"")."</legend>
        <div><label>Total lines: <b>".count($this->content)."</b></label></div>
        <div><label><b>".$this->deleted."</b> item(s) deleted from database</label></div>
        <div><label><b>".$this->inserted."</b> item(s) inserted into database</label></div>
        </div>
        <table>
            <tr>
                <th>Line nr</th>
                <th>Line data</th>
                <th>Links created</th>
                <th>Error</th>
            </tr>"
            .$tableHtml.
        "</table>        
        </fieldset>
        </body></html>";

    }
}
