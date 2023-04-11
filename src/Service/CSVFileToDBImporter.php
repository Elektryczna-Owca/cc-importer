<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Psr\Log\LoggerInterface;
use App\Model\FileImportResultDto;
use App\Model\ResourceImportResultDto;

class CSVFileToDBImporter
{
    private $logger;
    private $importer_id = -1;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function initImporterByName($importerName) : FileImportResultDto
    {
        $mysqli = mysqli_connect("localhost", "root", "Marchewka", "curriculum");
        $stmt = $mysqli->prepare('SELECT id FROM importer WHERE name = ?;');
        $stmt->bind_param('s', $importerName);
        $stmt->execute();
        $stmt->store_result();
        $stmt->bind_result($result);
        $content = array();
        if ($stmt->fetch())
        {
            $importer_id = $result;
            $this->logger->info('Importer found');
            return FileImportResultDto::of ($content);
        }
        else
        {
            $this->logger->info('No matching importer found');
            return FileImportResultDto::of ($content, true, 'No matching importer found');
        }
    }

    public function importResources($filePath, $testOnly, $deleteAll) : FileImportResultDto
    {
        if ($testOnly)
            $this->logger->info('Test only');
        if ($deleteAll)
            $this->logger->info('Delete all');
        $content = array();
        $i = 0;
        $hasError = false;
        if (($handle = fopen($filePath, "r")) !== false) 
        {            
            $mysqli = mysqli_connect("localhost", "root", "Marchewka", "curriculum");
            mysqli_begin_transaction($mysqli);

            if ($deleteAll)
            {
                $stmt = $mysqli->prepare('DELETE FROM resource WHERE importer_id = ?;');
                $stmt->bind_param('d', $importer_id);
                $stmt->execute();
                $this->logger->info('delete' . $mysqli->affected_rows);
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
                    $stmt->bind_param('ssdsddddddddds', $data[0],  $data[1], $importer_id ,$data[2], 
                            $grade0, $grade1, $grade2, $grade3, $grade4, $grade5, $grade6, $grade7, $grade8, $symbols);
                    $stmt->execute();
                    $stmt->store_result();
                    $stmt->bind_result($result1, $result2);
                    if($stmt->fetch())
                    {
                        $lineRes->count = $result1;
                        $lineRes->error = $result2;
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


            if ($testOnly || $hasError)
                $mysqli->rollback();
            else 
                $mysqli->commit();
            fclose($handle);
            return FileImportResultDto::of ($content, $testOnly || $hasError);
        }
    }
}