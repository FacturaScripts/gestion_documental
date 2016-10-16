<?php

/*
 * @author Fusió d'Arts      contacto@fusiodarts.com
 * @copyright 2016, Fusió d'Arts. All Rights Reserved.
 */

require_model('gestion_documento.php');
require_model('gestion_documento_config.php');

/**
 * Configuración de las opciones de Gestión Documental
 *
 * @author Angel Albiach
 */
class gestion_documental_config extends fs_controller
{

    public $tipodoc;
    public $gesdoc;
    public $docs;
    public $avanzadas;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Opciones', 'G. Documental', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->gesdoc        = new gestion_documento();
        $this->gesdoc_config = new gestion_documento_config();
        $fsvar               = new fs_var();
        $this->avanzadas     = new stdClass();

        // Asignamos los tipos de documentos para el selector
        $this->tiposdoc = $this->gesdoc->tipos_documentos();

        // Si recibimos tipodoc guardamos los cambios
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
        {
            $f            = array();
            $f['name']    = 'gdoc_tipodoc';
            $f['varchar'] = $_POST['tipodoc'];
            $fsvar2      = new fs_var($f);
            $fsvar2->save();
            $this->tipodoc   = $_POST['tipodoc'];
        } else
        {
            // Cargamos la opcion tipodoc desde fsvar
            $this->tipodoc = $fsvar->simple_get('gdoc_tipodoc');
        }

        $this->init_gesdoc_avanzada();
    }

    /// Con esta función gestionamos las llamadas al plugin de gestion_documental_avanzada
    private function init_gesdoc_avanzada()
    {
        $fsvar = new fs_var();

        if (isset($_POST['nombre_original']) || isset($_POST['numero2']) || isset($_POST['codigo_facturacion']) || isset($_POST['fecha_facturacion']))
        {
            $this->gesdoc_config->gestion_documental_avanzada();
        }
        $this->avanzadas->nombre_original    = $fsvar->simple_get('gdoc_nombre_original');
        $this->avanzadas->numero2            = $fsvar->simple_get('gdoc_numero2');
        $this->avanzadas->codigo_facturacion = $fsvar->simple_get('gdoc_codigo_facturacion');
        $this->avanzadas->fecha_facturacion  = $fsvar->simple_get('gdoc_fecha_facturacion');

        return;
    }

}
