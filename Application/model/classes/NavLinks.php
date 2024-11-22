<?php
    namespace Application\model\classes;

    trait NavLinks
    {
        public function __construct(private array $menus = [])
        {
            
        }

        public function showAdminLinks(): array
        {
            $this->menus = [
                "Home"				=>	"/",				
				"Registration"		=> 	"/register",
				"Administration"	=>	"/admin/admin/index",				
				"Login"			    => 	"/login",
            ];

            return $this->menus;
        }

        
        public function showUserLinks(): array
        {
            $this->menus = [
                "Home"	=>	"/",												
				"Login"	=> 	"/login",
            ];

            return $this->menus;
        }
    }    
?>
