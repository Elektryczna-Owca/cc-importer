<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Service\FileUploader;
use App\Service\CSVFileToDBImporter;
use Psr\Log\LoggerInterface;
use App\Model\ImportResourceResult;
use App\Model\FileImportResourceResult;

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
        $token = $request->get("token");
        $file = $request->files->get('myfile');
        $doNotDelete = $request->get('doNotDelete');
        $testUpload = $request->get('testUpload');
        $importerName = $request->get('importerName');        
        if (!$this->isCsrfTokenValid('upload', $token))
        {
            $logger->info("CSRF failure");

            return new Response("Operation not allowed",  Response::HTTP_BAD_REQUEST,
                ['content-type' => 'text/plain']);
        }


        if (empty($file))
        {
            return new Response("No file specified",
               Response::HTTP_UNPROCESSABLE_ENTITY, ['content-type' => 'text/plain']);
        }              

        $res = $dbImporter->initImporterByName($importerName);
        if (!$res->isError)
        {
            $filename = $file->getClientOriginalName();
            $logger->info($filename);
            $uploader->upload($uploadDir, $file, $filename);
            $res = $dbImporter->importResources($uploadDir . '/' . $filename, $testUpload, $doNotDelete);
        }
      
        return $this->json($res);     
    }
}