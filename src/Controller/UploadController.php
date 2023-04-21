<?php

namespace App\Controller;

use App\Model\FileImportResultDto;
use App\Model\UploadRequest;
use App\Model\UploadWithImporterRequest;
use App\Form\FileImportResultDtoType;
use App\Form\UploadRequestType;
use App\Form\UploadWithImporterRequestType;
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
        $token = $request->get('token');

        if (empty($token))
        {
            $task = new UploadWithImporterRequest();     
            $form = $this->createForm(UploadWithImporterRequestType::class, $task);
        }
        else
        {
            $task = new UploadRequest();
            $form = $this->createForm(UploadRequestType::class, $task);            
        }

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
                    if (empty($token))
                        $res = $dbImporter->initImporterByName($task->importer->getName());                        
                    else
                        $res = $dbImporter->initImporterByName($token);

                    if (!$res->isError)
                    {
                        $logger->info('Importing ....');
                        $uploader->upload($uploadDir, $file, $file->getClientOriginalName());
                        $res = $dbImporter->importResources($uploadDir . '/' . $file->getClientOriginalName(), $task->testOnly, $task->doNotDelete);
                    }
                    $logger->info($res->error);
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

        if (empty($token))
            return $this->render('upload/indexWithImporter.html.twig', [
                'form' => $form->createView(),
            ]);
        else
            return $this->render('upload/index.html.twig', [
                'form' => $form->createView(),
            ]);
    }    
}