<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ApiModel;
class Api extends ResourceController
{
    //protected $modelName = 'App\Models\ApiModel';
    protected $format    = 'json';

    public function __construct() {
        $this->model = $this->setmodel(new ApiModel());
    }
   
    public function index()
    {
        
        //$dataP = json_decode(file_get_contents('php://input'));
        $dataP = $this->request->getJSON();
  
        $ipt = '';
        $ipr = '';

        if(isset($dataP)){   
        if(!empty($dataP)){ 
            
            log_message('info','peticion: request:'.json_encode($dataP));

            foreach($dataP->peticion as $pets => $epet) {
                $ipt = $epet->idPeticion;

                foreach($epet->proceso as $pros => $epro) {
                  
                    try{

                    $id  = mt_rand(1,9999999);    
                    $err = '0';
                    $msj = '';
                    $res = '';
                    $ipr = $epro->idProceso;
                    $ist = $epro->struct;

                    if ($epro->proceso == 'CON'){   
                        $res = $this->model->consulta($epro->condicion,$ist);
                        $err = $res["errCodigo"];
                        
                    }
                    
                    if ($epro->proceso == 'ING'){
                        $res = $this->model->inserta($epro->data,$epro->struct);
                        $err = $res["errCodigo"];
                    }
                    
                    if ($epro->proceso == 'ACT'){
                        $res = $this->model->modifica($epro->condicion,$epro->data,$epro->struct);
                        $err = $res["errCodigo"];
                    }
                    
                    if ($epro->proceso == 'ELI'){
                        $res = $this->model->elimina($epro->condicion,$epro->struct);
                        $err = $res["errCodigo"];
                    }

                    if ($epro->proceso == 'PRO'){
                        $res = $this->model->proceso($epro->param,$epro->struct);
                        $err = $res["errCodigo"];
                    }


                    $response[] = array(
                        "id" => $id,
                        "iPeticion" => $ipt,
                        "idProceso" => $ipr,
                        "struct" => $ist,
                        "errCodigo" => $err,
                        "errMenssa" => $msj,
                        "response" => $res			
                        );

                    }catch(\Exception $e ){

                        $err = '9900';
                        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
                         
                        $response[] = array(
                        "id" => $id,
                        "iPeticion" => $ipt,
                        "idProceso" => $ipr,
                        "struct" => $ist,
                        "errCodigo" => $err,
                        "errMenssa" => $msj,
                        "response" => $res			
                        );

                        log_message( 'error', 'peticion: error:'.json_encode($response).':: mesage error:'.$msj );
                    }         
                }
            }      
        }}
        
        log_message('info','peticion: response:'.json_encode($response));
        return $this->respond($response);
         
    }

    // ...
}