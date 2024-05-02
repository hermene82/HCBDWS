<?php

namespace App\Models;
use CodeIgniter\Model;

class ApiModel extends Model
{
    
    public function __construct() {
        parent::__construct();
    }    

    public function consulta($condicion,$tabla){ 

        //echo print_r($condicion[0]->where,true);

        $db = \Config\Database::connect();

        try{

            $err = '0';
            $msj = '';
            $res = '';
        
        $builder = $db->table($tabla);   

        $condicionAv = json_decode(json_encode($condicion), true);

        if (is_array($condicionAv)) {

        $condicionsA = json_decode(json_encode($condicion->select), true);
        $condicionA = json_decode(json_encode($condicion->where), true);
        $condicioninA = json_decode(json_encode($condicion->wherein), true);    
        $condicionjoinA = json_decode(json_encode($condicion->join), true);
        
        $condicionlikeA = '';
        if (isset($condicion->like)){
            $condicionlikeA = json_decode(json_encode($condicion->like), true); 
        }
        
        
        //echo print_r($condicionA,true);
        //echo print_r($condicioninA,true);
        //echo print_r($condicionjoinA,true);
        
        if (strlen($condicionsA) > 0 ) {
            $builder->select($condicionsA);
        }

        if (is_array($condicionA)) {
            $builder->where($condicionA);
        }

        if (is_array($condicioninA)) {
            foreach($condicion->wherein as $rins => $rin) {
                $campoin = json_decode(json_encode($rin->campo), true);
                $datosin = json_decode(json_encode($rin->datos), true);
                
                $builder->whereIn($campoin,$datosin);
            }
        }
        
        if (is_array($condicionjoinA)) {

            //echo print_r($condicionjoinA,true);

            foreach($condicion->join as $rjoins => $rjoin) {       
                $builder->join($rjoin->join,$rjoin->on,$rjoin->type);
                //$builder->join("admin.dlista as b","a.lista = b.lista and a.estado = 'A'","inner");
            }    
        }
        
        if (is_array($condicionlikeA)) {
            $builder->like($condicionlikeA);
        }

        }
        //echo "aqui con";         
        $query  = $builder->get();
        //echo "des aqui con";
        
        
        if( !$query ){
            $err = '9910';
            $msj = $db->error();
            log_message( 'error','peticion: error:'.$this->mapped_implode(" |",$msj,':'));
        } else {
        $msj = 'OK';
        $res = $query->getResult();
        }

        $db->close();
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;

    }catch(\Exception $e ){
        
        $db->close();
        $err = '9911';
        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        log_message( 'error', 'peticion: error:'.$msj );
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;
        
    }        
    }

    public function inserta($data,$tabla){
        $db = \Config\Database::connect();
    
        try{

        $err = '0';
        $msj = '';
        $res = '';
        
        $builder = $db->table($tabla);

        $res = $builder->upsertBatch($data);
        $db->close();
    
        $msj = 'OK';

        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;

    }catch(\Exception $e ){
        
        $db->close();
        $err = '9902';
        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        log_message( 'error', 'peticion: error:'.$msj );
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;
        
    }
      
    }

    public function modifica($condicion,$data,$tabla){
        $db = \Config\Database::connect();
        try{

            $err = '0';
            $msj = '';
            $res = '';
    
        $builder = $db->table($tabla);

        $condicionAv = json_decode(json_encode($condicion), true);

        if (is_array($condicionAv)) {

            $condicionA = json_decode(json_encode($condicion->where), true);

            if (is_array($condicionA)) {
                $builder->where($condicionA);
            }        
        }


        $res = $builder->update($data);
        $db->close();

        $msj = 'OK';

        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  

		return $respuesta;

    }catch(\Exception $e ){
        
        $db->close();
        $err = '9903';
        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        log_message( 'error', 'peticion: error:'.$msj );
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;
        
    }
        
    }
    
    public function proceso($param,$procedure){
        $db = \Config\Database::connect();
        try{

            $err = '0';
            $msj = '';
            $res = '';
        
            $jparam =json_encode($param);
            //echo print_r($jparam,true);    

        $sql = "CALL ". $procedure ."('" . $jparam . "')"; 
        //echo print_r($sql,true);
        $resul = $db->query($sql);

        if( $resul ){  
           $res =$resul->getResult();
           $msj = 'OK';
        }else{
            $err = '9950';
            $msj = $db->error();
            log_message( 'error','peticion: error:'.$this->mapped_implode(" |",$msj,':'));
        }

        $db->close();

        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  

        return $respuesta;

    }catch(\Exception $e ){
        
        $db->close();
        $err = '9904';
        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        log_message( 'error', 'peticion: error:'.$msj );
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;
        
    }

    }

    public function elimina($condicion,$tabla){
        $db = \Config\Database::connect();
        try{

            $err = '0';
            $msj = '';
            $res = '';
       
        $builder = $db->table($tabla);

        $condicionAv = json_decode(json_encode($condicion), true);

        if (is_array($condicionAv)) {

            $condicionA = json_decode(json_encode($condicion->where), true);

            if (is_array($condicionA)) {
                $builder->where($condicionA);
            }        
        }

        $res = $builder->delete();
        $db->close();

        $msj = 'OK';

        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  

        return $respuesta;

    }catch(\Exception $e ){
        
        $db->close();
        $err = '9904';
        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();
        log_message( 'error', 'peticion: error:'.$msj );
        
        $respuesta = array("errCodigo" => $err, "errMenssa" => $msj, "respuesta" => $res );  
		return $respuesta;
        
    }

    }

    function mapped_implode($glue, $array, $symbol = '=') {
        return implode($glue, array_map(
                function($k, $v) use($symbol) { 
                    return $k . $symbol . $v;
                }, 
                array_keys($array), 
                array_values($array)
                )
            );
    }
    
}