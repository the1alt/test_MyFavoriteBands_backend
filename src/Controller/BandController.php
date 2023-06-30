<?php

namespace App\Controller;

use App\Entity\Band;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\BandRepository;
use PhpOffice\PhpSpreadsheet\IOFactory;

#[Route('/api/band')]
class BandController extends AbstractController
{
    protected $bandRepository;

    public function __construct(BandRepository $bandRepository)
    {
        $this->bandRepository = $bandRepository;
    } 

    /**
     * List bands
     *
     * @return JsonResponse
     */
    #[Route('', name: 'list_bands', methods:["GET"])]
    public function list(): JsonResponse
    {

        // find all bands
        $list = $this->bandRepository->findAll(); 
        
        return $this->json(
            $list,
            headers: ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Get Band from Id
     * @param [integer] $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'read_band', methods:["GET"])]
    public function read($id): JsonResponse
    {
        // find band from $id;
        $band = $this->bandRepository->find($id); 

        // If band not found return 404
        if(!$band){
            return $this->json('No band found for id '.$id, 404, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        return $this->json(
            $band,
            headers: ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }
    


    #[Route('', name: 'create_band', methods:["POST"])]
    /**
     * Create Band
     * @param Request $request
     * @bodyParam name string required
     * @bodyParam origin string optional nullable
     * @bodyParam city string optional nullable
     * @bodyParam start integer optional nullable
     * @bodyParam split integer optional nullable
     * @bodyParam founders string optional nullable
     * @bodyParam members_count integer optional nullable
     * @bodyParam style string optional nullable
     * @bodyParam description text optional nullable
     * @return JsonResponse
     */
    public function create(Request $request): JsonResponse
    {

        // make sure that the band does not already exists in the db 
        $bandExistant = $this->bandRepository->findOneBy(array('name' => $request->request->get('name')));  

        // if a band with the same name already exists, return error 400
        if($bandExistant){
            return $this->json(
                'A band with the same name already exists',
                400,
                headers: ['Content-Type' => 'application/json;charset=UTF-8']
            );
        }

        // Create Band
        $band = new Band();
        $band->setName($request->request->get('name'));
        $band->setOrigin($request->request->get('origin'));
        $band->setCity($request->request->get('city'));
        $band->setStart($request->request->get('start'));
        $band->setSplit($request->request->get('split'));
        $band->setFounders($request->request->get('founders'));
        $band->setMembersCount($request->request->get('members_count'));
        $band->setStyle($request->request->get('style'));
        $band->setDescription($request->request->get('description'));
        $this->bandRepository->save($band, true);

        return $this->json(
            $band,
            headers: ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Update Band
     *
     * @param [integer] $id
     * @param Request $request
     * @bodyParam name string required
     * @bodyParam origin string optional nullable
     * @bodyParam city string optional nullable
     * @bodyParam start integer optional nullable
     * @bodyParam split integer optional nullable
     * @bodyParam founders string optional nullable
     * @bodyParam members_count integer optional nullable
     * @bodyParam style string optional nullable
     * @bodyParam description text optional nullable
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'update_band', methods:["PUT"])]
    public function update($id, Request $request): JsonResponse
    {
        // find band from $id;
        $band = $this->bandRepository->find($id);

        // If band not found return 404
        if(!$band){
            return $this->json('No band found for id '.$id, 404, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }
        
        // Update fields sent
        if($request->request->get('name'))
            $band->setName($request->request->get('name'));
        if($request->request->get('origin'))
            $band->setOrigin($request->request->get('origin'));
        if($request->request->get('city'))
            $band->setCity($request->request->get('city'));
        if($request->request->get('start'))
            $band->setStart($request->request->get('start'));
        if($request->request->get('split'))
            $band->setSplit($request->request->get('split'));
        if($request->request->get('founders'))
            $band->setFounders($request->request->get('founders'));
        if($request->request->get('members_count'))
            $band->setMembersCount($request->request->get('members_count'));
        if($request->request->get('style'))
            $band->setStyle($request->request->get('style'));
        if($request->request->get('description'))
            $band->setDescription($request->request->get('description'));

        $this->bandRepository->save($band, true);
        
        return $this->json(
            $band,
            headers: ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Delete Band
     *
     * @param [integer] $id
     * @return JsonResponse
     */
    #[Route('/{id}', name: 'delete_band', methods:["DELETE"])]
    public function delete($id): JsonResponse
    {

        // find band from $id;
        $band = $this->bandRepository->find($id);

        // If band not found return 404
        if(!$band){
            return $this->json('No band found for id '.$id, 404, headers: ['Content-Type' => 'application/json;charset=UTF-8']);
        }

        $this->bandRepository->remove($band, true);
        
        return $this->json(
            'The band has succesfully been deleted',
            headers: ['Content-Type' => 'application/json;charset=UTF-8']
        );
    }

    /**
     * Import Excel
     * Import bands data from a xslx file
     *
     * @param [file] file
     * @return JsonResponse
     */
    #[Route('/import', name:'import_bands', methods:["POST"])]
    public function import(Request $request): JsonResponse
    {
        // get the file from the sent request
        $file = $request->files->get('file'); 
        
        // choose the folder in which the uploaded file will be stored
        $fileFolder = __DIR__ . '/../../public/uploads/';  
        
        // apply md5 function to generate an unique identifier for the file and concat it with the file extension  
        $filePathName = md5(uniqid()) . $file->getClientOriginalName(); 
        
        // Try to save the file
        try {
            $file->move($fileFolder, $filePathName);
        } catch (FileException $e) {
            dd($e);
        }

        // Here we are able to read from the excel file 
        $spreadsheet = IOFactory::load($fileFolder . $filePathName); 
        
        // I added this to be able to remove the first file line 
        $row = $spreadsheet->getActiveSheet()->removeRow(1); 
        
        // here, the read data is turned into an array
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true); 

        // Loop threw the datas to rename columns;
        foreach($sheetData as $bandEl){

            // make sure that the band does not already exists in the db 
            $bandExistant = $this->bandRepository->findOneBy(array('name' => $bandEl['A']));  
            
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
