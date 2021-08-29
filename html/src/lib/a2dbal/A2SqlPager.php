<?php

class A2SqlPager{

	protected $id = "page";
	protected $query = NULL;
	protected $page = NULL;
	protected $request = NULL;
	protected $totalPages = NULL;
	protected $totalResults = NULL;
	protected $resultsPerPage = 50;
	protected $result = NULL;
	protected $BackendListUrl = NULL;
	
    public function __construct( $query=NULL, $request=NULL, $page=1 ){
		if($query!=NULL && $page!=NULL && $request!=NULL)
			$this->CargarDatos($query,$request,$page);
    }
	
	public function CargarDatos( $query=NULL, $request=NULL, $page=1 ){
		if($query!=NULL && $request!=NULL){
			$this->setQuery($query);
			$this->setRequest($request);	
		}
		if( ($this->getQuery()!=NULL && $this->getRequest()!=NULL) ){
			if($page!=NULL && is_numeric($page) && $page!=1)
				$this->setPage($page);
			else if( $this->getRequest()->get($this->getId())!=NULL && is_numeric($this->getRequest()->get($this->getId())) )
				$this->setPage($this->getRequest()->get($this->getId()));
			else if( isset($_SESSION[$this->getId()]) && $_SESSION[$this->getId()]!="1" )
				$this->setPage($_SESSION[$this->getId()]);
			else
				$this->setPage(1);
			
			$min = ($this->getPage() - 1) * $this->getResultsPerPage(); 			
			$limite = " LIMIT ".$min.",".$this->getResultsPerPage();
			
			$SQL="";
			if(strpos($this->getQuery(),"SELECT")===false)
				$SQL = implode("SELECT SQL_CALC_FOUND_ROWS ", explode("SELECT",$this->getQuery())) . $limite;
			else
				$SQL = implode("SELECT SQL_CALC_FOUND_ROWS ", explode("SELECT",$this->getQuery())) . $limite;
			
			\A2Sql::getInstance()->query($SQL);
			$this->setResult( \A2Sql::getInstance()->getResult()->returnAssocArray() );
			
			A2Debug::Info($SQL, $this->getClassName()."CargarDatos() Query");
			A2Debug::Info($this->getResult(), $this->getClassName()."CargarDatos() RESULTADOS PAGINADOS");
			
			$SQL = "SELECT FOUND_ROWS()";
			\A2Sql::getInstance()->query($SQL);
			
			$this->setTotalResults( \A2Sql::getInstance()->getResult()->returnPlain() );
			$this->setTotalPages( ceil($this->getTotalResults()/$this->getResultsPerPage()) );
		}
		else
			A2Debug::Error("No se ha definido un valor 'query' y 'request'");
	}
	
	public function getId(){
		return($this->id);
	}
	public function getQuery(){
		return($this->query);
	}
	public function getPage(){
		return($this->page);
	}
	public function getRequest(){
		return($this->request);
	}
	public function getTotalPages(){
		return($this->totalPages);
	}
	public function getTotalResults(){
		return($this->totalResults);
	}
	public function getResultsPerPage(){
		return($this->resultsPerPage);
	}
	public function getResult(){
		return($this->result);
	}
	
	public function setId($valor){
		$this->id=$valor;
	}
	public function setQuery($valor){
		$this->query=$valor;
	}
	public function setPage($valor){
		$_SESSION[$this->getId()]=$valor;
		$this->page=$valor;
	}
	public function setRequest($valor){
		$this->request=$valor;
	}
	public function setTotalPages($valor){
		$this->totalPages=$valor;
	}
	public function setTotalResults($valor){
		$this->totalResults=$valor;
	}
	public function setResultsPerPage($valor){
		$this->resultsPerPage=$valor;
	}
	public function setResult($valor){
		$this->result=$valor;
	}

	public function getBackendListUrl(){
		return($this->BackendListUrl);
	}
	public function setBackendListUrl($valor){
		$this->BackendListUrl=$valor;
	}



	public function getClassName(){
		return(get_class($this));
	}


}
?>