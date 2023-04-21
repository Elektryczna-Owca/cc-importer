<?php

namespace App\Controller;

use App\Model\FileImportResultDto;
use App\Model\UploadRequest;
use App\Form\FileImportResultDtoType;
use App\Form\UploadRequestType;
use App\Service\FileUploader;
use App\Service\CSVFileToDBImporter;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Psr\Log\LoggerInterface;

class UploadController extends AbstractController
{
    #[Route('/upload', name: 'app_upload')]
    public function index(Request $request, string $uploadDir,
            FileUploader $uploader, CSVFileToDBImporter $dbImporter, LoggerInterface $logger): Response
    {
        $task = new UploadRequest();     
        $form = $this->createForm(UploadRequestType::class, $task);

        $form->handleRequest($request);
        if ($form->isSubmitted()) {            
            $file = $form['file']->getData();


            $res = new FileImportResultDto(true, 'Unknown');

            if (empty($file))
            {
                $res = new FileImportResultDto(true, 'No file specified');
            }    
            else
            {
                try
                {
                    $res = $dbImporter->initImporterByName($task->importer->getName());
                    if (!$res->isError)
                    {
                        $uploader->upload($uploadDir, $file, $file->getClientOriginalName());
                        $res = $dbImporter->importResources($uploadDir . '/' . $file->getClientOriginalName(), $task->testOnly, $task->doNotDelete);
                    }
                }
                catch(Excectpion $e)
                {
                    $res = new FileImportResultDto(true, 'Internal error' . $e->getMessage());
                }    
            }

            $res->request = $task;   
            
            //return $this->json($res);     

            $resForm = $this->createForm(FileImportResultDtoType::class, $res);   
            return $this->render('upload/result.html.twig', [
                'form' => $resForm->createView(),
            ]);  
            
        }

        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }    
}