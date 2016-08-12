<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of gestion_documental
 *
 * @author Angel Albiach <fusiodarts.com>
 */
class gestion_documento extends fs_model
{

    public $filtros;
    
    public function __construct($gd = FALSE)
    {
        parent::__construct();
    }

    public function set_filtros($type, $value)
    {
        
        if ($type == 'b_fdesde' || $type == 'b_fhasta')
        {
            $date  = date('d-m-Y', strtotime($value));
            $this->filtros[$type] = $date;
        } else {
            $this->filtros[$type] = $value;
        }

    }

    /**
     * Función para construir los diferentes tipos de documentos 
     * de facturación con código y descripción
     * @return array Array con objetos de los tipos de documentos
     */
    public function tipos_documentos()
    {
        $tipodoc_fc              = new stdClass();
        $tipodoc_fc->codigo      = 'FC';
        $tipodoc_fc->descripcion = 'Factura de Cliente';

        $tipodoc_fp              = new stdClass();
        $tipodoc_fp->codigo      = 'FP';
        $tipodoc_fp->descripcion = 'Factura de Proveedor';

        $tipodoc_ac              = new stdClass();
        $tipodoc_ac->codigo      = 'AC';
        $tipodoc_ac->descripcion = 'Albarán de Cliente';

        $tipodoc_ap              = new stdClass();
        $tipodoc_ap->codigo      = 'AP';
        $tipodoc_ap->descripcion = 'Albarán de Proveedor';

        $tiposdoc = array(
            'FC' => $tipodoc_fc,
            'FP' => $tipodoc_fp,
            'AC' => $tipodoc_ac,
            'AP' => $tipodoc_ap,
        );

        return $tiposdoc;
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
    
    public function get_documents($sql, $sql_res, $sql_pages, $offset, $adjunto, $tabla, $id)
    {
        $wh = '';
        if ($sql && $adjunto == '1' || $sql && $adjunto == '2') {
            $wh = ' AND ' . $sql;
        } else if ($sql != '' && $adjunto == '0') {
            $wh = ' WHERE ' . $sql;
        }
        
        $resultados = $this->db->select_limit("SELECT f.*, d.*, f.".$id." as ".$id.", f.fecha as fecha, f.nombre as nombre, d.nombre as doc_nombre, d.fecha as doc_fecha, d.hora as doc_hora FROM " . $sql_res . $wh . " ORDER BY f.fecha DESC, f.codigo DESC ", FS_ITEM_LIMIT, $offset);
        $pages = $this->db->select("SELECT COUNT(f.".$id.") as total FROM ".$tabla." as f " . $sql_pages . $wh . ";");

        return array($resultados, $pages);
    }

}
