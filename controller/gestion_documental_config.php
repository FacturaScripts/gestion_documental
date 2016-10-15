<?php

/*
 * @author Fusió d'Arts      contacto@fusiodarts.com
 * @copyright 2016, Fusió d'Arts. All Rights Reserved.
 */

require_model('gestion_documento.php');

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

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Opciones', 'G. Documental', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->gesdoc   = new gestion_documento();
        // Asignamos los tipos de documentos para el selector
        $this->tiposdoc = $this->gesdoc->tipos_documentos();

        // Si recibimos tipodoc guardamos los cambios
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
        {
            $f            = array();
            $f['name']    = 'gdoc_tipodoc';
            $f['varchar'] = $_POST['tipodoc'];
            $tipodoc      = new fs_var($f);
            $tipodoc->save();
            $this->docs   = $_POST['tipodoc'];
        } else
        {
            // Cargamos la opcion tipodoc desde fsvar
            $fsvar      = new fs_var();
            $this->docs = $fsvar->simple_get('gdoc_tipodoc');
        }
    }

}
