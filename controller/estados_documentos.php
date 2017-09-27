<?php

/**
 * Estados documentos
 */
class estados_documentos extends fs_controller
{
    public $estado_doc;
    public $documentos;
    public $todos;
    /**
     * Constructor de la clase
     */
    public function __construct()
    {
        parent::__construct(__CLASS__, 'Estados de documentos', 'ventas');
    }

    /**
     * Parte privada del controlador
     */
    public function private_core()
    {
        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on($this->class_name);
        
        $this->estado_doc = new estado_documento();
        
        if ($id = filter_input(INPUT_POST, 'id')) {
            $this->editar($id);
        } elseif ($id = filter_input(INPUT_GET, 'delete')) {
            $this->eliminar($id);
        } elseif (filter_input(INPUT_POST, 'status')) {
            $this->crear();
        }
        
        $this->documentos = $this->estado_doc->all_tipo_documentos();
    }
    
    /**
     * Editar estado
     * 
     * @param int $id
     */
    private function editar($id)
    {
        $est0 = $this->estado_doc->get($id);

        $est0->nombre = filter_input(INPUT_POST, 'nombre');

        if ($est0->save()) {
            $this->new_message('Datos guardados correctamente.');
        } else {
            $this->new_error_msg('Error al guardar los datos.');
        }
    }
    
    /**
     * Eliminar estado
     * 
     * @param int $id
     */
    private function eliminar($id)
    {
        $est0 = $this->estado_doc->get($id);
        if ($est0) {
            if (!$this->allow_delete) {
                $this->new_error_msg('No tienes permiso para eliminar en esta página.');
            } else if ($est0->delete()) {
                $this->new_message('Estado eliminado correctamente.');
            } else {
                $this->new_error_msg('Error al eliminar el estado.');
            }
        } else {
            $this->new_error_msg('Estado no encontrada.');
        }
    }
    
    /**
     * Crear estado
     * 
     * @param int $id
     */
    private function crear()
    {
        $est0 = new estado_documento();
        $est0->documento = filter_input(INPUT_POST, 'documento');
        $est0->status = filter_input(INPUT_POST, 'status');
        $est0->nombre = filter_input(INPUT_POST, 'nombre');

        if ($est0->save()) {
            $this->new_message('Datos insertados correctamente.');
        } else {
            $this->new_error_msg('Error al guardar los datos.');
        }
    }
}
