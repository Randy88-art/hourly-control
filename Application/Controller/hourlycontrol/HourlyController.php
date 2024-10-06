<?php

declare(strict_types = 1);

namespace Application\Controller\hourlycontrol;

use App\Core\Controller;
use model\classes\Query;

class HourlyController extends Controller
{
    private object $dbcon = DB_CON;
    public function getIn()
    {
        $dateIn = date('Y-m-d H:i:s');
        $query = new Query();
        $query->insertInto("hourly_control", [
            "id_user" => $_SESSION['id_user'],
            "date_in" => $dateIn
        ]); 
        
        header("Location: /");
        die();
    }

    public function getOut()
    {
        //$dateOut = date('Y-m-d H:i:s');
        $dateTime = new \DateTime('now');
        $dateOut = $dateTime->format('Y-m-d H:i:s');       

        $query = "UPDATE hourly_control 
                SET date_out = :date_out 
                WHERE id_user = :id_user 
                AND date_in = (SELECT MAX(date_in) 
                                FROM hourly_control) 
                AND date_out IS NULL";
       
        $stm = $this->dbcon->pdo->prepare($query);       
        $stm->bindValue(":date_out", $dateOut);
        $stm->bindValue(":id_user", $_SESSION['id_user']);
        $stm->execute();        

        header("Location: /");
        die();
    }
}