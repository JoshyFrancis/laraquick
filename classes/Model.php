<?php
Class Model{
	protected $names=[];
	protected $values=[];
	public $ID=null;
	protected $table='users';
	protected $primaryKey='ID';
	protected $fillable=[];
	public function __set($name, $value){
		if($name===$this->primaryKey){
			return;
		}
		$p=array_search($name,$this->names);
		if($p===false){
			$this->names[]=$name;
			$p=count($this->names)-1;
		}
		
		$this->values[$p]=$value;
    }
    public function __get($name){
		if($name===$this->primaryKey){
			return $this->ID;
		}
		$p=array_search($name,$this->names);
		if($p!==false){
			return $this->values[$p];
		}
		return null;
    }
    public static function create($data){
		$model=new self;
		foreach($data as $key=>$val){
			$model->{$key}=$val;
		}
		return $model->save();
	}
    public function save(){
		if($this->ID===null){
			$sql='INSERT INTO '.$this->table.'(';
			$c=0;
			$values='';
			foreach($this->names as $name){
				if($c>0){
					$sql.=',';
					$values.=',';
				}
				$sql.=$name;
				$values.='?';
				$c+=1;
			}
			$sql.=') VALUES(';
			$sql.=$values;
			$sql.=')';
			DB::insert($sql,$this->values);
			$this->ID=DB::lastInsertId();
		}else{
			$sql='UPDATE '.$this->table.' SET ';
			$c=0;
			foreach($this->names as $name){
				if($c>0){
					$sql.=',';
				}
				$sql.=$name.'=?';
				$c+=1;
			}
			$sql.=' where '.$this->primaryKey.'=?';
			$this->values[]=$this->ID;
			DB::update($sql,$this->values);
			array_pop($this->values);
		}
		return $this;
	}
	public static function find($ID){
		$model=new self;
		$fillable=$this->fillable+$this->names;
			$sql='SELECT ';
			$c=0;
			foreach($fillable as $name){
				if($c>0){
					$sql.=',';
				}
				$sql.=$name;
				$c+=1;
			}
			$sql.=' FROM '.$this->table.' where '.$this->primaryKey.'=?';

		$rows =DB::select($sql ,[$ID] );
		if(count($rows)>0){
			foreach($fillable as $name){
				$model->{$name}=$rows[0]->{$name};
			}
		}
		return $model;
	}
}
