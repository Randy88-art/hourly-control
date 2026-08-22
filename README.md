### Hourly Control Project

#### Application Functionality
This application is designed to manage and control the hours worked by users. It allows employees to register their start and end times of the workday (in/out), allowing the system to calculate the total time worked on a specific day or period. Administrators can monitor these records and manage project and associated task information.

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

