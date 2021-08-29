<?php

class databaseActions extends _a2builder
{
	
	private $TablesMapsPath;
	private $TablesObjectsPath;
	
	public function preExecute(){		
		$this->LoadGLobalClass("a2sistemas", "A2Form");
		$this->LoadGLobalClass("a2sistemas", "A2FormControl");
		$this->LoadGLobalClass("a2sistemas", "A2Combo");
	}
	public function globalStart(){
	}
	public function globalExecute(){
		$this->setTablesMapsPath(A2Config::get("a2framework_p2fw").A2Config::get("a2framework_a2dorm_folder")."tables/");		
		$this->setTablesObjectsPath(A2Config::get("a2framework_p2fw").A2Config::get("a2framework_a2dorm_folder")."objects/");
	}
	
	public function executeMapTable(){		
		$this->A2Form=new A2Form();
		$this->A2FormUrl=new A2Form();
		
		$dataBases=A2Config::get("databases");
		$connectionStrings=array_keys($dataBases);
		$dataBasesNames=array();
		foreach($connectionStrings as $connectionString)
			$dataBasesNames[]=$dataBases[$connectionString]["name"];
		$this->A2Form->add(array("dataBase"=>""));
		$this->A2Form->setControl("dataBase", new A2Combo('dataBase', $dataBasesNames) );
		$this->A2Form->getControl("dataBase")->setItemNeutro("");
		$this->A2Form->getControl("dataBase")->setValor(99);
		$this->A2Form->getControl("dataBase")->setEtiqueta("Select a DataBase");
				
		$this->Template->assign("A2Form",$this->A2Form);
	}
	public function executeMapDataBase(){
		$this->A2Form=new A2Form();
		$this->A2FormUrl=new A2Form();
		
		$dataBases=A2Config::get("databases");
		$connectionStrings=array_keys($dataBases);
		$dataBasesNames=array();
		foreach($connectionStrings as $connectionString)
			$dataBasesNames[]=$dataBases[$connectionString]["name"];
		$this->A2Form->add(array("dataBase"=>""));
		$this->A2Form->setControl("dataBase", new A2Combo('dataBase', $dataBasesNames) );
		$this->A2Form->getControl("dataBase")->setItemNeutro("");
		$this->A2Form->getControl("dataBase")->setValor(99);
		$this->A2Form->getControl("dataBase")->setEtiqueta("Select a DataBase");
				
		$this->Template->assign("A2Form",$this->A2Form);
	}
	public function executeCreateTable(){
		
	}
	
	public function executeAjaxShowTables(){
		$connectionStringId=$this->Request->get("dataBase");
	
		$connectionStringsValues=array_keys(A2Config::get("databases"));
		$A2Sql=new A2Sql();
		$A2Sql->setConnectionString($connectionStringsValues[$connectionStringId]);
		$A2Sql->connect();
		
		$SQL="SHOW TABLES";
		$A2Sql->query($SQL);
		$tablesCol=$A2Sql->getResult()->returnUniArray();
		
		$A2Sql->disconnect();
		
		$tables=array();
		foreach($tablesCol as $col)
			$tables[$col]=$col;
		
		$select = new A2Combo('table',$tables);
		$select->setItemNeutro("");
		$select->setValor(99);
		
		$this->trace($select->render(false));		
	}
	public function executeAjaxMappedObjectExists(){
		$table=$this->Request->get("table");
	
		if(is_readable($this->getTablesMapsPath().$table."_map.class.php"))
			$res=1;
		else
			$res=0;
		
		$this->trace($res);		
	}
	public function executeAjaxMapTable(){
		$connectionStringId=$this->Request->get("dataBase");
		$table=$this->Request->get("table");
		$connectionStringsValues=array_keys(A2Config::get("databases"));
		
		$this->mapTable($connectionStringsValues[$connectionStringId],$table);
		
		$this->trace("1");		
	}
	public function executeAjaxMapDataBase(){
		$connectionStringId=$this->Request->get("dataBase");
		$connectionStringsValues=array_keys(A2Config::get("databases"));
		
		$this->mapDataBase($connectionStringsValues[$connectionStringId]);
		
		$this->trace("1");		
	}

	public function mapTable($connectionString,$table){		
		require_once(A2Config::get("a2framework_p2fw_lib")."/smarty/Smarty.class.php");
				
		$tableTpl = new Smarty();
		$tableTpl->template_dir 	= A2Config::get("a2framework_module_path")."/config/tablesTemplates/";
		$tableTpl->compile_dir  	= A2Config::get("a2framework_p2app").'/templates_compiled';

		$objectTpl = new Smarty();
		$objectTpl->template_dir 	= A2Config::get("a2framework_module_path")."/config/tablesTemplates/";
		$objectTpl->compile_dir  	= A2Config::get("a2framework_p2app").'/templates_compiled';

		$tableTpl->setCaching(false);		
		$objectTpl->setCaching(false);
		
		$A2Sql=new A2Sql();
		$A2Sql->setConnectionString($connectionString);
		$A2Sql->connect();
		
		$A2Sql->query("SHOW COLUMNS FROM ".$table);
		$columns=$A2Sql->getResult()->returnAssocArray();
		
		$A2Sql->disconnect();
		
		$tableTpl->assign("class",$table."_map");
		$tableTpl->assign("primaryKey",$this->getPrimaryKey($columns));
		$tableTpl->assign("listado",$columns);				

		$objectTpl->assign("class",$table);
		$objectTpl->assign("tableClass",$table."_map");				
		
		$this->createClassFile($this->getTablesMapsPath(),$table."_map",$tableTpl->fetch("table_map.class.tpl"));
		
		if(!is_readable($this->getTablesObjectsPath().$table.".class.php"))
			$this->createClassFile($this->getTablesObjectsPath(),$table,$objectTpl->fetch("table.class.tpl"));
	}	
	
	public function mapDataBase($connectionString){
		require_once(A2Config::get("a2framework_p2fw_lib")."/smarty/Smarty.class.php");
				
		$tableTpl = new Smarty();
		$tableTpl->template_dir 	= A2Config::get("a2framework_module_path")."/config/tablesTemplates/";
		$tableTpl->compile_dir  	= A2Config::get("a2framework_p2app").'/templates_compiled';

		$objectTpl = new Smarty();
		$objectTpl->template_dir 	= A2Config::get("a2framework_module_path")."/config/tablesTemplates/";
		$objectTpl->compile_dir  	= A2Config::get("a2framework_p2app").'/templates_compiled';

		$tableTpl->setCaching(false);		
		$objectTpl->setCaching(false);
		
		$A2Sql=new A2Sql();
		$A2Sql->setConnectionString($connectionString);
		$A2Sql->connect();
		
		$A2Sql->query("SHOW TABLES");
		$tables=$A2Sql->getResult()->returnUniArray();
		
		foreach($tables as $table){	
			$A2Sql->query("SHOW COLUMNS FROM ".$table);
			$columns=$A2Sql->getResult()->returnAssocArray();
			
			$tableTpl->assign("class",$table."_map");
			$tableTpl->assign("primaryKey",$this->getPrimaryKey($columns));
			$tableTpl->assign("listado",$columns);				

			$objectTpl->assign("class",$table);
			$objectTpl->assign("tableClass",$table."_map");				
			
			$this->createClassFile($this->getTablesMapsPath(),$table."_map",$tableTpl->fetch("table_map.class.tpl"));

			if(!is_readable($this->getTablesObjectsPath().$table.".class.php"))
				$this->createClassFile($this->getTablesObjectsPath(),$table,$objectTpl->fetch("table.class.tpl"));
		}

		$A2Sql->disconnect();		
	}
	
	public function createClassFile($path,$class,$contents){
		$path2Class = $path.$class.".class.php";
		$file = fopen($path2Class, 'w') or die("can't open file '$path2Class'");
		fwrite($file,$contents);
		fclose($file);		
	}
	
	private function getPrimaryKey($columns){
		$primaryKey="";
		foreach($columns as $column){
			if($column["Key"]=="PRI")
				$primaryKey=$column["Field"];
		}
		return($primaryKey);		
	}
	
	
	private function setTablesMapsPath($value){
		$this->TablesMapsPath=$value;	
	}
	private function getTablesMapsPath(){
		return($this->TablesMapsPath);		
	}
	private function setTablesObjectsPath($value){
		$this->TablesObjectsPath=$value;	
	}
	private function getTablesObjectsPath(){
		return($this->TablesObjectsPath);		
	}
	
	
	
}