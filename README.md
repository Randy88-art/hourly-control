### Hourly Control Project
1.- Clone the repository in a new directory of your choice ("directoryName").
```
git clone git@github.com:u83mm/hourly-control.git "directoryName"
```

2.- Navigate to the new directory.
```
cd directoryName
```
3.- Build the project and stands up the containers
```
docker compose build
docker compose up -d
```
4.- Enter in php container and run "composer install"
```
docker exec -it php bash
composer install
```
5.- Access to phpMyAdmin.
```
http://localhost:8080/
user: admin
passwd: admin
```
6.- Select "my_database" and go to "import" menu and search my_database.sql file in your "Application/MariaDB" directory.

7.- Go to your localhost in the browser and you can do login.
```
http://localhost/
user: admin@admin.com
passwd: admin
```

