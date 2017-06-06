<?php

/*
 * This file is part of FacturaScripts
 * Copyright (C) 2017    Luis Miguel Pérez Romero  luismipr@gmail.com
 * Copyright (C) 2017    Carlos Garcia Gomez       neorazorx@gmail.com
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

require_model('cliente.php');
require_model('proveedor.php');
require_model('documento_factura.php');
require_model('albaran_cliente.php');
require_model('albaran_proveedor.php');
require_model('documento_factura.php');
require_model('factura_cliente.php');
require_model('factura_proveedor.php');
require_model('pedido_cliente.php');
require_model('pedido_proveedor.php');
require_model('presupuesto_cliente.php');
require_model('servicio_cliente.php');

require_once 'plugins/plantillas_pdf/extra/plantillas_setup.php';

class informes_documentos extends fs_controller
{
   public $mostrar;
   public $cliente;
   public $proveedor;
   public $desde;
   public $hasta;
   public $tipo;
   public $resultados;
   public $totalresultados;
   public $idtipo;
   public $documento;
   public $b_url;
   public $offset;
   public $numresultados;
   public $adj;

   private $plantilla;
   private $pdf_setup;

   public function __construct()
   {
      parent::__construct(__CLASS__, 'Gestión documental', 'informes');
   }

   protected function private_core()
   {
      if( isset($_REQUEST['buscar_cliente']) )
      {
         $this->buscar_cliente();
      }
      else if( isset($_REQUEST['buscar_proveedor']) )
      {
         $this->buscar_proveedor();
      }
      else
      {
         $this->mostrar = 'ventas';
         if(isset($_REQUEST['mostrar']))
         {
            $this->mostrar = $_REQUEST['mostrar'];
         }
         
         $this->desde = '';
         if( isset($_REQUEST['desde']))
         {
            $this->desde = $_REQUEST['desde'];
         }
         
         $this->hasta = '';
         if( isset($_REQUEST['hasta']))
         {
            $this->hasta = $_REQUEST['hasta'];
         }

         $this->adj = '0';
         if( isset($_REQUEST['b_adjunto']))
         {
            $this->adj = $_REQUEST['b_adjunto'];
         }
         
         $this->offset = 0;
         if(isset($_REQUEST['offset']))
         {
            $this->offset = $_REQUEST['offset'];
         }

         if (isset($_REQUEST['detalle']))
         {
            $this->generar_detalle();
         }
         
         $this->cliente = new cliente();
         $this->proveedor = new proveedor();
         if( isset($_REQUEST['codcliente']) && $_REQUEST['codcliente'] != '')
         {
            $cli0 = new cliente();
            $this->cliente = $cli0->get($_REQUEST['codcliente']);
         }
         else if( isset($_REQUEST['codproveedor']) && $_REQUEST['codproveedor'] != '')
         {
            $pro0 = new proveedor();
            $this->proveedor = $pro0->get($_REQUEST['codproveedor']);
         }
         
         if ($this->mostrar == 'compras')
         {
            $this->tipo = 'facturasprov';
            $this->idtipo = 'idfactura';
            $this->documento = 'factura_proveedor';
         } else
         {
            $this->tipo = 'facturascli';
            $this->idtipo = 'idfactura';
            $this->documento = 'factura_cliente';
         }

         if( isset($_REQUEST['tipo']) )
         {
            $this->tipo = $_REQUEST['tipo'];
            if($_REQUEST['tipo'] == 'facturascli')
            {
               $this->idtipo = 'idfactura';
               $this->documento = 'factura_cliente';
            }
            else if($_REQUEST['tipo'] == 'presupuestoscli')
            {
               $this->idtipo = 'idpresupuesto';
               $this->documento = 'presupuesto_cliente';
            }
            else if($_REQUEST['tipo'] == 'pedidoscli')
            {
               $this->idtipo = 'idpedido';
               $this->documento = 'pedido_cliente';
            }
            else if($_REQUEST['tipo'] == 'albaranescli')
            {
               $this->idtipo = 'idalbaran';
               $this->documento = 'albaran_cliente';
            }
            else if($_REQUEST['tipo'] == 'servicioscli')
            {
               $this->idtipo = 'idservicio';
               $this->documento = 'servicio_cliente';
            }
            else if($_REQUEST['tipo'] == 'pedidosprov')
            {
               $this->idtipo = 'idpedido';
               $this->documento = 'pedido_proveedor';
            }
            else if($_REQUEST['tipo'] == 'albaranesprov')
            {
               $this->idtipo = 'idalbaran';
               $this->documento = 'albaran_proveedor';
            }
            else if($_REQUEST['tipo'] == 'facturasprov')
            {
               $this->idtipo = 'idfactura';
               $this->documento = 'factura_proveedor';
            }
         }
         
         $this->resultados = $this->resultados();
         
         /// url para paginacion y descarga 
         $this->b_url = $this->url() . "&mostrar=" . $this->mostrar
                 . "&codcliente=" . $this->cliente->codcliente
                 . "&codproveedor=" . $this->proveedor->codproveedor
                 . "&tipo=" . $this->tipo
                 . "&desde=" . $this->desde
                 . "&hasta=" . $this->hasta
                 . "&offset=" . $this->offset
                 . "&b_adjunto=" . $this->adj;
         
         /// ¿Descargar zip?
         if( isset($_REQUEST['download']) )
         {
            if($this->totalresultados)
            {
               $archivo_zip = '';
               foreach($this->totalresultados as $r)
               {
                  if (!empty($r['ruta']))
                  {
                    $archivo_zip = $this->download_zip($r['ruta'], $r['nombrearchivo']);
                  }
               }
               
               if($archivo_zip != '')
               {
                  header("Content-Type: application/zip");
                  header("Content-Transfer-Encoding: Binary");
                  header("Content-Length: " . filesize('documentos.zip'));
                  header("Content-Disposition: attachment; filename=\"" . basename('documentos.zip') . "\"");
                  readfile('documentos.zip');
                  
                  if( file_exists('documentos.zip') )
                  {
                     unlink('documentos.zip');
                  }
               }
               else
               {
                  $this->new_error_msg('Ha ocurrido un problema al generar el zip');
               }
            }
         }
      }
   }

   /**
    * Buscamos los documentos con adjuntos
    */
   public function resultados()
   {
      $resultados = array();
      
      /// inicio y fin
      $inicio = intval($this->offset);
      $fin = intval($inicio + FS_ITEM_LIMIT);

      if($this->mostrar == 'ventas')
      {
         $nombre = 'nombrecliente';
         $prov = '';
         $num2 = 'numero2';
      }
      else
      {
         $nombre = 'nombre';
         $prov = 'prov';
         $num2 = 'numproveedor';
      }

      /// filtros.
      $sql = '';
      $where = 'WHERE ';
      if($this->desde != '')
      {
         $sql .= $where . "fecha >= " . $this->empresa->var2str($this->desde);
         $where = ' AND ';
      }

      if($this->hasta != '')
      {
         $sql .= $where . "fecha <= " . $this->empresa->var2str($this->hasta);
         $where = ' AND ';
      }

      if($this->cliente->codcliente)
      {
         $sql .= $where . "codcliente = " . $this->empresa->var2str($this->cliente->codcliente);
         $where = ' AND ';
      }

      if($this->proveedor->codproveedor)
      {
         $sql .= $where . "codproveedor = " . $this->empresa->var2str($this->proveedor->codproveedor);
         $where = ' AND ';
      }

      $sql = "SELECT * FROM " . $this->tipo . " " . $sql . " ORDER BY fecha DESC;";
      $data = $this->db->select($sql);

      if($data)
      {
         foreach($data as $d)
         {
            $documento = new $this->documento($d);

            $adj0 = new documento_factura();
            $adjuntos = $adj0->all_from($this->idtipo . $prov, $d[$this->idtipo]);
            if ($adjuntos && $this->adj == '0' || $adjuntos && $this->adj == '1')
            {
                foreach($adjuntos as $adj)
                {
                    $resultados[] = array(
                        'codigo' => $documento->codigo,
                        'doc_url' => $documento->url(),
                        'fecha' => $documento->fecha,
                        'nombre' => $documento->$nombre,
                        'numero2' => $documento->$num2,
                        'ruta' => $adj->ruta,
                        'nombrearchivo' => $adj->nombre,
                        'docfecha' => $adj->fecha,
                        'dochora' => $adj->hora,
                        'tamano' => $adj->tamano(),
                        'usuario' => $adj->usuario,
                    );
                }
            } else if (!$adjuntos && $this->adj == '0' || !$adjuntos && $this->adj == '2')
            {
                $resultados[] = array(
                    'codigo' => $documento->codigo,
                    'doc_url' => $documento->url(),
                    'fecha' => $documento->fecha,
                    'nombre' => $documento->$nombre,
                    'numero2' => $documento->$num2,
                    'ruta' => '',
                    'nombrearchivo' => '',
                    'docfecha' => '',
                    'dochora' => '',
                    'tamano' => '',
                    'usuario' => '',
                );
            }
         }
      }
      $this->totalresultados = $resultados;
      $this->numresultados = count($resultados);

      return array_slice($resultados, $inicio, $fin);
   }

   private function download_zip($ruta, $filename)
   {
      /// desactivamos el motor de plantillas
      $this->template = FALSE;

      $zip = new ZipArchive;
      if($zip->open('documentos.zip', ZipArchive::CREATE) === TRUE)
      {
         $zip->addFile($ruta, $filename);
         $zip->close();
      }

      return true;
   }

   /**
    * buscamos los clientes autocomplete
    */
   private function buscar_cliente()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;

      $cli0 = new cliente();
      $json = array();
      foreach($cli0->search($_REQUEST['buscar_cliente']) as $cli)
      {
         $json[] = array('value' => $cli->nombre, 'data' => $cli->codcliente);
      }

      header('Content-Type: application/json');
      echo json_encode(array('query' => $_REQUEST['buscar_cliente'], 'suggestions' => $json));
   }

   /**
    * Buscamos el proveedor autocomplete
    */
   private function buscar_proveedor()
   {
      /// desactivamos la plantilla HTML
      $this->template = FALSE;

      $pro0 = new proveedor();
      $json = array();
      foreach($pro0->search($_REQUEST['buscar_proveedor']) as $pro)
      {
         $json[] = array('value' => $pro->nombre, 'data' => $pro->codproveedor);
      }

      header('Content-Type: application/json');
      echo json_encode(array('query' => $_REQUEST['buscar_proveedor'], 'suggestions' => $json));
   }
   
   public function paginas()
   {
      $total = $this->numresultados;
      
      $url = $this->url()."&mostrar=" . $this->mostrar
              . "&codcliente=" . $this->cliente->codcliente
              . "&codproveedor=" . $this->proveedor->codproveedor
              . "&tipo=" . $this->tipo
              . "&desde=" . $this->desde
              . "&hasta=" . $this->hasta;
      
      
      $paginas = array();
      $i = 0;
      $num = 0;
      $actual = 1;

      /// añadimos todas la página
      while($num < $total)
      {
         $paginas[$i] = array(
             'url' => $url."&offset=".($i*FS_ITEM_LIMIT),
             'num' => $i + 1,
             'actual' => ($num == $this->offset)
         );
         
         if($num == $this->offset)
         {
            $actual = $i;
         }
         
         $i++;
         $num += FS_ITEM_LIMIT;
      }
      
      /// ahora descartamos
      foreach($paginas as $j => $value)
      {
         $enmedio = intval($i/2);
         
         /**
          * descartamos todo excepto la primera, la última, la de enmedio,
          * la actual, las 5 anteriores y las 5 siguientes
          */
         if( ($j>1 AND $j<$actual-5 AND $j!=$enmedio) OR ($j>$actual+5 AND $j<$i-1 AND $j!=$enmedio) )
         {
            unset($paginas[$j]);
         }
      }
      
      if( count($paginas) > 1 )
      {
         return $paginas;
      }
      else
      {
         return array();
      }
   }
   
   public function is_image($name)
   {
      $is_image = FALSE;
      $name = mb_strtolower($name, 'UTF-8');
      
      if( mb_substr($name, -4) == '.jpg' OR mb_substr($name, -5) == '.jpeg' )
      {
         $is_image = TRUE;
      }
      else if( mb_substr($name, -4) == '.png' OR mb_substr($name, -4) == '.gif' )
      {
         $is_image = TRUE;
      }
      
      return $is_image;
   }

   public function generar_detalle()
   {
      // definir archivos para concatenar
      $files = array();

      /// filtros.
      $sql = '';
      $where = 'WHERE ';
      if($this->desde != '')
      {
         $sql .= $where . "fecha >= " . $this->empresa->var2str($this->desde);
         $where = ' AND ';
      }

      if($this->hasta != '')
      {
         $sql .= $where . "fecha <= " . $this->empresa->var2str($this->hasta);
         $where = ' AND ';
      }

      if(isset($_REQUEST['codcliente']) && $_REQUEST['codcliente'] != '')
      {
         $sql .= $where . "codcliente = " . $this->empresa->var2str($_REQUEST['codcliente']);
         $where = ' AND ';
      }

      if(isset($_REQUEST['codproveedor']) && $_REQUEST['codproveedor'] != '')
      {
         $sql .= $where . "codproveedor = " . $this->empresa->var2str($_REQUEST['codproveedor']);
         $where = ' AND ';
      }

      if ($this->adj == '1')
      {
         $sql .= $where . "numdocs = '1'";
         $where = ' AND ';
      } else if ($this->adj == '2')
      {
         $sql .= $where . "numdocs = '0'";
         $where = ' AND ';
      }

      $sql = "SELECT * FROM " . $_REQUEST['tipo'] . " " . $sql . " ORDER BY fecha DESC;";
      $data = $this->db->select($sql);

      $ppdf_setup = new plantillas_setup();
      $this->pdf_setup = $ppdf_setup->setup;
      $this->plantilla = $ppdf_setup->cargar_plantilla();

      $cliente = new cliente();

      foreach ($data as $d) {
         $fecha = date('Y-m-d', strtotime($d['fecha']));

         if($_REQUEST['tipo'] == 'albaranescli')
         {
            $this->plantilla->albaran = new albaran_cliente($d);
            $this->plantilla->cliente = $cliente->get($this->plantilla->albaran->codcliente);

            $this->plantilla->documento = $this->plantilla->albaran;

            $filename = 'albaran_' . $fecha . '_'.$this->plantilla->albaran->codigo.'.pdf';

            $this->plantilla->generar_pdf_albaran($filename);
            $files[] = 'tmp/'.FS_TMP_NAME.'enviar/'.$filename;
         }
         else if($_REQUEST['tipo'] == 'facturascli')
         {
            $this->plantilla->factura = new factura_cliente($d);
            $this->plantilla->cliente = $cliente->get($this->plantilla->factura->codcliente);

            $this->plantilla->documento = $this->plantilla->factura;

            $filename = 'factura_' . $fecha . '_'.$this->plantilla->factura->codigo.'.pdf';

            $this->plantilla->generar_pdf_factura($filename);
            $files[] = 'tmp/'.FS_TMP_NAME.'enviar/'.$filename;
         }
         else if($_REQUEST['tipo'] == 'presupuestoscli')
         {
            $this->plantilla->presupuesto = new presupuesto_cliente($d);
            $this->plantilla->cliente = $cliente->get($this->plantilla->presupuesto->codcliente);

            $this->plantilla->documento = $this->plantilla->presupuesto;

            $filename = 'presupuesto_' . $fecha . '_'.$this->plantilla->presupuesto->codigo.'.pdf';

            $this->plantilla->generar_pdf_presupuesto($filename);
            $files[] = 'tmp/'.FS_TMP_NAME.'enviar/'.$filename;
         }
         else if($_REQUEST['tipo'] == 'pedidoscli')
         {
            $this->plantilla->pedido = new pedido_cliente($d);
            $this->plantilla->cliente = $cliente->get($this->plantilla->pedido->codcliente);

            $this->plantilla->documento = $this->plantilla->pedido;

            $filename = 'pedido_' . $fecha . '_'.$this->plantilla->pedido->codigo.'.pdf';

            $this->plantilla->generar_pdf_pedido($filename);
            $files[] = 'tmp/'.FS_TMP_NAME.'enviar/'.$filename;
         }
      }
   }
}
