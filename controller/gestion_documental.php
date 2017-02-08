<?php

/*
 * @author Angel Albiach      contacto@fusiodarts.com
 * @copyright 2016, Fusió d'Arts. All Rights Reserved.
 */

require_model('gestion_documento.php');

/**
 * Gestión Documental de los archivos adjuntos a los documentos de facturación
 *
 * @author Angel Albiach
 */
class gestion_documental extends fs_controller
{

    public $tiposdoc;
    public $resultados;
    public $offset;
    public $b_adjunto;
    public $filtros;
    public $desde;
    public $hasta;
    public $adj;
    public $url;
    public $pages;
    public $desc_tipodoc;
    public $tipodoc;
    public $extension;
    public $doc_url;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Gestión', 'G. Documental', FALSE, TRUE, FALSE);
    }

    protected function private_core()
    {

        $this->gesdoc = new gestion_documento();

        // Asignamos los tipos de documentos para el selector
        $this->tiposdoc = $this->gesdoc->tipos_documentos();

        $this->offset = 0;
        if (isset($_REQUEST['offset']))
        {
            $this->offset = intval($_REQUEST['offset']);
        }

        $this->tipodoc = '';
        $this->desde   = '';
        $this->hasta   = '';
        $this->adj     = '';
        $sql           = "";
        $where         = '';

        /// Generar ZIP
        if (isset($_POST['zip']) && $_POST['zip'] != '')
        {
            $this->generate_zip();
        }

        /// Inicializamos los filtros
        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
        {
            $this->gesdoc->set_filtros('tipodoc', $_POST['tipodoc']);
        } else
        {
            /// Cargamos la opción por defecto desde la configuracion
            $fsvar         = new fs_var();
            $this->tipodoc = $fsvar->simple_get('gdoc_tipodoc');
            $this->gesdoc->set_filtros('tipodoc', $this->tipodoc);
        }
        if (isset($_POST['b_adjunto']) && $_POST['b_adjunto'] != '')
        {
            $this->gesdoc->set_filtros('b_adjunto', $_POST['b_adjunto']);
        }
        if (isset($_POST['desde']) && $_POST['desde'] != '')
        {
            $this->gesdoc->set_filtros('b_fdesde', $_POST['desde']);
            $d     = date('Y-m-d', strtotime($_POST['desde']));
            $sql .= $where . " f.fecha >= '" . $d . "'";
            $where = ' AND ';
        }
        if (isset($_POST['hasta']) && $_POST['hasta'] != '')
        {
            $this->gesdoc->set_filtros('b_fhasta', $_POST['hasta']);
            $h = date('Y-m-d', strtotime($_POST['hasta']));
            $sql .= $where . " f.fecha <= '" . $h . "'";
        }

        /// Activamos filtros
        $this->filtros = $this->gesdoc->filtros;
        if (isset($this->filtros['tipodoc']))
        {
            $this->tipodoc = $this->filtros['tipodoc'];
        }
        if (isset($this->filtros['b_adjunto']))
        {
            $this->adj = $this->filtros['b_adjunto'];
        }
        if (isset($this->filtros['b_fhasta']))
        {
            $this->hasta = $this->filtros['b_fhasta'];
        }
        if (isset($this->filtros['b_fdesde']))
        {
            $this->desde = $this->filtros['b_fdesde'];
        }
        
        if (!$this->tipodoc)
        {
            $this->tipodoc = 'FC';
        }
        
        /// Variables para las consultas
        if ($this->tipodoc == 'FC')
        {
            $this->desc_tipodoc = 'Factura Cliente';
            $tabla              = 'facturascli';
            $id                 = 'idfactura';
            $id2                = 'idfactura';
            $mainpage           = 'ventas';
        } else if ($this->tipodoc == 'FP')
        {
            $this->desc_tipodoc = 'Factura Proveedor';
            $tabla              = 'facturasprov';
            $id                 = 'idfactura';
            $id2                = 'idfacturaprov';
            $mainpage           = 'compras';
        } else if ($this->tipodoc == 'AC')
        {
            $this->desc_tipodoc = 'Albarán Cliente';
            $tabla              = 'albaranescli';
            $id                 = 'idalbaran';
            $id2                = 'idalbaran';
            $mainpage           = 'ventas';
        } else if ($this->tipodoc == 'AP')
        {
            $this->desc_tipodoc = 'Albarán Proveedor';
            $tabla              = 'albaranesprov';
            $id                 = 'idalbaran';
            $id2                = 'idalbaranprov';
            $mainpage           = 'compras';
        }

        if ($this->adj != '0')
        {
            $wh = '';
            if ($sql)
            {
                $wh = ' AND ' . $sql;
            }
        } else
        {
            $wh = '';
            if ($sql)
            {
                $wh = ' WHERE ' . $sql;
            }
        }

        /// Selector de opciones de b_adjunto    
        if ($this->adj == '1')
        {
            $sql_res   = 'documentosfac as d, ' . $tabla . ' as f WHERE f.' . $id . ' = d.' . $id2 . ' ';
            $sql_pages = ', documentosfac as d WHERE f.' . $id . ' = d.' . $id2 . ' ';
        } else if ($this->adj == '2')
        {
            $sql_res   = $tabla . ' as f LEFT JOIN documentosfac as d ON f.' . $id . ' = d.' . $id2 . ' WHERE d.' . $id2 . ' IS NULL ';
            $sql_pages = ' LEFT JOIN documentosfac as d ON f.' . $id . ' = d.' . $id2 . ' WHERE d.' . $id2 . ' IS NULL ';
        } else
        {
            $sql_res   = $tabla . ' as f LEFT JOIN documentosfac as d ON d.' . $id2 . ' = f.' . $id . ' ';
            $sql_pages = '';
        }

        /// Obtenemos listado de documentos
        $res              = $this->gesdoc->get_documents($sql, $sql_res, $sql_pages, $this->offset, $this->adj, $tabla, $id);
        $this->resultados = $res[0];
        $this->pages      = $res[1];

        $pagina = explode('id', $id);

        if (!empty($this->resultados))
        {
            foreach ($this->resultados as $i => $r)
            {
                $this->resultados[$i]['extension'] = substr(strrchr($r['doc_nombre'], '.'), 1);
                $this->resultados[$i]['fecha']     = date('d-m-Y', strtotime($this->resultados[$i]['fecha']));
                $this->resultados[$i]['doc_fecha'] = date('d-m-Y', strtotime($this->resultados[$i]['doc_fecha']));
                $this->resultados[$i]['doc_url']   = 'index.php?page=' . $mainpage . '_' . $pagina[1] . '&id=' . $r["$id"];
            }
        }
    }

    public function paginas()
    {
        $url = $this->url() . $this->url;

        $paginas = array();
        $i       = 0;
        $num     = 0;
        $actual  = 1;
        $total   = intval($this->pages[0]['total']);

        /// añadimos todas la página
        while ($num < $total)
        {
            $paginas[$i] = array(
                'url' => $url . "&offset=" . ($i * FS_ITEM_LIMIT),
                'num' => $i + 1,
                'actual' => ($num == $this->offset)
            );

            if ($num == $this->offset)
            {
                $actual = $i;
            }

            $i++;
            $num += FS_ITEM_LIMIT;
        }

        /// ahora descartamos
        foreach ($paginas as $j => $value)
        {
            $enmedio = intval($i / 2);

            /**
             * descartamos todo excepto la primera, la última, la de enmedio,
             * la actual, las 5 anteriores y las 5 siguientes
             */
            if (($j > 1 AND $j < $actual - 5 AND $j != $enmedio) OR ( $j > $actual + 5 AND $j < $i - 1 AND $j != $enmedio))
            {
                unset($paginas[$j]);
            }
        }

        if (count($paginas) > 1)
        {
            return $paginas;
        } else
        {
            return array();
        }
    }

    protected function generate_zip()
    {

        $sql = "";
        if (isset($_REQUEST['desde']) && isset($_REQUEST['hasta']) && $_REQUEST['desde'] != '' && $_REQUEST['hasta'] != '')
        {
            $sql = " AND f.fecha >= '" . date('Y-m-d', strtotime($_REQUEST['desde'])) . "' AND f.fecha <= '" . date('Y-m-d', strtotime($_REQUEST['hasta'])) . "'";
        }

        if (isset($_POST['tipodoc']) && $_POST['tipodoc'] != '')
        {
            if ($_POST['tipodoc'] == 'FC')
            {
                $this->resultados = $this->get_documents_zip('facturascli as f', 'idfactura', 'idfactura', $sql);
            }

            if ($_POST['tipodoc'] == 'FP')
            {
                $this->resultados = $this->get_documents_zip('facturasprov as f', 'idfactura', 'idfacturaprov', $sql);
            }

            if ($_POST['tipodoc'] == 'AC')
            {
                $this->resultados = $this->get_documents_zip('albaranescli as f', 'idalbaran', 'idalbaran', $sql);
            }

            if ($_POST['tipodoc'] == 'AP')
            {
                $this->resultados = $this->get_documents_zip('albaranesprov as f', 'idalbaran', 'idalbaranprov', $sql);
            }

            /// Recorremos resultados para añadir adjuntos al archivo zip
            foreach ($this->resultados as $r)
            {
                $filename   = $this->compose_filename($_POST['tipodoc'], $r);
                $zip_create = $this->download_zip($r['ruta'], $filename);
            }

            if ($zip_create)
            {
                header("Content-Type: application/zip");
                header("Content-Transfer-Encoding: Binary");
                header("Content-Length: " . filesize('documentos.zip'));
                header("Content-Disposition: attachment; filename=\"" . basename('documentos.zip') . "\"");
                readfile('documentos.zip');

                if (file_exists('documentos.zip'))
                {
                    unlink('documentos.zip');
                }
            } else
            {
                $this->new_error_msg('Ha ocurrido un problema al generar el zip');
            }
        }
    }

    /**
     * 
     * @param string $tipodoc       
     * @param array  $datos     
     * 
     * @return string $filename
     */
    protected function compose_filename($tipodoc, $datos)
    {
        $fsvar                = new fs_var();
        $filename             = '';
        $c_nombre_original    = $fsvar->simple_get('gdoc_nombre_original');
        $c_numero2            = $fsvar->simple_get('gdoc_numero2');
        $c_codigo_facturacion = $fsvar->simple_get('gdoc_codigo_facturacion');
        $c_fecha_facturacion  = $fsvar->simple_get('gdoc_fecha_facturacion');

        $n               = pathinfo($datos['nombre']);
        $nombre_original = $n['filename'];
        $extension       = $n['extension'];

        if ($c_fecha_facturacion)
        {
            $filename .= $datos['fecha_facturacion'];
        }
        if ($c_codigo_facturacion)
        {
            $filename .= '_' . $datos['codigo_facturacion'];
        }
        if ($c_numero2)
        {
            if (isset($datos['numero2']) && $datos['numero2'] != '')
            {
                $filename .= '_' . $datos['numero2'];
            } else
            {
                $filename .= '_' . '---';
            }
        }
        if ($c_nombre_original)
        {
            $filename .= '_' . $nombre_original;
        }
        $filename .= '.' . $extension;
        
        if ($filename == '.'.$extension)
        {
            $filename = $nombre_original . '.' . $extension;
        }

        return $filename;
    }

    public function get_documents_zip($tabla, $id, $id2, $sql)
    {
        if ($id2 == 'idfacturaprov' || $id2 == 'idalbaranprov')
        {
            $resultados = $this->db->select("SELECT f.fecha as fecha_facturacion, f.codigo as codigo_facturacion, d.* FROM " . $tabla . ", documentosfac as d WHERE d." . $id2 . " = f." . $id . " " . $sql . ";");
        } else
        {
            $resultados = $this->db->select("SELECT f.fecha as fecha_facturacion, f.codigo as codigo_facturacion, f.numero2 as numero2, d.* FROM " . $tabla . ", documentosfac as d WHERE d." . $id2 . " = f." . $id . " " . $sql . ";");
        }

        return $resultados;
    }

    private function download_zip($ruta, $filename)
    {
        /// desactivamos el motor de plantillas
        $this->template = FALSE;

        $zip = new ZipArchive;
        if ($zip->open('documentos.zip', ZipArchive::CREATE) === TRUE)
        {
            $zip->addFile($ruta, $filename);
            $zip->close();
        }

        return true;
    }

}
