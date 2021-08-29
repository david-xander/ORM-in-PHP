<?php

class A2SqlResult {
		
	public function A2SqlResult2( $recordSet=NULL ){
		if($recordSet!=NULL)
			$this->setRecordSet($recordSet);
	}

	public function setRecordSet($value){
		$this->recordSet = $value;
	}
	
	public function getRecordSet(){
        return($this->recordSet);
	}

    public function returnAssocArray(){
        $_data = array();

        for($i=0;$i<mysqli_num_rows($this->getRecordSet());$i++){
            $_row=mysqli_fetch_array($this->getRecordSet());
            for($j=0;$j<mysqli_num_fields($this->getRecordSet());$j++){
               $_data[$i][mysqli_fetch_field_direct($this->getRecordSet(), $j)->name] = stripslashes($_row[$j]);
            }
        }
        return( $_data );
    }
        
        
    public function return2ColArray(){
        $_data = array();

        for($i=0;$i<mysqli_num_rows($this->getRecordSet());$i++){
            $_row=mysqli_fetch_array($this->getRecordSet());

            $_data[$_row[0]] = stripslashes($_row[1]);
        }
        return( $_data );
    }
        
        
    public function returnMultiArray(){
        $_data = array();

        for($i=0;$i<mysqli_num_rows($this->getRecordSet());$i++){
            $_row=mysqli_fetch_array($this->getRecordSet());
            for($j=0;$j<mysqli_num_fields($this->getRecordSet());$j++){
                $_data[$i][$j] = stripslashes($_row[$j]);
            }
        }
        return( $_data );	
    }
        
        
    public function returnUniArray(){
        $_data = array();

        for($i=0;$i<mysqli_num_rows($this->getRecordSet());$i++){
            $_row=mysqli_fetch_array($this->getRecordSet());
            $_data[$i] = stripslashes($_row[0]);
        }
        return( $_data );
    }
        
        
    public function returnPlain(){
        $_row = mysqli_fetch_array($this->getRecordSet());
        return( stripslashes($_row[0]) );
    }
        
        
    public function returnSingleResultAssocArray(){
        $_data=array();

        $_row = mysqli_fetch_array($this->getRecordSet());
        
        for($j=0; $j<mysqli_num_fields($this->getRecordSet()); $j++){
            $_data[mysqli_fetch_field_direct($this->getRecordSet(), $j)->name] = stripslashes($_row[$j]);
        }
        return( $_data );
    }
    
    
    public function returnSingleResultArray(){
        $_data=array();

        $_row = mysqli_fetch_array($this->getRecordSet());
        for($i=0;$i<mysqli_num_fields($this->getRecordSet());$i++){
            $_data[$i] = stripslashes($_row[$i]);
        }
        return( $_data );
    }    
}

?>