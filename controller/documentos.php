<?php
/*
 * This file is part of FacturaScripts
 * Copyright (C) 2015-2018  Carlos Garcia Gomez  neorazorx@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Description of documentos
 *
 * @author carlos
 */
class documentos extends fs_controller
{

    public $documentos;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Documentos', 'ventas', FALSE, FALSE);
    }

    protected function private_core()
    {
        $this->share_extension();

        $this->check_documentos();
        $this->documentos = array();

        if (isset($_GET['folder']) AND isset($_GET['id'])) {
            if (isset($_POST['upload'])) {
                $this->upload_documento();
            } else if (isset($_GET['delete'])) {
                $this->delete_documento();
            }

            $this->documentos = $this->get_documentos();
            $this->update_documento();
        } else {
            /**
             * Nos aseguramos que la tabla se cree o se compruebe durante la instalación.
             * En la instalación se cargan todos los controladores del plugin, como no hay
             * parámetros, llega aquí.
             */
            $dofa = new documento_factura();
        }
    }

    private function upload_documento()
    {
        if (!is_uploaded_file($_FILES['fdocumento']['tmp_name'])) {
            $this->new_error_msg('Error al mover el archivo.');
            return;
        }

        if (substr(strtolower($_FILES['fdocumento']['name']), -4) === '.php') {
            $this->new_error_msg('No puedes subir archivos PHP.');
            return;
        }

        $nuevon = $this->random_string(6) . '_' . $_FILES['fdocumento']['name'];
        if (copy($_FILES['fdocumento']['tmp_name'], FS_MYDOCS . 'documentos/' . $nuevon)) {
            $doc = new documento_factura();
            $doc->ruta = 'documentos/' . $nuevon;
            $doc->nombre = $_FILES['fdocumento']['name'];
            $doc->tamano = filesize(getcwd() . '/' . FS_MYDOCS . $doc->ruta);
            $doc->usuario = $this->user->nick;

            if ($_GET['folder'] == 'facturascli') {
                $doc->idfactura = $_GET['id'];
            } else if ($_GET['folder'] == 'albaranescli') {
                $doc->idalbaran = $_GET['id'];
            } else if ($_GET['folder'] == 'pedidoscli') {
                $doc->idpedido = $_GET['id'];
            } else if ($_GET['folder'] == 'presupuestoscli') {
                $doc->idpresupuesto = $_GET['id'];
            } else if ($_GET['folder'] == 'facturasprov') {
                $doc->idfacturaprov = $_GET['id'];
            } else if ($_GET['folder'] == 'albaranesprov') {
                $doc->idalbaranprov = $_GET['id'];
            } else if ($_GET['folder'] == 'pedidosprov') {
                $doc->idpedidoprov = $_GET['id'];
            } else if ($_GET['folder'] == 'servicioscli') {
                $doc->idservicio = $_GET['id'];
            }

            if ($doc->save()) {
                $this->new_message('Documentos añadido correctamente.');
            } else {
                $this->new_error_msg('Error al asignar el archivo.');
                @unlink($doc->ruta);
            }
        }
    }

    private function delete_documento()
    {
        $doc0 = new documento_factura();

        $documento = $doc0->get($_GET['delete']);
        if ($documento) {
            if ($documento->delete()) {
                $this->new_message('Documento eliminado correctamente.');
                @unlink(FS_MYDOCS . $documento->ruta);
            } else {
                $this->new_error_msg('Error al eliminar el documento.');
            }
        } else {
            $this->new_error_msg('Documento no encontrado.');
        }
    }

    private function share_extension()
    {
        $extensiones = array(
            array(
                'name' => 'documentos_facturascli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_factura',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=facturascli'
            ),
            array(
                'name' => 'documentos_albaranescli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_albaran',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=albaranescli'
            ),
            array(
                'name' => 'documentos_pedidoscli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_pedido',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=pedidoscli'
            ),
            array(
                'name' => 'documentos_presupuestoscli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_presupuesto',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=presupuestoscli'
            ),
            array(
                'name' => 'documentos_servicioscli',
                'page_from' => __CLASS__,
                'page_to' => 'ventas_servicio',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=servicioscli'
            ),
            array(
                'name' => 'documentos_facturasprov',
                'page_from' => __CLASS__,
                'page_to' => 'compras_factura',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=facturasprov'
            ),
            array(
                'name' => 'documentos_albaranesprov',
                'page_from' => __CLASS__,
                'page_to' => 'compras_albaran',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=albaranesprov'
            ),
            array(
                'name' => 'documentos_pedidosprov',
                'page_from' => __CLASS__,
                'page_to' => 'compras_pedido',
                'type' => 'tab',
                'text' => '<span class="glyphicon glyphicon-file" aria-hidden="true" title="Documentos"></span>',
                'params' => '&folder=pedidosprov'
            ),
        );
        foreach ($extensiones as $ext) {
            $fsext = new fs_extension($ext);
            $fsext->save();
        }
    }

    public function url()
    {
        if (isset($_GET['folder']) AND isset($_GET['id'])) {
            return 'index.php?page=' . __CLASS__ . '&folder=' . $_GET['folder'] . '&id=' . $_GET['id'];
        } else
            return parent::url();
    }

    private function get_documentos()
    {
        $doc = new documento_factura();
        if ($_GET['folder'] == 'facturascli') {
            /// comprobamos los albaranes relacionados con esta factura
            $alba = new albaran_cliente();
            foreach ($alba->all_from_factura($_GET['id']) as $alb) {
                foreach ($doc->all_from('idalbaran', $alb->idalbaran) as $d) {
                    $d->idfactura = $_GET['id'];
                    $d->save();
                }
            }

            return $doc->all_from('idfactura', $_GET['id']);
        } else if ($_GET['folder'] == 'albaranescli') {
            if (class_exists('pedido_cliente')) {
                /// comprobamos los pedidos relacionados con este albarán
                $pedi = new pedido_cliente();
                foreach ($pedi->all_from_albaran($_GET['id']) as $ped) {
                    foreach ($doc->all_from('idpedido', $ped->idpedido) as $d) {
                        $d->idalbaran = $_GET['id'];
                        $d->save();
                    }
                }
            }

            return $doc->all_from('idalbaran', $_GET['id']);
        } else if ($_GET['folder'] == 'pedidoscli') {
            /// comprobamos los presupuestos relacionados con este pedido
            $presu = new presupuesto_cliente();
            foreach ($presu->all_from_pedido($_GET['id']) as $pre) {
                foreach ($doc->all_from('idpresupuesto', $pre->idpresupuesto) as $d) {
                    $d->idpedido = $_GET['id'];
                    $d->save();
                }
            }

            return $doc->all_from('idpedido', $_GET['id']);
        } else if ($_GET['folder'] == 'presupuestoscli') {
            return $doc->all_from('idpresupuesto', $_GET['id']);
        } else if ($_GET['folder'] == 'servicioscli') {
            return $doc->all_from('idservicio', $_GET['id']);
        } else if ($_GET['folder'] == 'facturasprov') {
            /// comprobamos los albaranes relacionados con esta factura
            $alba = new albaran_proveedor();
            foreach ($alba->all_from_factura($_GET['id']) as $alb) {
                foreach ($doc->all_from('idalbaranprov', $alb->idalbaran) as $d) {
                    $d->idfacturaprov = $_GET['id'];
                    $d->save();
                }
            }

            return $doc->all_from('idfacturaprov', $_GET['id']);
        } else if ($_GET['folder'] == 'albaranesprov') {
            if (class_exists('pedido_proveedor')) {
                /// comprobamos los pedidos relacionados con este albarán
                $pedi = new pedido_proveedor();
                foreach ($pedi->all_from_albaran($_GET['id']) as $ped) {
                    foreach ($doc->all_from('idpedidoprov', $ped->idpedido) as $d) {
                        $d->idalbaranprov = $_GET['id'];
                        $d->save();
                    }
                }
            }

            return $doc->all_from('idalbaranprov', $_GET['id']);
        } else if ($_GET['folder'] == 'pedidosprov') {
            return $doc->all_from('idpedidoprov', $_GET['id']);
        } else {
            return array();
        }
    }

    private function check_documentos()
    {
        if (!file_exists(FS_MYDOCS . 'documentos')) {
            mkdir(FS_MYDOCS . 'documentos');
        }

        if (isset($_GET['folder']) AND isset($_GET['id'])) {
            /// comprobamos la antigua ruta
            $folder = 'tmp/' . FS_TMP_NAME . 'documentos/' . $_GET['folder'] . '/' . $_GET['id'];
            if (file_exists($folder)) {
                foreach (scandir($folder) as $f) {
                    if ($f != '.' AND $f != '..') {
                        /// movemos a la nueva ruta
                        $nuevon = $this->random_string(6) . '_' . (string) $f;
                        if (rename($folder . '/' . $f, FS_MYDOCS . 'documentos/' . $nuevon)) {
                            $doc = new documento_factura();
                            $doc->ruta = 'documentos/' . $nuevon;
                            $doc->nombre = (string) $f;
                            $doc->tamano = filesize(getcwd() . '/' . $doc->ruta);
                            $doc->usuario = $this->user->nick;

                            if ($_GET['folder'] == 'facturascli') {
                                $doc->idfactura = $_GET['id'];
                            } else if ($_GET['folder'] == 'facturasprov') {
                                $doc->idfacturaprov = $_GET['id'];
                            }

                            if (!$doc->save()) {
                                $this->new_error_msg('Error al mover el archivo.');
                            }
                        } else {
                            $this->new_error_msg('Error al mover el archivo a la nueva ruta.');
                        }
                    }
                }
            }
        }
    }

    private function update_documento()
    {
        $numdocs = count($this->documentos);

        $documento = FALSE;
        if ($_GET['folder'] == 'facturascli') {
            $fact0 = new factura_cliente();
            $documento = $fact0->get($_GET['id']);
        } else if ($_GET['folder'] == 'albaranescli') {
            $alb0 = new albaran_cliente();
            $documento = $alb0->get($_GET['id']);
        } else if ($_GET['folder'] == 'pedidoscli') {
            $ped0 = new pedido_cliente();
            $documento = $ped0->get($_GET['id']);
        } else if ($_GET['folder'] == 'presupuestoscli') {
            $presu0 = new presupuesto_cliente();
            $documento = $presu0->get($_GET['id']);
        } else if ($_GET['folder'] == 'facturasprov') {
            $fact0 = new factura_proveedor();
            $documento = $fact0->get($_GET['id']);
        } else if ($_GET['folder'] == 'albaranesprov') {
            $alb0 = new albaran_proveedor();
            $documento = $alb0->get($_GET['id']);
        } else if ($_GET['folder'] == 'pedidosprov') {
            $ped0 = new pedido_proveedor();
            $documento = $ped0->get($_GET['id']);
        } else if ($_GET['folder'] == 'servicioscli') {
            $serv0 = new servicio_cliente();
            $documento = $serv0->get($_GET['id']);
        }

        if ($documento) {
            if ($numdocs != $documento->numdocs) {
                $documento->numdocs = $numdocs;
                $documento->save();
            }
        }
    }

    public function is_image($name)
    {
        $is_image = FALSE;
        $name = mb_strtolower($name, 'UTF-8');

        if (mb_substr($name, -4) == '.jpg' OR mb_substr($name, -5) == '.jpeg') {
            $is_image = TRUE;
        } else if (mb_substr($name, -4) == '.png' OR mb_substr($name, -4) == '.gif') {
            $is_image = TRUE;
        }

        return $is_image;
    }
}
