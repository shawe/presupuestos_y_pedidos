<?php

/**
 * Representa el estado de un documento
 */
class estado_documento extends fs_model
{
    /**
     * Clave primaria.
     * @var int
     */
    public $id;
    
    /**
     * Al tipo de documento al que va asociado
     * ventas_presupuesto, ventas_pedido, ventas_albaran, ventas_factura
     * compras_pedido, compras_albaran, compras_factura
     * @var string (20)
     */
    public $documento;

    /**
     * Identificador del estado
     * @var int
     */
    public $status;
    
    /**
     * Nombre del estado
     * @var string (20)
     */
    public $nombre;
    
    /**
     * Indica si el campo está bloqueado
     * @var bool
     */
    public $bloqueado;
    
    /**
     * Constructor del modelo
     * 
     * @param array/bool $data
     */
    public function __construct($data = FALSE)
    {
        parent::__construct('estados_documentos', 'plugins/estados_documentos/');
        if ($data) {
            $this->id = $data['id'];
            $this->documento = $data['documento'];
            $this->status = $data['status'];
            $this->nombre = $data['nombre'];
            $this->bloqueado = $data['bloqueado'];
        } else {
            $this->id = NULL;
            $this->documento = NULL;
            $this->status = NULL;
            $this->nombre = NULL;
            $this->bloqueado = FALSE;
        }
    }
    
    /**
     * Inserta los datos por defecto de la tabla
     * 
     * @return string
     */
    protected function install()
    {
        $estados = [
            ['documento' => 'ventas_presupuesto', 'status' => 0, 'nombre' => 'Pendiente', 'bloqueado' => true ],
            ['documento' => 'ventas_presupuesto', 'status' => 1, 'nombre' => 'Aprobado', 'bloqueado' => true ],
            ['documento' => 'ventas_presupuesto', 'status' => 2, 'nombre' => 'Rechazado', 'bloqueado' => true ],
            ['documento' => 'ventas_pedido', 'status' => 0, 'nombre' => 'Pendiente', 'bloqueado' => true ],
            ['documento' => 'ventas_pedido', 'status' => 1, 'nombre' => 'Aprobado', 'bloqueado' => true ],
            ['documento' => 'ventas_pedido', 'status' => 2, 'nombre' => 'Rechazado', 'bloqueado' => true ],
            ['documento' => 'ventas_pedido', 'status' => 3, 'nombre' => 'En trámite', 'bloqueado' => false ],
            ['documento' => 'ventas_pedido', 'status' => 4, 'nombre' => 'Back orders', 'bloqueado' => false ]
        ];
        $sql = '';
        foreach ($estados as $pos => $estado) {
            $sql .= 'INSERT INTO ' . $this->table_name
                    . ' (id, documento, status, nombre, bloqueado)'
                    . ' VALUES (' 
                    . $this->var2str($pos+1)
                    . ',' . $this->var2str($estado['documento'])
                    . ',' . $this->var2str($estado['status'])
                    . ',' . $this->var2str($estado['nombre'])
                    . ',' . $this->var2str($estado['bloqueado'])
                    . ');';
        }
        
        return $sql;
    }
    
    /**
     * Devuelve la URL
     * 
     * @return string
     */
    public function url()
    {
        if (is_null($this->id)) {
            return "index.php?page=estados_documentos";
        }

        return "index.php?page=estado_documento&id=" . urlencode($this->id);
    }
    
    /**
     * Devuelve el elemento con id=$id
     * 
     * @param string $id
     * @return boolean|self
     */
    public function get($id)
    {
        $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($id) . ";");
        if ($data) {
            return new self($data[0]);
        }

        return FALSE;
    }
    
    /**
     * Devuelve una array con los estados para el tipo de documento indicado
     * 
     * @param string $doc
     * @return array
     */
    public function get_by_document($doc)
    {
        /// lee la lista de la caché
        $list = $this->cache->get_array('m_estados_' . $doc . '_all');
        if (!$list) {
            /// si la lista no está en caché, leemos de la base de datos
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " WHERE documento = " . $this->var2str($doc) .  " ORDER BY id ASC;");
            if ($data) {
                foreach ($data as $d) {
                    $list[] = new self($d);
                }
            }
            /// guardamos la lista en caché
            $this->cache->set('m_estados_' . $doc . '_all', $list);
        }
        
        return $list;
    }
    
    /**
     * Devuelve un array con todas los estados
     * 
     * @return \self
     */
    public function all()
    {
        /// lee la lista de la caché
        $list = $this->cache->get_array('m_estados_doc_all');
        if (!$list) {
            /// si la lista no está en caché, leemos de la base de datos
            $data = $this->db->select("SELECT * FROM " . $this->table_name . " ORDER BY id ASC;");
            if ($data) {
                foreach ($data as $d) {
                    $list[] = new self($d);
                }
            }

            /// guardamos la lista en caché
            $this->cache->set('m_estados_doc_all', $list);
        }

        return $list;
    }
    
    /**
     * Devuelve un array con todas los estados
     * 
     * @return array
     */
    public function all_tipo_documentos()
    {
        $list = [];
        /// si la lista no está en caché, leemos de la base de datos
        $data = $this->db->select("SELECT DISTINCT(documento) FROM " . $this->table_name . " ORDER BY id ASC;");
        if ($data) {
            foreach ($data as $d) {
                $list[] = $d['documento'];
            }
        }

        return $list;
    }
    
    /**
     * Devuelve si el registro existe en la tabla
     * 
     * @return bool
     */
    public function exists()
    {
        if (is_null($this->id)) {
            return FALSE;
        }

        return $this->db->select("SELECT * FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";");
    }

    /**
     * Comprueba los datos, devuelve TRUE si son correctos
     * 
     * @return bool
     */
    public function test()
    {
        $status = FALSE;

        $this->documento = $this->no_html($this->documento);
        $this->nombre = $this->no_html($this->nombre);

        if (strlen($this->documento) < 1 || strlen($this->documento) > 20) {
            $this->new_error_msg("Tipo de documento no válido (debe tener entre 1 y 20 carácteres).");
        } else if (strlen($this->nombre) < 1 || strlen($this->nombre) > 20) {
            $this->new_error_msg("Nombre del estado no válido (debe tener entre 1 y 20 carácteres).");
        } else if (!is_numeric($this->status)) {
            $this->new_error_msg("El campo estado debe ser numérico.");
        } else {
            $status = TRUE;
        }

        return $status;
    }

    /**
     * Guarda los datos en la tabla
     * 
     * @return bool
     */
    public function save()
    {
        if ($this->test()) {
            $this->clean_cache();

            if ($this->exists()) {
                $sql = "UPDATE " . $this->table_name
                        . " SET documento = " . $this->var2str($this->documento)
                        . ", status = " . $this->var2str($this->status)
                        . ", nombre = " . $this->var2str($this->nombre)
                        . ", bloqueado = " . $this->var2str($this->bloqueado)
                        . "  WHERE id = " . $this->var2str($this->id) . ";";
            } else {
                $sql = 'INSERT INTO ' . $this->table_name
                    . ' (documento, status, nombre, bloqueado)'
                    . ' VALUES (' 
                    . $this->var2str($this->documento)
                    . ',' . $this->var2str($this->status)
                    . ',' . $this->var2str($this->nombre)
                    . ',' . $this->var2str($this->bloqueado)
                    . ');';
            }

            return $this->db->exec($sql);
        }

        return FALSE;
    }

    /**
     * Elimina el registro de la tabla
     * 
     * @return boolean
     */
    public function delete()
    {
        $this->clean_cache();
        $sql = "DELETE FROM " . $this->table_name . " WHERE id = " . $this->var2str($this->id) . ";";

        return $this->db->exec($sql);
    }

    /**
     * Limpia la caché
     */
    private function clean_cache()
    {
        $this->cache->delete('m_estados_docs_all');
        
        foreach ($this->all_tipo_documentos() as $tipo) {
            $this->cache->delete('m_estados_' . $tipo . '_all');
        }
    }
}
