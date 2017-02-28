<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2016  Carlos Garcia Gomez  neorazorx@gmail.com
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
 * @author carlos
 */

class documento_factura extends \fs_model
{
   public $id;
   public $ruta;
   public $nombre;
   public $fecha;
   public $hora;
   public $tamano;
   public $usuario;
   public $idfactura;
   public $idalbaran;
   public $idpedido;
   public $idpresupuesto;
   public $idservicio;
   public $idfacturaprov;
   public $idalbaranprov;
   public $idpedidoprov;
   
   private $file_exist;
   
   public function __construct($d = FALSE)
   {
      parent::__construct('documentosfac');
      if($d)
      {
         $this->id = $this->intval($d['id']);
         $this->ruta = $d['ruta'];
         $this->nombre = $d['nombre'];
         $this->fecha = date('d-m-Y', strtotime($d['fecha']));
         $this->hora = date('h:i:s', strtotime($d['hora']));
         $this->tamano = intval($d['tamano']);
         $this->usuario = $d['usuario'];
         $this->idfactura = $this->intval($d['idfactura']);
         $this->idalbaran = $this->intval($d['idalbaran']);
         $this->idpedido = $this->intval($d['idpedido']);
         $this->idpresupuesto = $this->intval($d['idpresupuesto']);
         $this->idservicio = $this->intval($d['idservicio']);
         $this->idfacturaprov = $this->intval($d['idfacturaprov']);
         $this->idalbaranprov = $this->intval($d['idalbaranprov']);
         $this->idpedidoprov = $this->intval($d['idpedidoprov']);
      }
      else
      {
         $this->id = NULL;
         $this->ruta = NULL;
         $this->nombre = NULL;
         $this->fecha = date('d-m-Y');
         $this->hora = date('h:i:s');
         $this->tamano = 0;
         $this->usuario = NULL;
         $this->idfactura = NULL;
         $this->idalbaran = NULL;
         $this->idpedido = NULL;
         $this->idpresupuesto = NULL;
         $this->idservicio = NULL;
         $this->idfacturaprov = NULL;
         $this->idalbaranprov = NULL;
         $this->idpedidoprov = NULL;
      }
   }
   
   protected function install()
   {
      return '';
   }
   
   public function file_exists()
   {
      if( !isset($this->file_exist) )
      {
         $this->file_exist = file_exists(FS_MYDOCS.$this->ruta);
      }
      
      return $this->file_exist;
   }
   
   /**
    * Converts bytes into human readable file size.
    *
    * @param string $bytes
    * @return string human readable file size (2,87 Мб)
    * @author Mogilev Arseny
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

      foreach($arBytes as $arItem)
      {
         if($bytes >= $arItem["VALUE"])
         {
            $result = $bytes / $arItem["VALUE"];
            $result = str_replace(".", ",", strval(round($result, 2))) . " " . $arItem["UNIT"];
            break;
         }
      }
      return $result;
   }
   
   public function get($id)
   {
      $data = $this->db->select("SELECT * FROM documentosfac WHERE id = ".$this->var2str($id).";");
      if($data)
      {
         return new documento_factura($data[0]);
      }
      else
         return FALSE;
   }
   
   public function exists()
   {
      if( is_null($this->id) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM documentosfac WHERE id = ".$this->var2str($this->id).";");
   }
   
   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE documentosfac SET ruta = ".$this->var2str($this->ruta)
                 .", nombre = ".$this->var2str($this->nombre)
                 .", fecha = ".$this->var2str($this->fecha)
                 .", hora = ".$this->var2str($this->hora)
                 .", tamano = ".$this->var2str($this->tamano)
                 .", usuario = ".$this->var2str($this->usuario)
                 .", idfactura = ".$this->var2str($this->idfactura)
                 .", idalbaran = ".$this->var2str($this->idalbaran)
                 .", idpedido = ".$this->var2str($this->idpedido)
                 .", idpresupuesto = ".$this->var2str($this->idpresupuesto)
                 .", idservicio = ".$this->var2str($this->idservicio)
                 .", idfacturaprov = ".$this->var2str($this->idfacturaprov)
                 .", idalbaranprov = ".$this->var2str($this->idalbaranprov)
                 .", idpedidoprov = ".$this->var2str($this->idpedidoprov)
                 ."  WHERE id = ".$this->var2str($this->id).";";
         
         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO documentosfac (ruta,nombre,fecha,hora,tamano,usuario,"
                 . "idfactura,idalbaran,idpedido,idpresupuesto,idservicio,idfacturaprov,"
                 . "idalbaranprov,idpedidoprov) VALUES (".$this->var2str($this->ruta)
                 . ",".$this->var2str($this->nombre)
                 . ",".$this->var2str($this->fecha)
                 . ",".$this->var2str($this->hora)
                 . ",".$this->var2str($this->tamano)
                 . ",".$this->var2str($this->usuario)
                 . ",".$this->var2str($this->idfactura)
                 . ",".$this->var2str($this->idalbaran)
                 . ",".$this->var2str($this->idpedido)
                 . ",".$this->var2str($this->idpresupuesto)
                 . ",".$this->var2str($this->idservicio)
                 . ",".$this->var2str($this->idfacturaprov)
                 . ",".$this->var2str($this->idalbaranprov)
                 . ",".$this->var2str($this->idpedidoprov).");";
         
         if( $this->db->exec($sql) )
         {
            $this->id = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }
   
   public function delete()
   {
      return $this->db->exec("DELETE FROM documentosfac WHERE id = ".$this->var2str($this->id).";");
   }
   
   /**
    * Devuelve todos los documentos relacionados.
    * @param type $tipo
    * @param type $id
    * @return \documento_factura
    */
   public function all_from($tipo, $id)
   {
      $lista = array();
      $sql = "SELECT * FROM documentosfac WHERE ".$tipo." = ".$this->var2str($id).";";
      
      $data = $this->db->select($sql);
      if($data)
      {
         foreach($data as $d)
         {
            $lista[] = new documento_factura($d);
         }
      }
      
      return $lista;
   }
}
