<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_model('agente.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('proveedor.php');
require_model('factura_cliente.php');
require_model('albaran_cliente.php');
require_model('factura_proveedor.php');
require_model('albaran_proveedor.php');
require_model('gestion_documento.php');
require_model('documento_factura.php');

/**
 * Description of gestion_documental
 *
 * @author Angel Albiach <fusiodarts.com>
 */
class gestion_documento extends fs_model {

    public $id;
    public $codigo;
    public $tipo;
    public $cod_tipodoc;
    public $desc_tipodoc;
    public $numero2;
    public $pagada;
    public $anulada;
    public $idfacturarect;
    public $femail;
    public $fecha;
    public $codcliente;
    public $codproveedor;
    public $nombre;
    public $nombrecliente;
    public $observaciones;
    public $archivo;
    public $fecha_archivo;
    public $hora_archivo;
    public $tamano;
    public $usuario;
    public $filtros;

    public function __construct($gd = FALSE) {

//        parent::__construct('gesdoc');
//        $facturacli  = new factura_cliente();
//        $facturaprov = new factura_proveedor();
//        $albarancli  = new albaran_cliente();
//        $albaranprov = new albaran_proveedor();
        $docfactura = new documento_factura();

        if ($gd) {

            if (get_class($gd) == 'factura_cliente') {
                $this->cod_tipodoc = 'FC';
                $this->desc_tipodoc = 'Factura Cliente';
                $this->tipo = 'idfactura';
                $this->id = $this->intval($gd->idfactura);
                $this->nombre = $gd->nombrecliente;
                $this->codcliente = $gd->codcliente;
            }
            if (get_class($gd) == 'factura_proveedor') {
                $this->cod_tipodoc = 'FP';
                $this->desc_tipodoc = 'Factura Proveedor';
                $this->tipo = 'idfacturaprov';
                $this->id = $this->intval($gd->idfactura);
                $this->nombre = $gd->nombre;
                $this->codproveedor = $gd->codproveedor;
            }
            if (get_class($gd) == 'albaran_cliente') {
                $this->cod_tipodoc = 'AC';
                $this->desc_tipodoc = 'Albaran Cliente';
                $this->tipo = 'idalbaran';
                $this->id = $this->intval($gd->idalbaran);
                $this->nombre = $gd->nombrecliente;
                $this->codcliente = $gd->codcliente;
            }
            if (get_class($gd) == 'albaran_proveedor') {
                $this->cod_tipodoc = 'AP';
                $this->desc_tipodoc = 'Albaran Proveedor';
                $this->tipo = 'idalbaranprov';
                $this->id = $this->intval($gd->idalbaran);
                $this->nombre = $gd->nombre;
                $this->codproveedor = $gd->codproveedor;
            }

            $this->codigo = $gd->codigo;
            $this->url = $gd->url();
            $this->numero2 = $gd->numero2;
            $this->pagada = $gd->pagada;
            $this->anulada = $gd->anulada;
            $this->idfacturarect = $gd->idfacturarect;
            $this->femail = date('d-m-Y', strtotime($gd->femail));
            $this->fecha = date('d-m-Y', strtotime($gd->fecha));
            $this->observaciones = $gd->observaciones_resume();

            $docfact = $docfactura->all_from($this->tipo, $this->id);

            $this->df_id = $docfact[0]->id;
            $this->df_ruta = $docfact[0]->ruta;
            $this->df_nombre = $docfact[0]->nombre;
            $this->df_extension = substr(strrchr($this->df_nombre, '.'), 1);
            $this->df_fecha = date('d-m-Y', strtotime($docfact[0]->fecha));
            $this->df_hora = date('h:i:s', strtotime($docfact[0]->hora));
            $this->df_tamano = $docfact[0]->tamano;
            $this->df_usuario = $docfact[0]->usuario;

            $this->df_idfactura = $docfact[0]->idfactura;
            $this->df_idalbaran = $docfact[0]->idalbaran;
            $this->df_idpedido = $docfact[0]->idpedido;
            $this->df_idpresupuesto = $docfact[0]->idpresupuesto;
            $this->df_idfacturaprov = $docfact[0]->idfacturaprov;
            $this->df_idalbaranprov = $docfact[0]->idalbaranprov;
            $this->df_idpedidoprov = $docfact[0]->idpedidoprov;
        } else {
            $this->cod_tipodoc = NULL;
            $this->desc_tipodoc = NULL;
            $this->id = NULL;
            $this->codigo = NULL;
            $this->tipo = NULL;
            $this->numero2 = NULL;
            $this->pagada = NULL;
            $this->anulada = NULL;
            $this->idfacturarect = NULL;
            $this->fecha = date('d-m-Y');
            $this->codcliente = NULL;
            $this->codproveedor = NULL;
            $this->nombre = NULL;
            $this->nombrecliente = NULL;
            $this->observaciones = NULL;

            $this->df_id = NULL;
            $this->df_ruta = NULL;
            $this->df_nombre = NULL;
            $this->df_fecha = date('d-m-Y');
            $this->df_hora = date('h:i:s');
            $this->df_tamano = 0;
            $this->df_usuario = NULL;

            $this->df_idfactura = NULL;
            $this->df_idalbaran = NULL;
            $this->df_idpedido = NULL;
            $this->df_idpresupuesto = NULL;
            $this->df_idfacturaprov = NULL;
            $this->df_idalbaranprov = NULL;
            $this->df_idpedidoprov = NULL;
        }
    }

    public function filtros($b_adjunto = 0) {
        $this->filtros = array(
            'b_adjunto' => $b_adjunto,
        );
    }

    public function all_facturas_clientes($desde, $hasta) {
        $model = new factura_cliente();
        return $this->all_by_model($model, $desde, $hasta);
    }

    public function all_facturas_proveedores($desde, $hasta) {
        $model = new factura_proveedor();
        return $this->all_by_model($model, $desde, $hasta);
    }

    public function all_albaranes_clientes($desde, $hasta) {
        $model = new albaran_cliente();
        return $this->all_by_model($model, $desde, $hasta);
    }

    public function all_albaranes_proveedores($desde, $hasta) {
        $model = new albaran_proveedor();
        return $this->all_by_model($model, $desde, $hasta);
    }

    /**
     * 
     * @param type $model
     * @param type $filter 0 = todos | 1 = solo con documento
     * @return \gestion_documento
     */
    public function all_by_model($model, $desde, $hasta) {
        if ($desde != '' && $desde != null && $hasta != '' && $hasta != null) {
            $fdesde = date('Y-m-d', strtotime($desde));
            $fhasta = date('Y-m-d', strtotime($hasta));
            $listado = $model->all_desde($fdesde, $fhasta);
        } else {
            $listado = $model->all();
        }

        $lista_gesdoc = array();

        foreach ($listado as $obj) {
            $gesdoc = new gestion_documento($obj);
            
            if ($this->filtros['b_adjunto'] == '1' && !$gesdoc->df_id) {
                // Saltamos este item
            } else if ($this->filtros['b_adjunto'] == '2' && $gesdoc->df_id != null) {
                // Saltamos este item
            } else {
                $lista_gesdoc[] = $gesdoc;
            }
        }
        
        return $lista_gesdoc;
    }

    public function all_documentos($desde, $hasta) {
        $all = array_merge($this->all_facturas_clientes($desde, $hasta), $this->all_albaranes_clientes($desde, $hasta), $this->all_facturas_proveedores($desde, $hasta), $this->all_albaranes_proveedores($desde, $hasta));
        return $all;
    }

//    public function check_documento_adjunto()
//    {
//        $check = array(
//            'idfactura' => $this->df_idfactura,
//            'idalbaran' => $this->df_idalbaran,
//            'idpedido' => $this->df_idpedido,
//            'idpresupuesto' => $this->df_idpresupuesto,
//            'idfacturaprov' => $this->df_idfacturaprov,
//            'idalbaranprov' => $this->df_idalbaranprov,
//            'idpedidoprov' => $this->df_idpedidoprov
//        );
//
//        return in_array($this->id, $check);
//    }

    /**
     * Función para construir los diferentes tipos de documentos 
     * de facturación con código y descripción
     * @return array Array con objetos de los tipos de documentos
     */
    public function tipos_documentos() {
        $tipodoc_fc = new stdClass();
        $tipodoc_fc->codigo = 'FC';
        $tipodoc_fc->descripcion = 'Factura de Cliente';

        $tipodoc_fp = new stdClass();
        $tipodoc_fp->codigo = 'FP';
        $tipodoc_fp->descripcion = 'Factura de Proveedor';

        $tipodoc_ac = new stdClass();
        $tipodoc_ac->codigo = 'AC';
        $tipodoc_ac->descripcion = 'Albarán de Cliente';

        $tipodoc_ap = new stdClass();
        $tipodoc_ap->codigo = 'AP';
        $tipodoc_ap->descripcion = 'Albarán de Proveedor';

        $tiposdoc = array(
            'FC' => $tipodoc_fc,
            'FP' => $tipodoc_fp,
            'AC' => $tipodoc_ac,
            'AP' => $tipodoc_ap,
        );

        return $tiposdoc;
    }

//            $this->tiposdoc = $this->tipos_documentos();
//        /// creamos el array resultados con el tipo de documento solicitado
//        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
//        {
//            $this->tipodoc = $_POST['tipodoc'];
//            if ($this->tipodoc == 'FC')
//            {
//                $this->resultados = $this->facturacli->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
//            } else if ($this->tipodoc == 'FP')
//            {
//                $this->resultados = $this->facturaprov->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
//            } else if ($this->tipodoc == 'AC')
//            {
//                $this->resultados = $this->albarancli->all($this->offset, $this->order . $order2);
//            } else if ($this->tipodoc == 'AP')
//            {
//                $this->resultados = $this->albaranprov->all($this->offset, $this->order . $order2);
//            }
//
//            setcookie('gesdoc_tipodoc', $this->order, time() + FS_COOKIES_EXPIRE);
//        } else
//        {
//            $fc_tmp        = $this->facturacli->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
//            $resultados_fc = $this->object_gesdoc($this->facturacli->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2), 'idfactura');
//            $resultados_fp = $this->object_gesdoc($this->facturaprov->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2), 'idfactura');
//            $resultados_ac = $this->object_gesdoc($this->albarancli->all($this->offset, $this->order . $order2), 'idalbaran');
//            $resultados_ap = $this->object_gesdoc($this->albaranprov->all($this->offset, $this->order . $order2), 'idalbaran');
//
//            $this->resultados = $this->object_gesdoc($fc_tmp, 'idfactura');
////            $this->resultados = array_merge($resultados_fc, $resultados_fp, $resultados_ac, $resultados_ap);
//        }


    protected function install() {
        
    }

    public function delete() {
        
    }

    public function exists() {
        
    }

    public function save() {
        
    }

}
