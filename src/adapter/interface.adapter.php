<?php

global $_PGOS;

$_PGOS = array();

abstract class PGOS_Interface
{
    private                     $__pgos_object_name;
    private                     $__pgos_object_data = array();
    private                     $__pgos_dynamic_data = array();
    private                     $__pgos_object_loaded = false;
    private                     $__pgos_object_changed = false;
    public function              &__get($k)
    {
        if(property_exists($this,$k))
        {
            return $this->{$k};
        }elseif(array_key_exists($k,$this->__pgos_dynamic_data))
        {
            return $this->__pgos_dynamic_data[$k];
        }else{
            throw new Exception('PGOS: Attempt to access '.$k.' of '.get_class($this).' that does not exist');
        }
    }
    public function              __set($k, $v)
    {
        if($this->__pgos_object_loaded)
            $this->__pgos_object_changed = true;
        
        if(property_exists($this,$k))
        {
            $this->{$k} = $v;
        }else{
            $this->__pgos_dynamic_data[$k] = $v;
        }
    }
    public function              __isset($k)
    {
        if(array_key_exists($k,$this->__pgos_dynamic_data))
        {
            return true;
        }else{
            return false;
        }
    }
    public function              __unset($k)
    {
        unset($this->__pgos_dynamic_data[$k]);
        $this->__pgos_object_changed = true;
    }
    protected function           ___set_object_registry()
    {
        global $_PGOS;
        if(!array_key_exists($this->__pgos_object_name,$_PGOS))
           $_PGOS[] = $this;
    }
    protected function           ___set_object_name()
    {
        $this->__pgos_object_name = get_class($this).'_'.crc32(get_class($this).json_encode($this->__pgos_object_data));
    }
    protected function           ___set_object_data()
    {
        foreach(get_object_vars($this) as $k => $v)
        {
            if(strpos($k,'__pgos') === 0) continue;
            $this->__pgos_object_data[$k] = $v;
        }
    }
    abstract public function     ___save_object();
    public function              ___save()
    {
        if($this->__pgos_object_changed)
            $this->___save_object();
    }
    abstract public function     ___load_object();
    protected function           __construct()
    {
        $this->___set_object_data();
        $this->___set_object_name();
        $this->___set_object_registry();
        $this->___load_object();
    }
    function                     __destruct()
    {
        if($this->__pgos_object_changed)
            $this->___save_object();
    }
}