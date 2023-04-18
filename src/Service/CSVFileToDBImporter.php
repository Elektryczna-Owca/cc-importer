<?php

namespace App\Service;

use App\Model\FileImportResultDto;
use App\Model\ResourceImportResultDto;
use Psr\Log\LoggerInterface;

class CSVFileToDBImporter
{
    private $logger;
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPwd;
    private $importer_id = -1;

    public function __construct(string $dbHost, string $dbName, string $dbUser, string $dbPwd, LoggerInterface $logger)
    {
        $this->dbHost = $dbHost; 
        $this->dbName = $dbName; 
        $this->dbUser = $dbUser;
        $this->dbPwd = $dbPwd;
        $this->logger = $logger;
    }

    public function initImporterByName(string $importerName) : FileImportResultDto
    {
        $mysqli = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName);
        $stmt = $mysqli->prepare('SELECT id FROM importer WHERE name = ?;');
        $stmt->bind_param('s', $importerName);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($result);
        if ($stmt->fetch())
        {
            $this->importer_id = $result;
            return new FileImportResultDto();
        }
        else
        {
            $this->logger->info('No matching importer found based on name');
            return new FileImportResultDto(true, 'No matching importer found');
        }
    }

    public function initImporterByToken(string $token) : FileImportResultDto
    {
        $mysqli = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName);
        $stmt = $mysqli->prepare('SELECT id FROM importer WHERE token = ?;');
        $stmt->bind_param('s', $token);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($result);
        if ($stmt->fetch())
        {
            $this->importer_id = $result;
            return new FileImportResultDto();
        }
        else
        {
            $this->logger->info('No matching importer found based on token');
            return new FileImportResultDto(true, 'No matching importer found');
        }
    }

    public function importResources(string $filePath, bool $testOnly, bool $doNotDelete) : FileImportResultDto
    {
        
        $content = array();
        $hasError = false;
        $i = $deleted = $inserted = 0;
        if (($handle = fopen($filePath, "r")) !== false) 
        {            
            $mysqli = mysqli_connect($this->dbHost, $this->dbUser, $this->dbPwd, $this->dbName);
            mysqli_begin_transaction($mysqli);

            if (! $doNotDelete)
            {
                $this->logger->info('Delete all on import');

                $stmt = $mysqli->prepare('DELETE FROM resource WHERE importer_id = ?;');
                $stmt->bind_param('d', $this->importer_id);
                $stmt->execute();
                $deleted = $mysqli->affected_rows;
                $this->logger->info('deleted ->' . $deleted .' for importer' . strval( $this->importer_id) ) ;
                $stmt->close();
            }

            // Read and process the lines. 
            // Skip the first line if the file includes a header
            while (($line = fgetcsv($handle)) !== false) 
            {
                $lineRes = new ResourceImportResultDto();
                $lineRes->data = implode(',', $line);
                try
                {
                    $grade0 = str_contains($line[3], '0');
                    $grade1 = str_contains($line[3], '1');
                    $grade2 = str_contains($line[3], '2');
                    $grade3 = str_contains($line[3], '3');
                    $grade4 = str_contains($line[3], '4');
                    $grade5 = str_contains($line[3], '5');
                    $grade6 = str_contains($line[3], '6');
                    $grade7 = str_contains($line[3], '7');
                    $grade8 = str_contains($line[3], '8');                    

                    $symbolsArray = array();
                    for($j = 4; $j<count($line); $j++)
                        $symbolsArray[$j-4] = $line[$j];
                    $symbols = implode(',', $symbolsArray);                        

                    $stmt = $mysqli->prepare('CALL curriculum.IMPORT_RESOURCE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @O_COUNT, @O_ERROR);');
                    $stmt->bind_param('ssdsddddddddds', $line[0],  $line[1], $this->importer_id ,$line[2], 
                            $grade0, $grade1, $grade2, $grade3, $grade4, $grade5, $grade6, $grade7, $grade8, $symbols);
                    $stmt->execute();
                    $stmt->bind_result($lineRes->count, $lineRes->error);
                    if($stmt->fetch())
                    {
                        if ($lineRes->count > 0)
                            $inserted +=1;
                        else
                            $hasError = true;    
                    }
                    else
                        $lineRes->error = "Internal: Cannot read query results";
                    $stmt->free_result();
                    $stmt->close();
                }
                catch(mysqli_sql_exception $exception)
                {
                    $hasError = true;
                    $lineRes->error = $exception;
                }                
                $content[$i++] = $lineRes;
            }

            if ($testOnly)// || $hasError)
                $mysqli->rollback();
            else 
                $mysqli->commit();
            fclose($handle);

            $res = new FileImportResultDto($hasError);
            $res->content = $content;
            $res->deleted = $deleted;
            $res->inserted = $inserted;
            return $res;
        }
    }
}