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
    public function gestion_documental_avanzada()
    {
         $this->new_error_msg('Necesitas el plugin de Gestión Documental Avanzada.');
    }
}
