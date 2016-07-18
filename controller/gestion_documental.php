<?php

/*
 * @author Angel Albiach      contacto@fusiodarts.com
 * @copyright 2016, Fusió d'Arts. All Rights Reserved.
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
 * Gestión Documental de los archivos adjuntos a los documentos de facturación
 *
 * @author Angel Albiach
 */
class gestion_documental extends fs_controller {

//    public $codserie;
    public $serie;
    public $tiposdoc;
    public $resultados;
    public $offset;
    public $b_adjunto;
    public $fdesde;
    public $fhasta;

    public function __construct() {
        parent::__construct(__CLASS__, 'Gesti&oacute;n Documental', 'compras', FALSE, TRUE, FALSE);
    }

    protected function private_core() {

//        $this->facturacli  = new factura_cliente();
//        $this->facturaprov = new factura_proveedor();
//        $this->albarancli  = new albaran_cliente();
//        $this->albaranprov = new albaran_proveedor();
//        $this->docfactura  = new documento_factura();
        $this->gesdoc = new gestion_documento();
        $this->fdesde = Date('1-m-Y');
        $this->fhasta = Date('d-m-Y');

//        $this->offset      = 0;
//        if (isset($_REQUEST['offset']))
//        {
//            $this->offset = intval($_REQUEST['offset']);
//        }
//
//        /// primer nivel de ordenación
//        $this->order = 'fecha DESC';
//        if (isset($_GET['order']))
//        {
//            if ($_GET['order'] == 'fecha_desc')
//            {
//                $this->order = 'fecha DESC';
//            } else if ($_GET['order'] == 'fecha_asc')
//            {
//                $this->order = 'fecha ASC';
//            } else if ($_GET['order'] == 'vencimiento_desc')
//            {
//                $this->order = 'vencimiento DESC';
//            } else if ($_GET['order'] == 'vencimiento_asc')
//            {
//                $this->order = 'vencimiento ASC';
//            } else if ($_GET['order'] == 'total_desc')
//            {
//                $this->order = 'total DESC';
//            }
//
//            setcookie('gesdoc_order', $this->order, time() + FS_COOKIES_EXPIRE);
//        }
//
//        /// añadimos segundo nivel de ordenación
//        $order2 = '';
//        if (substr($this->order, -4) == 'DESC')
//        {
//            $order2 = ', hora DESC, numero DESC';
//        } else
//        {
//            $order2 = ', hora ASC, numero ASC';
//        }
        // Asignamos los tipos de documentos para el selector
        $this->tiposdoc = $this->gesdoc->tipos_documentos();

        // Inicializamos los filtros
        if (isset($_POST['b_adjunto']) && $_POST['b_adjunto'] != '') {
            $this->b_adjunto = $_POST['b_adjunto'];
            $this->gesdoc->filtros($this->b_adjunto);
        }

        // Mostramos listado por tipo de documento
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '') {
            $this->tipodoc = $_POST['tipodoc'];
            if ($this->tipodoc == 'FC') {
                $this->resultados = $this->gesdoc->all_facturas_clientes($_POST['desde'], $_POST['hasta']);
            } else if ($this->tipodoc == 'FP') {
                $this->resultados = $this->gesdoc->all_facturas_proveedores($_POST['desde'], $_POST['hasta']);
            } else if ($this->tipodoc == 'AC') {
                $this->resultados = $this->gesdoc->all_albaranes_clientes($_POST['desde'], $_POST['hasta']);
            } else if ($this->tipodoc == 'AP') {
                $this->resultados = $this->gesdoc->all_albaranes_proveedores($_POST['desde'], $_POST['hasta']);
            }
        } else {
            $this->resultados = $this->gesdoc->all_documentos($_POST['desde'], $_POST['hasta']);
        }

        $this->fdesde = $_POST['desde'];
        $this->fhasta = $_POST['hasta'];
    }

}
