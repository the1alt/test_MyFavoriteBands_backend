<?php

namespace App\Controller;

use App\Entity\Band;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BandRepository;
use Symfony\Component\HttpFoundation\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/api')]
class BandController extends AbstractController
{
    protected $bandRepository;

    public function __construct(BandRepository $bandRepository)
    {
        $this->bandRepository = $bandRepository;
    } 

    #[Route('/band', name: 'list_bands')]
    public function list(): JsonResponse
    {

        $list = $this->bandRepository->findAll();
        
        return $this->json([
            'data' => $list,
            'message' => 'List of bands',
            'path' => 'src/Controller/BandController.php',
        ]);
    }

    #[Route('/upload-excel', name:'import_bands')]
    /*
    * @param Request $request
    * @throws \Exception
    */
    public function import(Request $request): JsonResponse
    {
        $file = $request->files->get('file'); // get the file from the sent request
        
        $fileFolder = __DIR__ . '/../../public/uploads/';  //choose the folder in which the uploaded file will be stored
        
        $filePathName = md5(uniqid()) . $file->getClientOriginalName(); // apply md5 function to generate an unique identifier for the file and concat it with the file extension  
        
        // Try to save the file
        try {
            $file->move($fileFolder, $filePathName);
        } catch (FileException $e) {
            dd($e);
        }

        $spreadsheet = IOFactory::load($fileFolder . $filePathName); // Here we are able to read from the excel file 
        
        $row = $spreadsheet->getActiveSheet()->removeRow(1); // I added this to be able to remove the first file line 
        
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); // here, the read data is turned into an array

        $bandsArray = []; // Initialize an array to store bands
        // Loop threw the datas to rename columns;
        foreach($sheetData as $bandEl){

            $bandExistant = $this->bandRepository->findOneBy(array('name' => $bandEl['A']));  // make sure that the user does not already exists in your db 
            
            // if no band with same name already exists, 
            if(!$bandExistant){
                $band = New Band();

                $band->setName($bandEl["A"]);
                $band->setOrigin($bandEl["B"]);
                $band->setCity($bandEl["C"]);
                $band->setStart($bandEl["D"]);
                $band->setSplit($bandEl["E"]);
                $band->setFounders($bandEl["F"]);
                $band->setMembersCount($bandEl["G"]);
                $band->setStyle($bandEl["H"]);
                $band->setDescription($bandEl["I"]);
                
                $this->bandRepository->save($band, true);
            }
        }

        return $this->json([
            'message' => 'Import done',
            'path' => 'src/Controller/BandController.php',
        ]);

    }
    


}
