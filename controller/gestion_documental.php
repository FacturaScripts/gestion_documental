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

        /// Gestion Documental Avanzada
        if (isset($_POST['zip']) && $_POST['zip'] != '')
        {
            $this->gesdoc->gestion_documental_avanzada();
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

        /// Selector de opciones de b_ajunto    
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

}
