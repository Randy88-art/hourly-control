<?php

declare(strict_types = 1);

namespace model\classes;

use model\classes\Query;
use PDO;

final class QueryHourlyControl extends Query
{
    public function testWorkState(): array|bool
    {
        $query = "SELECT date_in, date_out FROM hourly_control 
                WHERE id_user = :id_user 
                AND date_in = (SELECT MAX(date_in) 
                                FROM hourly_control 
                                WHERE id_user = $_SESSION[id_user])";

        try {                        
            $stm = $this->dbcon->pdo->prepare($query);
            $stm->bindValue(":id_user", $_SESSION['id_user']);            
            $stm->execute();

            $rows = $stm->fetch(PDO::FETCH_ASSOC);

            return $rows;

        } catch (\Throwable $th) {
            throw new \Exception("{$th->getMessage()}", 1);
        }
    }

    public function getTotalTimeWorkedAtDay(string $date): string
    {
        $query = "SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, date_in, date_out))) AS total_time_worked
                    FROM hourly_control
                    WHERE id_user = :id_user
                    AND DATE(date_in) = :date";

        try {
            $stm = $this->dbcon->pdo->prepare($query);
            $stm->bindValue(":id_user", $_SESSION['id_user']);
            $stm->bindValue(":date", $date);
            $stm->execute();
            $rows = $stm->fetch(PDO::FETCH_ASSOC);            
            
            return $rows['total_time_worked'];

        } catch (\Throwable $th) {
            throw new \Exception("{$th->getMessage()}", 1);
        }
    } 
}