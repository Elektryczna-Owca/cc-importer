<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use App\Model\FileImportResultDto;
use App\Model\ResourceImportResultDto;
use App\Model\FileImportResultExtDto;

class CSVFileToDBImporter
{
    private $logger;
    private $importer_id = -1;
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPwd;

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
            $this->logger->info('Importer found'  );
            return new FileImportResultDto();
        }
        else
        {
            $this->logger->info('No matching importer found');
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
            $this->logger->info('Importer found');
            return new FileImportResultDto();
        }
        else
        {
            $this->logger->info('No matching importer found');
            return new FileImportResultDto(true, 'No matching importer found');
        }
    }

    public function importResources(string $filePath, bool $testOnly, bool $doNotDelete) : FileImportResultExtDto
    {
        
        $content = array();
        $i = 0;
        $hasError = false;
        $deleted = 0;
        $inserted = 0;
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
            while (($data = fgetcsv($handle)) !== false) 
            {
                $lineRes = new ResourceImportResultDto();
                $lineRes->data = $data[0] . ',' . $data[1] . ','. $data[2] ;
                try
                {
                    $stmt = $mysqli->prepare('CALL curriculum.IMPORT_RESOURCE(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @O_COUNT, @O_ERROR);');
                    $grade0 = str_contains($data[3], '0');
                    $grade1 = str_contains($data[3], '1');
                    $grade2 = str_contains($data[3], '2');
                    $grade3 = str_contains($data[3], '3');
                    $grade4 = str_contains($data[3], '4');
                    $grade5 = str_contains($data[3], '5');
                    $grade6 = str_contains($data[3], '6');
                    $grade7 = str_contains($data[3], '7');
                    $grade8 = str_contains($data[3], '8');
                    $symbols = $data[4] .','. $data[5];                        
                    $stmt->bind_param('ssdsddddddddds', $data[0],  $data[1], $this->importer_id ,$data[2], 
                            $grade0, $grade1, $grade2, $grade3, $grade4, $grade5, $grade6, $grade7, $grade8, $symbols);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($result1, $result2);
                    if($stmt->fetch())
                    {
                        $lineRes->count = $result1;
                        $lineRes->error = $result2;
                        if ($lineRes->count > 0)
                            $inserted +=1;
                        else
                            $hasError = true;    
                        $this->logger->info($result1);
                        $this->logger->info($result2);
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
            return new FileImportResultExtDto($content, $deleted, $inserted, $hasError);
        }
    }
}