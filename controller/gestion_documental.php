<?php

/*
 * @author Angel Albiach      contacto@fusiodarts.com
 * @copyright 2016, Fusió d'Arts. All Rights Reserved.
 */

require_model('agente.php');
require_model('articulo.php');
require_model('cliente.php');
require_model('factura_cliente.php');

/**
 * Gestión Documental de los archivos adjuntos a los documentos de facturación
 *
 * @author Angel Albiach
 */
class gestion_documental extends fs_controller
{

//    public $codserie;
//    public $serie;
    public $resultados;
    public $offset;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Gesti&oacute;n Documental', 'compras', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->facturacli = new factura_cliente();
        $this->facturaprov = new factura_proveedor();
        $this->albarancli = new factura_proveedor();
        $this->albaranprov = new factura_proveedor();


        $this->offset = 0;
        if (isset($_REQUEST['offset']))
        {
            $this->offset = intval($_REQUEST['offset']);
        }

        /// primer nivel de ordenación
        $this->order = 'fecha DESC';
        if (isset($_GET['order']))
        {
            if ($_GET['order'] == 'fecha_desc')
            {
                $this->order = 'fecha DESC';
            } else if ($_GET['order'] == 'fecha_asc')
            {
                $this->order = 'fecha ASC';
            } else if ($_GET['order'] == 'vencimiento_desc')
            {
                $this->order = 'vencimiento DESC';
            } else if ($_GET['order'] == 'vencimiento_asc')
            {
                $this->order = 'vencimiento ASC';
            } else if ($_GET['order'] == 'total_desc')
            {
                $this->order = 'total DESC';
            }

            setcookie('gesdoc_order', $this->order, time() + FS_COOKIES_EXPIRE);
        }

        /// añadimos segundo nivel de ordenación
        $order2 = '';
        if (substr($this->order, -4) == 'DESC')
        {
            $order2 = ', hora DESC, numero DESC';
        } else
        {
            $order2 = ', hora ASC, numero ASC';
        }

        /// creamos el array resultados con el tipo de documento solicitado
        if (isset($_GET['tipodoc']))
        {
            if ($_GET['tipodoc'] == 'facturacli')
            {
                $this->resultados = $this->facturacli->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
            } else if ($_GET['tipodoc'] == 'facturaprov')
            {
                $this->resultados = $this->facturaprov->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
            } else if ($_GET['tipodoc'] == 'albarancli')
            {
                $this->resultados = $this->facturaprov->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
            } else if ($_GET['tipodoc'] == 'albaranprov')
            {
                $this->resultados = $this->facturaprov->all($this->offset, FS_ITEM_LIMIT, $this->order . $order2);
            }

            setcookie('gesdoc_tipodoc', $this->order, time() + FS_COOKIES_EXPIRE);
        }
//        echo '<br/>';
//        echo '<br/>';
//        echo '<br/>';
//        echo '<br/>';
//        echo '<br/>';
//        echo '<br/>';
//        var_dump($this->factura->all());
    }

}
