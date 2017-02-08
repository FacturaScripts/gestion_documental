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
    public $generate_zip;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Opciones', 'G. Documental', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->gesdoc        = new gestion_documento();
        $this->gesdoc_config = new gestion_documento_config();
        $fsvar               = new fs_var();
        $this->generate_zip     = new stdClass();

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

        if (isset($_POST['nombre_original']) || isset($_POST['numero2']) || isset($_POST['codigo_facturacion']) || isset($_POST['fecha_facturacion']))
        {
            $this->gesdoc_config->export_zip();
        }
        $this->generate_zip->nombre_original    = $fsvar->simple_get('gdoc_nombre_original');
        $this->generate_zip->numero2            = $fsvar->simple_get('gdoc_numero2');
        $this->generate_zip->codigo_facturacion = $fsvar->simple_get('gdoc_codigo_facturacion');
        $this->generate_zip->fecha_facturacion  = $fsvar->simple_get('gdoc_fecha_facturacion');
    }

}
