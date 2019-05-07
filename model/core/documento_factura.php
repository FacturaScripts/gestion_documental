<?php
/**
 * This file is part of FacturaScripts
 * Copyright (C) 2016-2019 Carlos Garcia Gomez <neorazorx@gmail.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
namespace FacturaScripts\model;

/**
 * Realciona un documentos almacenado con una factura, albarán, pedio, etc...
 *
 * @author Carlos Garcia Gomez <neorazorx@gmail.com>
 */
class documento_factura extends \fs_extended_model
{

    /**
     *
     * @var string
     */
    public $fecha;

    /**
     *
     * @var bool
     */
    private $file_exist;

    /**
     *
     * @var string
     */
    public $hora;

    /**
     *
     * @var int
     */
    public $id;

    /**
     *
     * @var int
     */
    public $idalbaran;

    /**
     *
     * @var int
     */
    public $idalbaranprov;

    /**
     *
     * @var int
     */
    public $idfactura;

    /**
     *
     * @var int
     */
    public $idfacturaprov;

    /**
     *
     * @var int
     */
    public $idpedido;

    /**
     *
     * @var int
     */
    public $idpedidoprov;

    /**
     *
     * @var int
     */
    public $idpresupuesto;

    /**
     *
     * @var int
     */
    public $idservicio;

    /**
     *
     * @var string
     */
    public $nombre;

    /**
     *
     * @var string
     */
    public $ruta;

    /**
     *
     * @var int
     */
    public $tamano;

    /**
     *
     * @var string
     */
    public $usuario;

    /**
     * 
     * @param array $data
     */
    public function __construct($data = false)
    {
        parent::__construct('documentosfac', $data);
    }

    /**
     * Devuelve todos los documentos relacionados.
     *
     * @param string $tipo
     * @param int    $id
     *
     * @return \documento_factura
     */
    public function all_from($tipo, $id)
    {
        $lista = [];

        $sql = "SELECT * FROM documentosfac WHERE " . $tipo . " = " . $this->var2str($id) . ";";
        $data = $this->db->select($sql);
        if ($data) {
            foreach ($data as $d) {
                $lista[] = new documento_factura($d);
            }
        }

        return $lista;
    }

    public function clear()
    {
        parent::clear();
        $this->fecha = date('d-m-Y');
        $this->hora = date('h:i:s');
        $this->tamano = 0;
    }

    /**
     * 
     * @return bool
     */
    public function delete()
    {
        if (parent::delete()) {
            @unlink(FS_MYDOCS . $this->ruta);
            return true;
        }

        return false;
    }

    /**
     * 
     * @return bool
     */
    public function file_exists()
    {
        if (!isset($this->file_exist)) {
            $this->file_exist = file_exists(FS_MYDOCS . $this->ruta);
        }

        return $this->file_exist;
    }

    /**
     * 
     * @return bool
     */
    public function is_image()
    {
        $name = mb_strtolower($this->nombre, 'UTF-8');
        if (mb_substr($name, -4) == '.jpg' || mb_substr($name, -5) == '.jpeg') {
            return true;
        } else if (mb_substr($name, -4) == '.png' || mb_substr($name, -4) == '.gif') {
            return true;
        }

        return false;
    }

    /**
     * 
     * @return string
     */
    public function model_class_name()
    {
        return 'documento_factura';
    }

    /**
     * 
     * @return string
     */
    public function primary_column()
    {
        return 'id';
    }

    /**
     * 
     * @param string $file_path
     * @param string $name
     *
     * @return bool
     */
    public function setFile($file_path, $name)
    {
        if (substr(strtolower($name), -4) === '.php') {
            $this->new_error_msg('No puedes subir archivos PHP.');
            return false;
        }

        $nuevon = $this->random_string(6) . '_' . $name;
        if (copy($file_path, FS_MYDOCS . 'documentos/' . $nuevon)) {
            $this->nombre = $name;
            $this->ruta = 'documentos/' . $nuevon;
            $this->tamano = filesize(getcwd() . '/' . FS_MYDOCS . $this->ruta);
            return true;
        }

        return false;
    }

    /**
     * Converts bytes into human readable file size.
     *
     * @param string $bytes
     *
     * @return string
     */
    public function tamano()
    {
        $bytes = floatval($this->tamano);
        $arBytes = array(
            0 => array(
                "UNIT" => "TB",
                "VALUE" => pow(1024, 4)
            ),
            1 => array(
                "UNIT" => "GB",
                "VALUE" => pow(1024, 3)
            ),
            2 => array(
                "UNIT" => "MB",
                "VALUE" => pow(1024, 2)
            ),
            3 => array(
                "UNIT" => "KB",
                "VALUE" => 1024
            ),
            4 => array(
                "UNIT" => "B",
                "VALUE" => 1
            ),
        );

        foreach ($arBytes as $arItem) {
            if ($bytes >= $arItem["VALUE"]) {
                $result = $bytes / $arItem["VALUE"];
                $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
                break;
            }
        }
        return $result;
    }
}
