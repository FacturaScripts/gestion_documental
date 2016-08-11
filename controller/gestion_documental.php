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
class gestion_documental extends fs_controller
{

//    public $codserie;
    public $serie;
    public $tiposdoc;
    public $resultados;
    public $offset;
    public $b_adjunto;
    public $filtros;
    public $options;
    
    public $docs;
    public $desde;
    public $hasta;
    public $adj;
    public $url;
    
    public $pages;
    
    public $facturacli;
    
    public $desc_tipodoc;
    public $tipodoc;
    public $extension;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Gesti&oacute;n Documental', 'compras', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->facturacli  = new factura_cliente();
//        $this->facturaprov = new factura_proveedor();
//        $this->albarancli  = new albaran_cliente();
//        $this->albaranprov = new albaran_proveedor();
//        $this->docfactura  = new documento_factura();
        $this->gesdoc   = new gestion_documento();
//        $this->fdesde = Date('1-m-Y');
//        $this->fhasta = Date('d-m-Y');
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
        
        $this->url = $this->url();

        $this->offset = 0;
        if (isset($_REQUEST['offset'])) {
            $this->offset = intval($_REQUEST['offset']);
        }
        
        $this->docs = '';
        $this->desde = '';
        $this->hasta = '';
        $this->adj = '';

        // Inicializamos los filtros
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
        {
            $this->gesdoc->set_filtros('tipodoc', $_POST['tipodoc']);
        } else if (isset($_GET['docs']) && $_GET['docs'] != '')
        {
            $this->gesdoc->set_filtros('tipodoc', $_POST['docs']);
        } else
        {
            $this->gesdoc->set_filtros('tipodoc', 'FC');
            $this->docs = 'FC';
        }
        
        $sql = "";
        $where = '';
        if (isset($_POST['b_adjunto']) && $_POST['b_adjunto'] != '')
        {
            $this->gesdoc->set_filtros('b_adjunto', $_POST['b_adjunto']);
        } else if (isset($_GET['adjunto']) && $_GET['adjunto'] != '') 
        {
            $this->gesdoc->set_filtros('b_adjunto', $_GET['adjunto']);
        }
        if (isset($_POST['desde']) && $_POST['desde'] != '')
        {
            $this->gesdoc->set_filtros('b_fdesde', $_POST['desde']);
            $d = date('Y-m-d', strtotime($_POST['desde']));
            $sql .= $where . " f.fecha >= '" . $d . "'";
            $where = ' AND ';
        } else if (isset($_GET['desde']) && $_GET['desde'] != '')
        {
            $this->gesdoc->set_filtros('b_fdesde', $_GET['desde']);
            $d = date('Y-m-d', strtotime($_GET['desde']));
            $sql .= $where . " f.fecha >= '" . $d . "'";
            $where = ' AND ';
        }        
        if (isset($_POST['hasta']) && $_POST['hasta'] != '')
        {
            $this->gesdoc->set_filtros('b_fhasta', $_POST['hasta']);
            $h = date('Y-m-d', strtotime($_POST['hasta']));
            $sql .= $where . " f.fecha <= '" . $h . "'";
        } else if (isset($_GET['hasta']) && $_GET['hasta'] != '')
        {
            $this->gesdoc->set_filtros('b_fhasta', $_GET['hasta']);
            $h = date('Y-m-d', strtotime($_GET['hasta']));
            $sql .= $where . " f.fecha <= '" . $h . "'";
        }
        
        $this->filtros = $this->gesdoc->filtros;
        $this->options = '';
        
        if (isset($_REQUEST['tipodoc'])) {
            $this->docs = $_REQUEST['tipodoc'];
        } else if (isset($_GET['docs'])) {
            $this->docs = $_GET['docs'];
        }
        
        if (isset($_GET['desde'])) {
            $this->desde = $_GET['desde'];
        } else {
            $this->desde = $this->filtros['b_fdesde'];
        }
        
        if (isset($_GET['hasta'])) {
            $this->hasta = $_GET['hasta'];
        } else {
            $this->hasta = $this->filtros['b_fhasta'];
        }
        
        if (isset($_GET['adjunto'])) {
            $this->adj = $_GET['adjunto'];
        } else {
            $this->adj = $this->filtros['b_adjunto'];
        }

        // Mostramos listado por tipo de documento
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '' || isset($_GET['docs']) && $_GET['docs'] != '')
        {
            $this->tipodoc = $_POST['tipodoc'];
        } else
        {
            $this->tipodoc = 'FC';
        }
        
        if ($this->tipodoc == 'FC' || $this->docs == 'FC')
        {
            $this->options .= '&facturacli=true';
            $this->desc_tipodoc = 'Factura Cliente';
            $tabla = 'facturascli';
            $id = 'idfactura';
        } else if ($this->tipodoc == 'FP' || $this->docs == 'FP')
        {
            $this->options .= '&facturaprov=true';
            $this->desc_tipodoc = 'Factura Proveedor';
            $tabla = 'facturasprov';
            $id = 'idfactura';
        } else if ($this->tipodoc == 'AC' || $this->docs == 'AC')
        {
            $this->options .= '&albarancli=true';
            $this->desc_tipodoc = 'Albarán Cliente';
            $tabla = 'albaranescli';
            $id = 'idalbaran';
        } else if ($this->tipodoc == 'AP' || $this->docs == 'AP')
        {
            $this->options .= '&albaranprov=true';
            $this->desc_tipodoc = 'Albarán Proveedor';
            $tabla = 'albaranesprov';
            $id = 'idalbaran';
        }
        
        if ($this->adj != '0') {
            $wh = '';
            if ($sql) {
                $wh = ' AND ' . $sql;
            }
        } else {                
            $wh = '';
            if ($sql) {
                $wh = ' WHERE ' . $sql;
            }
        }
        
        if ($this->tipodoc) {
            $doc = $this->tipodoc;
        } else if ($this->docs) {
            $doc = $this->docs;
        }
        
        if ($this->adj == '1') {
            $sql_res = 'documentosfac as d, '.$tabla.' as f WHERE f.'.$id.' = d.'.$id.' ';
            $sql_pages = ', documentosfac as d WHERE f.'.$id.' = d.'.$id.' ';           
        } else if ($this->adj == '2') {
            $sql_res = $tabla.' as f LEFT JOIN documentosfac as d ON f.'.$id.' = d.'.$id.' WHERE d.'.$id.' IS NULL ';
            $sql_pages = ' LEFT JOIN documentosfac as d ON f.'.$id.' = d.'.$id.' WHERE d.'.$id.' IS NULL ';
        } else {
            $sql_res = $tabla.' as f LEFT JOIN documentosfac as d ON d.'.$id.' = f.'.$id.' ';
            $sql_pages = '';
        }
        
        $res = $this->gesdoc->get_documents($sql, $sql_res, $sql_pages, $this->offset, $doc, $this->adj, $tabla, $id);
        $this->resultados = $res[0];
        $this->pages = $res[1];
        
        foreach ($this->resultados as $i=>$r) {
            $this->resultados[$i]['extension'] = substr(strrchr($r['doc_nombre'], '.'), 1);
        }
        
        if ($this->filtros) {
            $this->url .= '&docs=' . $this->docs;
            $this->url .= '&desde=' . $this->desde;
            $this->url .= '&hasta=' . $this->hasta;
            $this->url .= '&adjunto=' . $this->adj;
        }

        $returned = $this->get_data('http://fusioerp.local/index.php?page=plantillas_pdf&factura=TRUE&id=1917');
        
        file_put_contents('prueba.pdf', $returned);
//        $this->print_facturacli_pdf('simple', 50, 'prueba.pdf');
    }

    /* gets the data from a URL */
    public function get_data($url)
    {
        $ch      = curl_init();
        $timeout = 5;
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
        $data    = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
   
    public function paginas()
    {
        $url = $this->url;

        $paginas = array();
        $i = 0;
        $num = 0;
        $actual = 1;
        $total = intval($this->pages[0]['total']);
//        $total = count($this->resultados);

        /// añadimos todas la página
        while ($num < $total) {
            $paginas[$i] = array(
                'url' => $url . "&offset=" . ($i * FS_ITEM_LIMIT),
                'num' => $i + 1,
                'actual' => ($num == $this->offset)
            );

            if ($num == $this->offset) {
                $actual = $i;
            }

            $i++;
            $num += FS_ITEM_LIMIT;
        }

        /// ahora descartamos
        foreach ($paginas as $j => $value) {
            $enmedio = intval($i / 2);

            /**
             * descartamos todo excepto la primera, la última, la de enmedio,
             * la actual, las 5 anteriores y las 5 siguientes
             */
            if (($j > 1 AND $j < $actual - 5 AND $j != $enmedio) OR ( $j > $actual + 5 AND $j < $i - 1 AND $j != $enmedio)) {
                unset($paginas[$j]);
            }
        }
        
        if (count($paginas) > 1) {
            return $paginas;
        } else {
            return array();
        }
    }

//
//    public function print_facturacli_pdf($tipo, $idfactura, $archivo)
//    {
//        $fac           = new factura_cliente();
//        $factura = $fac->get($idfactura);
//        if ($factura)
//        {
//            $cli       = new cliente();
//            $cliente = $cli->get($factura->codcliente);
//        }
//
//        
//        require_once '/plugins/facturacion_base/controller/ventas_imprimir.php';
//        $print = new ventas_imprimir();
//        $print->generar_pdf_factura($tipo, $archivo);
//    }
}
