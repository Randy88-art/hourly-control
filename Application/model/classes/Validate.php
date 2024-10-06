<?php
    declare(strict_types = 1);
    
    namespace model\classes;
    
    use PDOException;
    use PDO;

    /**
     * Validate inputs
     */
    class Validate
    {
    	private $msg;
    	
        /**
         * Method to validate fields from form
         */
        public function test_input(int|string|float|null $data): int|string|float|null
        {
            if(!is_int($data)) {
                $data = htmlspecialchars($data);
                $data = trim($data);
                $data = stripslashes($data);
            }
    
            return $data;
        }
        
        /**
         * Method to validate e-mail fields from form
         */
        public function validate_email(string $email): bool {
			if(preg_match('/^[a-zA-Z0-9_\.\-]+@[a-zA-Z0-9\-]+\.[a-zA-Z0-9\-\.]+$/', $email)) {
				return true;
			}
			else {
				return false;
			}
		}
		
		/**
         * Método para validar entradas de formulario
         */
        public function validate_form(array $fields): bool
        {                 
            $result = true;
            
            foreach ($fields as $key => $value) {
                if (empty($value) || !isset($value)) {                                        
                    $this->msg .= "<p class='alert alert-danger text-center'>'$key' es un dato requerido</p>";
                    $result = false;					
                }
            }
                      
            return $result;
        }
        
        /**
         * Show validation messages
         */
        public function get_msg(): string 
        {
            return $this->msg;
        }
    }
?>