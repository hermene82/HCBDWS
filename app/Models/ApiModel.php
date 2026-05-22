<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiModel extends Model
{
    public function consulta($condicion, $tabla)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table($tabla);

            if (!empty($condicion)) {

                if (!empty($condicion->select)) {
                    $builder->select($condicion->select);
                }

                if (!empty($condicion->where)) {
                    $builder->where($this->toArray($condicion->where));
                }

                if (!empty($condicion->wherein)) {
                    foreach ($condicion->wherein as $rin) {
                        $builder->whereIn($rin->campo, $rin->datos);
                    }
                }

                if (!empty($condicion->join)) {
                    foreach ($condicion->join as $rjoin) {
                        $builder->join(
                            $rjoin->join,
                            $rjoin->on,
                            $rjoin->type ?? 'inner'
                        );
                    }
                }

                if (!empty($condicion->like)) {
                    $builder->like($this->toArray($condicion->like));
                }

                if (!empty($condicion->groupby)) {
                    foreach ($condicion->groupby as $group) {
                        $builder->groupBy($group);
                    }
                }

                if (!empty($condicion->orderby)) {
                    foreach ($condicion->orderby as $order) {
                        $campo = $order->campo ?? '';
                        $tipo  = $order->tipo ?? 'ASC';

                        if ($campo !== '') {
                            $builder->orderBy($campo, $tipo);
                        }
                    }
                }

                if (!empty($condicion->limit)) {
                    $builder->limit((int)$condicion->limit);
                }
            }

            $query = $builder->get();

            return [
                'errCodigo' => '0',
                'errMenssa' => 'OK',
                'respuesta' => $query->getResult()
            ];

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error consulta:' . $msj);

            return [
                'errCodigo' => '9911',
                'errMenssa' => $msj,
                'respuesta' => null
            ];

        } finally {
            $db->close();
        }
    }

    public function inserta($data, $tabla)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table($tabla);

            if (empty($data)) {
                return [
                    'errCodigo' => '9901',
                    'errMenssa' => 'No existen datos para insertar',
                    'respuesta' => null
                ];
            }

            $res = $builder->upsertBatch($this->toArray($data));

            return [
                'errCodigo' => '0',
                'errMenssa' => 'OK',
                'respuesta' => $res
            ];

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error inserta:' . $msj);

            return [
                'errCodigo' => '9902',
                'errMenssa' => $msj,
                'respuesta' => null
            ];

        } finally {
            $db->close();
        }
    }

    public function modifica($condicion, $data, $tabla)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table($tabla);

            if (!empty($condicion) && !empty($condicion->where)) {
                $builder->where($this->toArray($condicion->where));
            } else {
                return [
                    'errCodigo' => '9905',
                    'errMenssa' => 'No existe condición WHERE para actualizar',
                    'respuesta' => null
                ];
            }

            if (empty($data)) {
                return [
                    'errCodigo' => '9906',
                    'errMenssa' => 'No existen datos para actualizar',
                    'respuesta' => null
                ];
            }

            $res = $builder->update($this->toArray($data));

            return [
                'errCodigo' => '0',
                'errMenssa' => 'OK',
                'respuesta' => $res
            ];

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error modifica:' . $msj);

            return [
                'errCodigo' => '9903',
                'errMenssa' => $msj,
                'respuesta' => null
            ];

        } finally {
            $db->close();
        }
    }

    public function elimina($condicion, $tabla)
    {
        $db = \Config\Database::connect();

        try {
            $builder = $db->table($tabla);

            if (!empty($condicion) && !empty($condicion->where)) {
                $builder->where($this->toArray($condicion->where));
            } else {
                return [
                    'errCodigo' => '9907',
                    'errMenssa' => 'No existe condición WHERE para eliminar',
                    'respuesta' => null
                ];
            }

            $res = $builder->delete();

            return [
                'errCodigo' => '0',
                'errMenssa' => 'OK',
                'respuesta' => $res
            ];

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error elimina:' . $msj);

            return [
                'errCodigo' => '9904',
                'errMenssa' => $msj,
                'respuesta' => null
            ];

        } finally {
            $db->close();
        }
    }

    public function proceso($param, $procedure)
    {
        $db = \Config\Database::connect();

        try {
            $jparam = json_encode($param, JSON_UNESCAPED_UNICODE);

            log_message('info', 'peticion: proceso CALL ' . $procedure . ' param:' . $jparam);

            $query = $db->query("CALL {$procedure}(?)", [$jparam]);

            return [
                'errCodigo' => '0',
                'errMenssa' => 'OK',
                'respuesta' => $query ? $query->getResult() : null
            ];

        } catch (\Throwable $e) {
            $msj = $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine();

            log_message('error', 'peticion: error proceso:' . $msj);

            return [
                'errCodigo' => '9950',
                'errMenssa' => $msj,
                'respuesta' => null
            ];

        } finally {
            $db->close();
        }
    }

    private function toArray($data): array
    {
        return json_decode(json_encode($data), true) ?? [];
    }
}