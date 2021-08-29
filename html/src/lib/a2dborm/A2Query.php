<?php

namespace A2Dborm;

class A2Query {

	private $TableFrom=NULL;
	private $Tables=array();
	private $Action=NULL;
	private $OrderBy;
	private $GroupBy;
	private $CompiledQuery;
	private $Update=NULL;
	private $Where=NULL;
	private $Joins=NULL;
	private $Fields=NULL;
	private $UpdateFields=NULL;
	private $Result=NULL;
	private $TestingOnly=false;

	public function __construct(){
		$this->OrderBy = new \A2Collection();
		$this->GroupBy = new \A2Collection();
		$this->Where = new \A2Collection();		
	}

	/*
	function __call( $method, $args ){
		return call_user_func_array(array(&$this->obj, $method), $args );
	}*/
	
	public function addTables(){
		$args = func_get_args();
		foreach($args as $table){
			$tableClass=explode("\\",get_class($table));
			$this->Tables[]=$tableClass[1];
			$this->{$tableClass[1]}=$table;
		}
	}
	public function removeTable($table){
		$tableClass=explode("\\",get_class($table));
		if(in_array($tableClass, $this->getTables()))
			unset($this->Tables[$tableClass]);
	}
	public function getTables(){
		return($this->Tables);
	}

	public function executeQuery(){	
		$this->compileQuery();
		$ACCION=$this->getAction();
		
		if($this->getTestingOnly())
			\A2Debug::Info($this->getCompiledQuery(), "TESTING:::$ACCION - getCompiledQuery()");
		
		if($this->getAction()==\A2Dborm::UPDATE){
			try{
				$query="SELECT ".$this->getTableFrom()->getPK()." FROM ".$this->getTableFrom()->getClassName()." ".implode(" ",$this->getJoins())." WHERE ".implode(" AND ",$this->getWhere()->getAll())." ".$this->getGroupByValue()." ".$this->getOrderByValue();
				
				if($this->getTestingOnly())
					\A2Debug::Info($query, "TESTING:::$ACCION - Pre-select");
				
				\A2Sql::getInstance()->query($query);
				$selection=\A2Sql::getInstance()->getResult()->returnAssocArray();
				
				if($this->getTestingOnly())
					\A2Debug::Info($selection, "TESTING:::$ACCION - Pre-select result");
				
				foreach($selection as $selectionRow){
					$class='\A2Dborm\\'.$this->getTableFrom()->getClassName();
					$object=new $class;
					$object->setPKValue($selectionRow["id"]);
					
					if($this->getTestingOnly())
						\A2Debug::Info($object->getPKValue(), "TESTING:::$ACCION - row getPKValue()");
					
					$object->loadRow();
					$object->addColumns($this->getUpdate()->compileModified());
					
					if($this->getTestingOnly()){
						\A2Debug::Info($this->getUpdate()->compileModified(), "TESTING:::$ACCION - row getUpdate()->compileModified()");
						\A2Debug::Info($object, "TESTING:::$ACCION - row object");
					}
					else
						$object->saveRow();		
				}
				return(true);
			}
			catch(Exception $e){
				\A2Debug::Error("Failed!!! ".$e);	
				return(false);
			}
		}
		else if($this->getAction()==\A2Dborm::DELETE){
			try{
				$query="SELECT ".$this->getTableFrom()->getPK()." FROM ".$this->getTableFrom()->getClassName()." ".implode(" ",$this->getJoins())." WHERE ".implode(" AND ",$this->getWhere()->getAll())." ".$this->getGroupByValue()." ".$this->getOrderByValue();
				
				if($this->getTestingOnly())
					\A2Debug::Info($query, "TESTING:::$ACCION - Pre-select");
				
				\A2Sql::getInstance()->query($query);
				$selection=\A2Sql::getInstance()->getResult()->returnAssocArray();
				
				if($this->getTestingOnly())
					\A2Debug::Info($selection, "TESTING:::$ACCION - Pre-select result");
				
				foreach($selection as $selectionRow){
					$class='\A2Dborm\\'.$this->getTableFrom()->getClassName();
					$object=new $class;
					$object->setPKValue($selectionRow["id"]);
					
					if($this->getTestingOnly()){
						\A2Debug::Info($object->getPKValue(), "TESTING:::$ACCION - row getPKValue()");
						\A2Debug::Info($object, "TESTING:::$ACCION - row object");
					}
					else
						$object->deleteRow();
				}
				return(true);
			}
			catch(Exception $e){
				\A2Debug::Error("Failed!!! ".$e);	
				return(false);
			}
		}
		else if($this->getAction()==\A2Dborm::SELECT){
			try{
				\A2Sql::getInstance()->query($this->getCompiledQuery());
				$this->setResult(new \A2Dborm\A2Result());
				$this->getResult()->setSourceTable( $this->getTableFrom() );
				$this->getResult()->setRecordSet(\A2Sql::getInstance()->getResult());
				
				if($this->getTestingOnly())
					\A2Debug::Info($this->getResult()->getRecordSet(), "TESTING:::$ACCION - getResult()->getRecordSet()");
				
				$this->getResult()->setFields($this->getFields());
				
				if($this->getTestingOnly()){
					\A2Debug::Info($this->getFields(), "TESTING:::$ACCION - getResult()->getFields()");	
					\A2Debug::Info($this->getResult(), "TESTING:::$ACCION - getResult() object complete");	
					
					$this->getResult()->setTestingOnly(true);
				}
				
				return(true);
			}
			catch(Exception $e){
				\A2Debug::Error("Failed!!! ".$e);	
				return(false);
			}
		}
		else{
			\A2Debug::Error("Action '".$this->getAction()."' not allowed!");
			return(false);
		}
	}
	public function compileQuery(){
		// Buscamos en las tablas una tabla con SELECT o UPDATE o DELETE
		$tableAction="";
		$fields=array();
		foreach($this->getTables() as $table){
			$action=call_user_func(array($this->{$table},"getAction"));
			if(	$action==\A2Dborm::SELECT || $action==\A2Dborm::UPDATE || $action==\A2Dborm::DELETE ){
				$this->setTableFrom($this->{$table});
				$this->setAction($action);
						
				if($action!=\A2Dborm::DELETE)
					$fields=array_merge($fields,call_user_func(array($this->getTableFrom(),"compileColumns")));
			}
			else
				$fields=array_merge($fields,call_user_func(array($this->{$table},"compileJoins")));
			
		}
		$this->setFields($fields);
		

		// Procesamos el UPDATE
		$update=array();
		if($this->getAction()==\A2Dborm::UPDATE){
			if(is_object($this->getUpdate())){
				$updateCompiled=$this->getUpdate()->compileUpdate();
				if(sizeof($updateCompiled)>0)
					$update=$updateCompiled;	
			}
			else
				\A2Debug::Info("No table SETS specified by getUpdate()");
		}
		$this->setUpdateFields($fields);
			
		
		// Procesamos las relacions de las tablas (JOINS) y adjuntamos campos adicionales
		$joins=array();
		foreach($this->getTables() as $table){
			$action=call_user_func(array($this->{$table},"getAction"));
			if(	$action==\A2Dborm::JOIN || $action==\A2Dborm::LEFT_JOIN || $action==\A2Dborm::RIGHT_JOIN || $action==\A2Dborm::INNER_JOIN || $action==\A2Dborm::OUTER_JOIN ){
				$joins[]=call_user_func(array($this->{$table},"getJoin"));	
			}
		}
		$this->setJoins($joins);
				
		// Procesamos el WHERE
		$where=array();
		foreach($this->getTables() as $table){
			$action=call_user_func(array($this->{$table},"getAction"));
			$whereCompiled=call_user_func(array($this->{$table},"compileWhere"));
			if(sizeof($whereCompiled)>0)
				$where[]=implode(" AND ",$whereCompiled);	
		}
		$this->getWhere()->add($where);
		
		$query=NULL;
		if($this->getAction()==\A2Dborm::SELECT){
			$query="SELECT ".implode(", ",$this->getFields())." FROM ".$this->getTableFrom()->getClassName()." ".implode(" ",$this->getJoins()).$this->combineIfNotNull(" WHERE ", implode(" AND ",$this->getWhere()->getAll()))." ".$this->getGroupByValue()." ".$this->getOrderByValue();
		}
		else if($this->getAction()==\A2Dborm::UPDATE){
			$query="UPDATE ".$this->getTableFrom()->getClassName()." SET ".implode(", ",$this->getUpdateFields())." WHERE ".implode(", ",$this->getWhere()->getAll());
		}
		else if($this->getAction()==\A2Dborm::DELETE){
			$query="DELETE FROM ".$this->getTableFrom()->getClassName()." WHERE ".implode(", ",$this->getWhere()->getAll());
		}
		else{
			\A2Debug::Error("Action '".$this->getAction()."' not valid!");
		}
		
		$this->setCompiledQuery($query);
	}
	
	
	private function combineIfNotNull($string, $string2){
		if($string2!="")
			return($string.$string2);
		else
			return("");
	}

	private function setTableFrom($value){
		$this->TableFrom=$value;
	}	
	private function getTableFrom(){
		return($this->TableFrom);
	}
	private function setAction($value){
		$this->Action=$value;
	}	
	private function getAction(){
		return($this->Action);
	}

	public function setGroupBy($value){
		$this->GroupBy=$value;
	}	
	public function getGroupBy(){
		return($this->GroupBy);
	}
	public function getGroupByValue(){
		$values=$this->getGroupBy()->getAll();		
		if(sizeof($values)>0){
			return("GROUP BY ".implode(", ",$values));
		}
		else
			return("");
	}

	public function setOrderBy($value){
		$this->OrderBy=$value;
	}	
	public function getOrderBy(){
		return($this->OrderBy);
	}
	public function getOrderByValue(){
		$values=$this->getOrderBy()->getAll();		
		if(sizeof($values)>0){
			$items=array();
			foreach($values as $key=>$value)
				$items[]=$key." ".$value;
			
			return("ORDER BY ".implode(", ",$items));
		}
		else
			return("");
	}
	public function setCompiledQuery($value){
		$this->CompiledQuery=$value;
	}	
	public function getCompiledQuery(){
		return($this->CompiledQuery);
	}
	public function setUpdate($value){
		$this->Update=$value;
	}	
	public function getUpdate(){
		return($this->Update);
	}
	public function setWhere($value){
		$this->Where=$value;
	}	
	public function getWhere(){
		return($this->Where);
	}
	public function setJoins($value){
		$this->Joins=$value;
	}	
	public function getJoins(){
		return($this->Joins);
	}
	public function setFields($value){
		$this->Fields=$value;
	}	
	public function getFields(){
		return($this->Fields);
	}
	public function setUpdateFields($value){
		$this->UpdateFields=$value;
	}	
	public function getUpdateFields(){
		return($this->UpdateFields);
	}
	public function setResult($value){
		$this->Result=$value;
	}	
	public function getResult(){
		return($this->Result);
	}
	private function setRecordSet($value){
		$this->RecordSet=$value;
	}
	public function getRecordSet(){
		if($this->getResult())
			return($this->getResult()->getRecordSet());
		else
			return(NULL);
	}
	public function setTestingOnly($value){
		$this->TestingOnly=$value;
	}	
	public function getTestingOnly(){
		return($this->TestingOnly);
	}


	public function getClassName(){	   
		$class = explode('\\', get_class($this));
		return $class[count($class) - 1];	
	}
	      
}

?>