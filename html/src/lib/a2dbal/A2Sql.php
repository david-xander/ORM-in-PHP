<?php

require_once(A2___P2A2FRAMEWORK."/lib/a2dbal/A2SqlResult.class.php");
require_once(A2___P2A2FRAMEWORK."/lib/a2dbal/A2SqlPager.class.php");

class A2Sql {
    static protected $_instance;
    protected $socket = NULL;
    private $result = NULL;
    private $user = NULL;
    private $password = NULL;
    private $dataBase = NULL;
    private $connectionString = NULL;
	
	
    public function __construct($connectionString=NULL){		
        $this->setResult( new A2SqlResult() );
        if($connectionString!=NULL)
            $this->setConnectionString($connectionString);

        $this->connect();
    }
    
    
    static public function getInstance() {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    
    final protected function __clone() {
        
    }


    public function connect() {	
        if($this->getSocket()!=NULL)
            $this->disconnect();

        $datosBBDD=A2Config::get("databases");

        // Si no se especifican los datos, se extrae de la connectionString por defecto ("main").
        if($this->getConnectionString()==NULL)
            $this->setConnectionString("main");

        $this->setUser($datosBBDD[$this->getConnectionString()]["user"]);
        $this->setPassword($datosBBDD[$this->getConnectionString()]["password"]);		
        $this->setDataBase($datosBBDD[$this->getConnectionString()]["name"]);		

        if(A2Config::get("a2framework_debug")){
            $this->setSocket(@mysqli_connect($datosBBDD[$this->getConnectionString()]["host"],$datosBBDD[$this->getConnectionString()]["user"],$datosBBDD[$this->getConnectionString()]["password"], $datosBBDD[$this->getConnectionString()]["name"]));
            \A2Debug::Info("(".$this->getSocket()->thread_id.") Connection success using ConnectionString '".$this->getConnectionString()."'!");
            \A2Debug::Info("(".$this->getSocket()->thread_id.") DB '".$datosBBDD[$this->getConnectionString()]["name"]."' selected!");
        }else{
            $this->setSocket(@mysqli_connect($datosBBDD[$this->getConnectionString()]["host"],$datosBBDD[$this->getConnectionString()]["user"],$datosBBDD[$this->getConnectionString()]["password"], $datosBBDD[$this->getConnectionString()]["name"]));
        }
    }
    
    
    public function disconnect() {
        mysqli_close($this->getSocket()); 
        \A2Debug::Info("Socket Disconnected!"); 
    }
    
    
    public function close() {
        $this->disconnect();	
    }

    
    public function setSocket($value){
        if(!$value)
            die("A2Sql.setSocket() - ERROR DE CONEXIÓN");
        $this->socket = $value;
    }	
    
    
    public function setResult($value){
        $this->result = $value;
    }	
    
    
    public function setUser($value){
        $this->user = $value;
    }
    
    
    public function setPassword($value){
        $this->password = $value;
    }
    
    
    public function setDataBase($value){
        $this->dataBase = $value;
    }	
    
    
    public function setConnectionString($value){
        $this->connectionString = $value;
    }

    
    public function getSocket(){
        return( $this->socket );
    }
    
    
    public function getResult(){
        return( $this->result );
    }
    
    
    public function getUser(){
        return( $this->user );
    }
    
    
    public function getPassword(){
        return( $this->password );
    }
    
    
    public function getDataBase(){
        return( $this->dataBase );
    }
    
    
    public function getConnectionString(){
        return( $this->connectionString );
    }
	
    
    public function query($query) {
        //$query = @mysqli_escape_string($this->getSocket(), $query);
        if(A2Config::get("a2framework_debug")){
            $this->getResult()->setRecordSet(@mysqli_query($this->getSocket(), $query));			
            if(!$this->getResult()->getRecordSet()){
                echo("<b>A2sistemas - A2FrameWork</b> <i>MODO <b>DEBUG</b> ACTIVADO</i><br>(error SQL)<br><br><pre>$query</pre><br><br><b>Error:</b><hr> ".mysqli_error($this->getSocket())."= ".mysqli_error($this->getSocket())."<br><br><strong>Pila:</strong><hr><pre>");
                debug_print_backtrace();
                die("</pre>");

            }
        }else{
            $this->getResult()->setRecordSet(@mysqli_query($this->getSocket(), $query));
            if(!$this->getResult()->getRecordSet())
                die("");
        }
    }	
	
	public function realEscapeString($texto){
		return(mysqli_real_escape_string($this->getInstance()->getSocket(), $texto));	
	}
}

?>