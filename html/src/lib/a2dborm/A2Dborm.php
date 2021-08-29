<?php

use A2Dborm\Email;

require_once(A2___P2A2FRAMEWORK."/lib/a2dborm/A2Table.class.php");
require_once(A2___P2A2FRAMEWORK."/lib/a2dborm/A2Query.class.php");
require_once(A2___P2A2FRAMEWORK."/lib/a2dborm/A2Result.class.php");
require_once(A2___P2A2FRAMEWORK."/lib/a2dborm/A2ResultRow.class.php");

class A2Dborm {

	const SELECT="SELECT";
	const INSERT="INSERT";
	const UPDATE="UPDATE";
	const DELETE="DELETE";
	const LEFT_JOIN="LEFT JOIN";
	const RIGHT_JOIN="RIGHT JOIN";
	const INNER_JOIN="INNER JOIN";
	const OUTER_JOIN="OUTER JOIN";
	const JOIN="JOIN";
	const ORDER_DESC="DESC";
	const ORDER_ASC="ASC";

	const EQ="=";
	const NEQ="<>";
	const GT=">";
	const LT="<";
	const GTE=">=";
	const LTE="<=";

	const LIKE="LIKE";
	const NOTLIKE="NOT LIKE";


	private $TablesMapsPath;
	private $TablesObjectsPath;

	public function __construct(){
		$this->setTablesMapsPath(\A2Config::get("a2framework_p2fw").\A2Config::get("a2framework_a2dorm_folder")."tables/");		
		$this->setTablesObjectsPath(\A2Config::get("a2framework_p2fw").\A2Config::get("a2framework_a2dorm_folder")."objects/");	
	}
	
	public function loadClasses(){
		if(is_dir($this->getTablesMapsPath())){
			$classesFiles=scandir($this->getTablesMapsPath());
			if($classesFiles){
				//eliminamos los primeros 2 elementos: (. y ..)
				array_shift($classesFiles);
				array_shift($classesFiles);
				foreach($classesFiles as $classFile){
					if($classFile!="." && $classFile!=".." && preg_match("/.class.php$/",$classFile)){
						require_once($this->getTablesMapsPath().DIRECTORY_SEPARATOR.$classFile);
						$class=explode('.class.php',$classFile);
						\A2Debug::Info($class[0]." loaded!");
					}
				}
			}
		}
		else
			\A2Debug::Error($this->getTablesMapsPath()." Table's maps path not found!");
			
		if(is_dir($this->getTablesObjectsPath())){
			$classesFiles=scandir($this->getTablesObjectsPath());
			if($classesFiles){
				//eliminamos los primeros 2 elementos: (. y ..)
				array_shift($classesFiles);
				array_shift($classesFiles);
				foreach($classesFiles as $classFile){
					if($classFile!="." && $classFile!=".." && preg_match("/.class.php$/",$classFile)){
						require_once($this->getTablesObjectsPath().DIRECTORY_SEPARATOR.$classFile);
						$class=explode('.class.php',$classFile);
						\A2Debug::Info($class[0]." loaded!");
					}
				}
			}
		}
		else
			\A2Debug::Error($this->getTablesObjectsPath()." Table's objects path not found!");			
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
	  
	public function getClassName(){
		return(get_class($this));
	}
	      
}

?>