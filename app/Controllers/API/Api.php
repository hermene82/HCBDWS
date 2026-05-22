<?php

namespace App\Controllers\API;

use CodeIgniter\RESTful\ResourceController;
use App\Models\ApiModel;

class Api extends ResourceController
{
    protected $format = 'json';

    public function __construct()
    {
        $this->model = new ApiModel();
    }

    public function index()
    {
        $response = [];

        try {
            $dataP = $this->request->getJSON();

            if (empty($dataP) || empty($dataP->peticion)) {
                return $this->respond([
                    [
                        'errCodigo' => '9001',
                        'errMenssa' => 'Petición vacía o formato inválido',
                        'response'  => null
                    ]
                ], 400);
            }

            log_message('info', 'peticion: request:' . json_encode($dataP));

            foreach ($dataP->peticion as $epet) {
                $ipt = $epet->idPeticion ?? '';

                foreach (($epet->proceso ?? []) as $epro) {
                    $id  = mt_rand(1, 9999999);
                    $ipr = $epro->idProceso ?? '';
                    $ist = $epro->struct ?? '';
                    $res = null;
                    $err = '0';
                    $msj = 'OK';

                    try {
                        switch ($epro->proceso ?? '') {
                            case 'CON':
                                $res = $this->model->consulta($epro->condicion ?? null, $ist);
                                break;

                            case 'ING':
                                $res = $this->model->inserta($epro->data ?? [], $ist);
                                break;

                            case 'ACT':
                                $res = $this->model->modifica($epro->condicion ?? null, $epro->data ?? [], $ist);
                                break;

                            case 'ELI':
                                $res = $this->model->elimina($epro->condicion ?? null, $ist);
                                break;

                            case 'PRO':
                                $res = $this->model->proceso($epro->param ?? [], $ist);
                                break;

                            default:
                                $res = [
                                    'errCodigo' => '9002',
                                    'errMenssa' => 'Proceso no soportado: ' . ($epro->proceso ?? ''),
                                    'respuesta' => null
                                ];
                                break;
                        }

                        $err = $res['errCodigo'] ?? '0';
                        $msj = $res['errMenssa'] ?? 'OK';

                    } catch (\Throwable $e) {
                        $err = '9900';
                        $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

                        $res = [
                            'errCodigo' => $err,
                            'errMenssa' => $msj,
                            'respuesta' => null
                        ];

                        log_message('error', 'peticion: error proceso:' . $msj);
                    }

                    $response[] = [
                        'id'        => $id,
                        'iPeticion' => $ipt,
                        'idProceso' => $ipr,
                        'struct'    => $ist,
                        'errCodigo' => $err,
                        'errMenssa' => $msj,
                        'response'  => $res
                    ];
                }
            }

            log_message('info', 'peticion: response:' . json_encode($response));

            return $this->respond($response);

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error general:' . $msj);

            return $this->respond([
                [
                    'errCodigo' => '9999',
                    'errMenssa' => $msj,
                    'response'  => null
                ]
            ], 500);
        }
    }
}