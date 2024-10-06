### Online Shop Project
1.- Clone the repository in a new directory of your choice ("directoryName").
```
git clone https://github.com/u83mm/e-commerce.git "directoryName"
```

2.- Navigate to the new directory.
```
cd directoryName
```
3.- Create "log" directory inside "Application" directory and inside "log" directory create "apache", "db" and "php" directories.
```
cd Application
mkdir log log/apache log/dg log/php
```
4.- Build the project and stands up the containers
```
docker compose build
docker compose up -d
```
5.- Enter in php container and run "composer install"
```
docker exec -it php bash
composer install
```
6.- Access to phpMyAdmin.
```
http://localhost:8080/
user: admin
passwd: admin
```
7.- Select "my_database" and go to "import" menu and search my_database.sql file in your "Application/MariaDB" directory.

8.- Go to your localhost in the browser and you can do login.
```
http://localhost/
user: admin@admin.com
passwd: admin
```

