<?php
    namespace model\classes;

    trait NavLinks
    {
        public function __construct(private array $menus = [])
        {
            
        }

        public function admin(): array
        {
            $this->menus = [
                "Home"				=>	"/",				
				"Registration"		=> 	"/register",
				"Administration"	=>	"/admin/admin",				
				"Login"			=> 	"/login",
            ];

            return $this->menus;
        }

        
        public function user(): array
        {
            $this->menus = [
                "Home"				=>	"/",				
				"Registration"		=> 	"/register",				
				"Login"			=> 	"/login",
            ];

            return $this->menus;
        }
    }    
?>
