<?php

namespace A2Dborm;

abstract class A2Table {
	
	protected $PK = NULL;
	protected $Columns = NULL;
	protected $ColumnsInfo = NULL;
	protected $Action = NULL;
	protected $Join = NULL;
	protected $Commands = NULL;
	 
    public function __construct(){
    }
	
	function __get( $property ){
		if(in_array($property, $this->getColumns()->getKeys())){
			return($this->getClassName().".".$property);
		}
	}
	
	public function getRow(){
		return($this->getColumns()->getAll());
	}
	public function loadRow(){
		if(!is_null($this->getPKValue())){
			$this->getColumns()->clearValues();
			$this->setAction(\A2Dborm::SELECT);
			$values=$this->compileColumns();			
			$SQL="SELECT ".implode(", ",$values)." FROM ".$this->getClassName()." WHERE ".$this->getClassName().".".$this->getPK()."='".$this->getPKValue()."' LIMIT 1";			
			\A2Debug::Info($SQL);
			\A2Sql::getInstance()->query($SQL);
			$res=\A2Sql::getInstance()->getResult()->returnSingleResultAssocArray();
			
			if( empty($res) )
				\A2Debug::Warn("No data found for PK '".$this->getPK()."' = '".$this->getPKValue()."'!");
			else{
				$rowData=new \A2Collection();
				$rowData->setCollection($res);
				$this->addColumns($rowData);
			}
		}
		else
			\A2Debug::Warn("PK '".$this->getPK()."' must be defined to load data!");
	}
	public function deleteRow(){
		$SQL="DELETE FROM ".$this->getClassName()." WHERE ".$this->getPK()."='".$this->getPKValue()."' LIMIT 1";
		\A2Sql::getInstance()->query($SQL);	
	}
	public function saveRow(){
            if($this->getPKValue()==NULL){
                $this->setAction(\A2Dborm::INSERT);
                $values=$this->compileColumns();
                $insertFields=$this->getColumns()->getKeys();
                array_shift($insertFields);
                $SQL = "INSERT INTO ".$this->getClassName()." (".implode(", ",$insertFields).") VALUES (".implode(", ",$values).")";
                \A2Sql::getInstance()->query($SQL);

                \A2Sql::getInstance()->query("SELECT ".$this->getPK()." FROM ".$this->getClassName()." ORDER BY ".$this->getPK()." DESC LIMIT 1");
                $this->setPKValue(\A2Sql::getInstance()->getResult()->returnPlain());
            }
            else{
                $this->setAction(\A2Dborm::UPDATE);
                $values=$this->compileColumns();
                $SQL = "UPDATE ".$this->getClassName()." SET ".implode(", ",$values)." WHERE ".$this->getPK()."='".$this->getPKValue()."'";

                \A2Sql::getInstance()->query($SQL);
            }
	}
	public function compileColumns(){
		$values=array();
		if($this->getAction()==\A2Dborm::INSERT){
			foreach($this->getColumns()->getKeys() as $columnName){
				if($columnName!=$this->getPK()){
					$columnInfo=$this->getColumnsInfo()->get($columnName);
					if(!isset($columnInfo["Visible"]) || $columnInfo["Visible"]=="true"){
						if($columnInfo["Type"]=="custom")
							$values[]=call_user_func(array($this,"get".ucfirst($columnName)));
						else if($columnInfo["Type"]=="now")
							$values[]="NOW()";
						else if($columnInfo["Type"]=="float" || $columnInfo["Type"]=="decimal")
							$values[]=$this->convertFloats(call_user_func(array($this,"get".ucfirst($columnName))));
						else if($columnInfo["Type"]=="timestamp" || $columnInfo["Type"]=="datetime" || $columnInfo["Type"]=="date")
							$values[]=$this->convertToTimestampMYSQLESCAPED(call_user_func(array($this,"get".ucfirst($columnName))));
						else
							$values[]="'".call_user_func(array($this,"get".ucfirst($columnName)))."'";
					}
					
				}
			}
		}
		else if($this->getAction()==\A2Dborm::UPDATE){
			foreach($this->getColumns()->getKeys() as $columnName){
				if($columnName!=$this->getPK()){
					$columnInfo=$this->getColumnsInfo()->get($columnName);
					if(!isset($columnInfo["Visible"]) || $columnInfo["Visible"]=="true"){
						if($columnInfo["Type"]=="custom")
							$values[]=$columnName."=".call_user_func(array($this,"get".ucfirst($columnName)));
						else if($columnInfo["Type"]=="now")
							$values[]=$columnName."="."NOW()";
						else if($columnInfo["Type"]=="float" || $columnInfo["Type"]=="decimal")
							$values[]=$columnName."=".$this->convertFloats(call_user_func(array($this,"get".ucfirst($columnName))));
						else if($columnInfo["Type"]=="timestamp" || $columnInfo["Type"]=="datetime" || $columnInfo["Type"]=="date")
							$values[]=$columnName."=".$this->convertToTimestampMYSQLESCAPED(call_user_func(array($this,"get".ucfirst($columnName))));
						else
							$values[]=$columnName."="."'".call_user_func(array($this,"get".ucfirst($columnName)))."'";
					}
				}
			}
		}
		else if($this->getAction()==\A2Dborm::SELECT){
			foreach($this->getColumns()->getKeys() as $columnName){
				$columnInfo=$this->getColumnsInfo()->get($columnName);
				if(!isset($columnInfo["Visible"]) || $columnInfo["Visible"]=="true"){
					if($columnInfo["Type"]=="custom")
						$values[]=$this->getClassName().".".$columnName;
					else if($columnInfo["Type"]=="now")
						$values[]=$this->getClassName().".".$columnName;
					else if($columnInfo["Type"]=="timestamp" || $columnInfo["Type"]=="datetime" || $columnInfo["Type"]=="date")
						$values[]=$this->getClassName().".".$columnName;
					else
						$values[]=$this->getClassName().".".$columnName;
				}
			}
		}
		else
			\A2Debug::Error("'$this->getAction()' is an INVALID \A2Dborm\Action statement (it must be one of the followings: INSERT, UPDATE, SELECT");
		
		return($values);
	}
	public function compileWhere(){
		$values=array();
		$this->populateColumns();
				
		foreach($this->getColumns()->getKeys() as $columnName){
			$columnInfo=$this->getColumnsInfo()->get($columnName);
			if(is_numeric($this->getColumns()->get($columnName)) || $this->getColumns()->get($columnName)!=""){
				if($columnInfo["Type"]=="timestamp" || $columnInfo["Type"]=="datetime" || $columnInfo["Type"]=="date")
					$values[]=$this->getClassName().".".$columnName." ".$this->getCommands()->get($columnName)." ".$this->convertToTimestampMYSQLESCAPED(call_user_func(array($this,"get".ucfirst($columnName))));
				else
					$values[]=$this->getClassName().".".$columnName." ".$this->getCommands()->get($columnName)." '".\A2SQL::getInstance()->realEscapeString(call_user_func(array($this,"get".ucfirst($columnName))))."'";
			}
		}
		return($values);
	}
	public function compileUpdate(){
		return($this->compileWhere());
	}
	public function compileJoins(){
		$values=array();
		foreach($this->getColumns()->getKeys() as $columnName){
			$columnInfo=$this->getColumnsInfo()->get($columnName);
			if(!isset($columnInfo["Visible"]) || $columnInfo["Visible"]=="true"){
				if($columnInfo["Type"]=="custom")
					$values[]=$this->getClassName().".".$columnName;
				else if($columnInfo["Type"]=="now")
					$values[]=$this->getClassName().".".$columnName;
				else if($columnInfo["Type"]=="timestamp")
					$values[]=$this->getClassName().".".$columnName;
				else
					$values[]=$this->getClassName().".".$columnName;
			}
		}
		return($values);
	}
	public function compileModified(){
		$values=new \A2Collection();		
		$this->populateColumns();
		foreach($this->getColumns()->getKeys() as $column){
			if(is_numeric($this->getColumns()->get($column)) || $this->getColumns()->get($column)!=""){
				$values->set($column,$this->getColumns()->get($column));
			}
		}
		return($values);
	}

	public function setView($columnsVisibles){
		if(is_array($columnsVisibles)){
			foreach($this->getColumns()->getKeys() as $columnName){
				if($columnName!=$this->getPK()){	
					$columnInfo=$this->getColumnsInfo()->get($columnName);				
					if(in_array($columnName,$columnsVisibles)){
						$columnInfo["Visible"]="true";
					}
					else
						$columnInfo["Visible"]="false";	
					
					$this->getColumnsInfo()->set($columnName,$columnInfo);
				}
			}
		}
		else
			\A2Debug::Error("Argument must be of type array()");
	}
	public function selectAllColumns(){
		foreach($this->getColumns()->getKeys() as $columnName){
			if($columnName!=$this->getPK()){
				$columnInfo=$this->getColumnsInfo()->get($columnName);
				if($columnInfo==NULL)
					\A2Debug::Warn("The column '$columnName' does not exist in '".$this->getClassName()."'");
				else
					$columnInfo["Visible"]="true";
			}
		}
	}
	public function hideAllColumns(){
		foreach($this->getColumns()->getKeys() as $columnName){
			if($columnName!=$this->getPK()){
				$columnInfo=$this->getColumnsInfo()->get($columnName);
				if($columnInfo==NULL)
					\A2Debug::Warn("The column '$columnName' does not exist in '".$this->getClassName()."'");
				else
					$columnInfo["Visible"]="false";
			}
		}
	}
	
	public function setJoin($tableAColumnX,$tableBColumnX){
		$this->Join=$this->getAction()." ".$this->getClassName()." ON ".$tableAColumnX." = ".$tableBColumnX;
	}
	public function getJoin(){
		return($this->Join);
	}
	public function populateProperties(){
	}
	public function populateColumns(){
	}

	public function addColumns($value=NULL) {
		if( !is_null($value) && is_object($value) && method_exists($value,"getAll") && get_class($value)=="A2Collection" && sizeof($value->getAll())>0 ){			
			$this->getColumns()->add($value->getAll());
			$this->populateProperties();
		}
		else
			\A2Debug::Error("A value must be defined and must be an A2Collection Object type!");
	}
	public function setColumns($value=NULL) {
		if( !is_null($value) && is_object($value) && method_exists($value,"getAll") && get_class($value)=="A2Collection" && sizeof($value->getAll())>0 ){			
			$this->getColumns()->setCollection($value->getAll());
			$this->populateProperties();
		}
		else
			\A2Debug::Error("A value must be defined and must be an A2Collection Object type!");
	}	
	public function getColumns(){
		return($this->Columns);	
	}
	
	public function setColumnsInfo($value){
		$this->ColumnsInfo=$value;	
	}	
	public function getColumnsInfo(){
		return($this->ColumnsInfo);	
	}

	public function setColumnInfoType($name, $value){
		$columnInfo=$this->getColumnsInfo()->get($name);
		$columnInfo["Type"]=(string) $value;
		$this->getColumnsInfo()->set($name, $columnInfo);
	}	
	public function getColumnInfoType($name){
		$columnInfo=$this->getColumnsInfo()->get($name);
		return($columnInfo["Type"]);	
	}

	public function setPK($value){
		$this->PK=$value;	
	}	
	public function getPK(){
		return($this->PK);	
	}
	public function setPKValue($value){
		call_user_func(array($this,"set".ucfirst($this->getPK())),$value);	
	}	
	public function getPKValue(){
		call_user_func(array($this,"get".ucfirst($this->getPK())));	
	}
	public function setAction($value){
		$this->Action=$value;	
	}	
	public function getAction(){
		return($this->Action);	
	}
	public function setCommands($value){
		$this->Commands=$value;	
	}	
	public function getCommands(){
		return($this->Commands);	
	}

	public function getClassName(){	   
		$class = explode('\\', get_class($this));
		return $class[count($class) - 1];	
	}
	
	public static function convertToTimestamp($dateString){
		$date = explode("/",$dateString);
		if(isset($date[2]))
			return("$date[2]-$date[1]-$date[0] 0:00:00");
		else{
			\A2Debug::Error("date passed '$dateString' is not valid!");
			return("");
		}
	} 
	
	public static function convertFloats($number){
		if(strpos($number, ",")===false)
			return("'".$number."'");
		else{
			// por si acaso, le sacamos la coma de los miles, si la tiene...
			$number=str_replace(".","",$number);
			//
			return("'".str_replace(",",".",$number)."'");
		}
	}
	
	public static function convertToTimestampMYSQLESCAPED($dateString){
		if($dateString=="NOW()")
			return("NOW()");
		
		if($dateString!=""){
			$sourceFormat = explode("-",$dateString);
			$date = explode("/",$dateString);
			$dateTime = explode(" ",$dateString);
			if(isset($sourceFormat[1]))
				return("'".$dateString."'");
			else if(isset($dateTime[1]) && isset($date[2])){
				return("'".substr($date[2],0,4)."-$date[1]-$date[0] ".$dateTime[1]."'");
			}
			else if(isset($date[2]))
				return("'"."$date[2]-$date[1]-$date[0] 0:00:00"."'");
			else{
				\A2Debug::Error("date passed '$dateString' is not valid!");
				return("''");
			}
		}
		else
			return("0");
	} 
	public static function convertToDateTime($timestamp){
		return date('d/m/Y', $timestamp);
	}

	
}

?>