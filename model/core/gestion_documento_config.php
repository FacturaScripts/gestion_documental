<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FacturaScripts\model;

/**
 * Description of gestion_documental
 *
 * @author Angel Albiach <fusiodarts.com>
 */
class gestion_documento_config extends \fs_model
{
    
    public function __construct($gd = FALSE)
    {
        parent::__construct();
    }

    protected function install()
    {
        
    }

    public function delete()
    {
        
    }

    public function exists()
    {
        
    }

    public function save()
    {
        
    }
    public function export_zip()
    {
        /// nombre_original
        if (isset($_POST['nombre_original']) && $_POST['nombre_original'] != '')
        {
            $this->save_fsvar('gdoc_nombre_original', '1');
        } else
        {
            $this->delete_fsvar('gdoc_nombre_original');
        }

        /// Numero 2
        if (isset($_POST['numero2']) && $_POST['numero2'] != '')
        {
            $this->save_fsvar('gdoc_numero2', '1');
        } else
        {
            $this->delete_fsvar('gdoc_numero2');
        }
        
        /// codigo_facturacion
        if (isset($_POST['codigo_facturacion']) && $_POST['codigo_facturacion'] != '')
        {
            $this->save_fsvar('gdoc_codigo_facturacion', '1');
        } else
        {
            $this->delete_fsvar('gdoc_codigo_facturacion');
        }
        
        /// fecha-facturacion
        if (isset($_POST['fecha_facturacion']) && $_POST['fecha_facturacion'] != '')
        {
            $this->save_fsvar('gdoc_fecha_facturacion', '1');
        } else
        {
            $this->delete_fsvar('gdoc_fecha_facturacion');
        }

        return;
    }

    protected function save_fsvar($name, $varchar)
    {
        $f            = array();
        $f['name']    = $name;
        $f['varchar'] = $varchar;
        $fsvar        = new fs_var($f);
        $fsvar->save();
        return;
    }

    protected function delete_fsvar($name)
    {
        $fsvar        = new fs_var();
        $fsvar->name = $name;
        $fsvar->delete();
        return;
    }
}
