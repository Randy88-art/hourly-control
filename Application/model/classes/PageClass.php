<?php
	namespace Application\model\classes;		

	class PageClass {
		public function __construct(
			public string $title = "Aquí va el title",
			public string $h1 = "Aquí va el H1",
			public string $meta_name_description = "Aquí va una descripción del sitio",
			public string $meta_name_keywords = "Aquí van una serie de palabras clave para los buscadores",
			public array $nav_links = [
				"Home"				=>	"/",				
				"Registration"		=> 	"/",				
				"Login "			=> 	"/",],
		)
		{			
			$links = new NavLinks();

			/** Configure menus by ROLE */			
			if (isset($_SESSION['role']) && $_SESSION['role'] === 'ROLE_ADMIN')	$this->nav_links = $links->admin();			
			if (isset($_SESSION['role']) && $_SESSION['role'] === 'ROLE_USER') $this->nav_links = $links->user();

			if (isset($_SESSION['id_user'])) {
				array_pop($this->nav_links);
				$this->nav_links["Logout"] = "/login.php?action=logout"; 
			}
		}

		public function do_html_header($title, $h1, $meta_name_description, $meta_name_keywords) {
?>
		<!DOCTYPE html>
		<html lang="es">
			<head>
				<meta charset='UTF-8' />
				<meta name="title" content="Web site" /> 
				<meta name="description" content="<?php echo $meta_name_description; ?>" />
				<meta name="keywords" content="<?php echo $meta_name_keywords; ?>" />
				<meta name="robots" content="All" />  
				<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
				<title><?php echo $title; ?></title>
				<!-- <link rel="shorcut icon" href="imagen para el favicon"> -->
				<link rel="icon" type="image/gif" href="/images/favicon.ico">								
				<link rel="stylesheet" type="text/css" href="/css/reset.css">
				<link rel="stylesheet" type="text/css" href="/css/bootstrap.min.css">
				<link rel="stylesheet" type="text/css" href="/css/estilo.css">
				<script src="/js/bootstrap.bundle.min.js"></script>
				<script src="/js/eventos.js"></script>
				<script src="/js/ajax.js"></script>
			</head>
			<body>
				<main>
					<header class="d-flex justify-content-center align-items-center">
						<h1><?php echo $this->h1; ?></h1><hr />
					</header>
<?php			
		}

		public function do_html_nav(array $links=NULL, string $active_name=NULL) {
?>
					<nav class="navbar navbar-expand-lg navbar-light bg-light sticky-top">
						<div class="container-fluid">
							<div class="col-5 col-sm-1 col-md-2 col-lg-2 col-xl-1">
								<a class="navbar-brand" href="/"><img src="/images/main_logo.png" class="img-fluid float-start" alt="imagen_logo"></a>								
							</div>							
							<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#my_nav" aria-controls="my_nav" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon"></span>
							</button>
							<div class="collapse navbar-collapse" id="my_nav">
								<ul class="nav navbar-nav justify-content-center w-100">
<?php
							foreach($links as $name => $url) {								
?>
									<li class="nav-item d-lg-inline-block"><a class="nav-link <?php if(isset($active_name) && strtolower($name) === strtolower($active_name)) echo "active"; ?>" href="<?php echo $url; ?>"><?php echo $name; ?></a></li>
<?php
							}
?>										
								</ul>
							</div>																					
						</div>
					</nav>
			<noscript><h4>Tienes javaScript desactivado</h4></noscript>
<?php
		}

		public function do_html_footer() {
?>
				</main>
				<footer class="d-flex justify-content-center align-items-center">
					<p>Copyright &copy; reserved <?php echo date("Y"); ?></p>
				</footer>
			</body>
		</html>
<?php		
		}
	}
?>
