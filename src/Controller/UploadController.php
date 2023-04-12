<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use App\Service\CSVFileToDBImporter;
use App\Model\ImportResourceResult;
use App\Model\FileImportResourceResult;
use App\Model\FileImportRequestDto;
use Psr\Log\LoggerInterface;

class UploadController extends AbstractController
{
    /**
     * @Route("/doUpload", name="do-upload")
     * @param Request $request
     * @param string $uploadDir
     * @param FileUploader $uploader
     * @param LoggerInterface $logger
     * @return Response
     */
    public function index(Request $request, string $uploadDir,
                          FileUploader $uploader, CSVFileToDBImporter $dbImporter, LoggerInterface $logger): Response
    {
        $file = $request->files->get('myfile');
        $inData = new FileImportRequestDto();
        $inData->doNotDelete = $request->get('doNotDelete') == true;
        $inData->testOnly = $request->get('testUpload') == true;
        $inData->importerName = $request->get('importerName');        

        if (empty($file))
        {
            return new Response("No file specified",
               Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }              

        $inData->file = $file->getClientOriginalName();
        $res = $dbImporter->initImporterByName($inData->importerName);
        if (!$res->isError)
        {
            $uploader->upload($uploadDir, $file, $inData->file);
            $res = $dbImporter->importResources($uploadDir . '/' . $inData->file, $inData->testOnly, $inData->doNotDelete);
        }
        $res->setRequest($inData);
      
        return $this->json($res);     
    }
}