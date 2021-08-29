<?php

namespace A2Dborm;

class A2Result {

	private $SourceTable=NULL;
	private $RecordSet=NULL; // es del tipo \A2SqlResult()
	private $Fields=NULL;

	private $CollectionArray=NULL;
	private $ObjectArray=NULL;
	private $SourceTableObjectArray=NULL;

	private $TestingOnly=false;


	public function __construct(){
	}
	
	public function generateCollectionArray(){
		$selection=$this->getResultArray();
		$objectArray=array();
		foreach($selection as $selectionRow){
			$row=\A2Collection();
			$row->add($selectionRow);
			$objectArray[]=$row;
		}
		
		$this->setObjectArray( $objectArray );
	}
	public function generateObjectArray(){
		$selection=$this->getResultArray();
		$objectArray=array();
		foreach($selection as $selectionRow){
			
			///////////////////////
			///////////////////////
			///////////////////////
			///////////////////////
			// POR HACER
			// ====================
			// Consiste en un objeto que contiene todos los campos de la select con sus respectivos getters y setters. Per hasta allí
			// Como una SELECT puede contener distintas tablas, este objeto puede ser completamente distinto al objeto del mapeo mysql
			// de la tabla SOURCE.
			// Además, este objeto no extiende la clase A2Table, ya que no tiene que tener relación con los mapeos de mysql. No contenmpla
			// entonces ningún método de loadRow ni nada del estilo.
			//
			// Ventajas: Básicamente, la aplicación de funciones automáticas en función del tipo de campo (varchar, longtext, datetime...)
			// Por ejemplo, podría convertirse automáticamente las fechas al invocar simplemente getFecha
			// ALFGO QUE TAMBIÉN DEBERÍA IMPLEMENTARSE EN LOS GETTERS Y SETTERS DE A2TABLE.
			//
			// Todas las ventajas de un objeto.
			//
			///////////////////////
			///////////////////////
			///////////////////////
			///////////////////////			
			
			
			$row=\A2Dborm\A2ResultRow();
			$row->add($selectionRow);
			
			$objectArray[]=$row;
		}
		
		$this->setObjectArray( $objectArray );
	}
	public function generateSourceTableObjectArray(){
		$selection=$this->getResultArray();
	
		if($this->getTestingOnly())
			\A2Debug::Info($selection,"TESTING:::SELECT - getResultArray()");
		
		$objectArray=array();
		foreach($selection as $selectionRow){
			if($this->getTestingOnly())
				\A2Debug::Info($selectionRow, "TESTING:::SELECT - selectionRow");
			
			if($this->getTestingOnly())
				\A2Debug::Info($selectionRow, "TESTING:::SELECT - creating object '\A2Dborm\'".$this->getSourceTable()->getClassName()."'");
			
			$class='\A2Dborm\\'.$this->getSourceTable()->getClassName();
			$object=new $class;	
			$object->setPKValue($selectionRow[$this->getSourceTable()->getPK()]);
			
			if($this->getTestingOnly())
				\A2Debug::Info($object->getPKValue(), "TESTING:::SELECT - object->getPKValue()");			
			
			$object->loadRow();
			
			if($this->getTestingOnly())
				\A2Debug::Info($object, "TESTING:::SELECT - object->getPKValue()");			
			
			$objectArray[]=$object;
		}
	
		if($this->getTestingOnly())
			\A2Debug::Info($objectArray, "TESTING:::SELECT - generateSourceTableObjectArray() result [objectArray]");			
		
		
		$this->setSourceTableObjectArray( $objectArray );
	}
	
	public function setSourceTable($value){
		$this->SourceTable=$value;
	}
	public function getSourceTable(){
		return($this->SourceTable);
	}		
	public function setRecordSet($value){
		$this->RecordSet=$value;
	}
	public function getRecordSet(){
		return($this->RecordSet);
	}		
	public function setFields($value){
		$this->Fields=$value;
	}
	public function getFields(){
		return($this->Fields);
	}		
	public function getResultArray(){
		return($this->RecordSet->returnAssocArray());
	}
	public function setCollectionArray($value){
		$this->CollectionArray=$value;
	}
	public function getCollectionArray(){
		if($this->CollectionArray==NULL)
			$this->generateCollectionArray();
		
		return($this->CollectionArray);
	}			
	public function setObjectArray($value){
		$this->ObjectArray=$value;
	}
	public function getObjectArray(){
		if($this->ObjectArray==NULL)
			$this->generateObjectArray();
		
		return($this->ObjectArray);
	}
	public function setSourceTableObjectArray($value){
		$this->SourceTableObjectArray=$value;
	}
	public function getSourceTableObjectArray(){
		if($this->SourceTableObjectArray==NULL)
			$this->generateSourceTableObjectArray();
		
		return($this->SourceTableObjectArray);
	}
	public function setTestingOnly($value){
		$this->TestingOnly=$value;
	}	
	public function getTestingOnly(){
		return($this->TestingOnly);
	}
	
		
}

?>