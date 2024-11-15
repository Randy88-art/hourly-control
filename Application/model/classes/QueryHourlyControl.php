<?php

declare(strict_types = 1);

namespace Application\model\classes;

use Application\model\classes\Query;
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

    public function getTotalTimeWorkedToday(string $date, int $id_user): string|null
    {
        $query = "SELECT SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, date_in, date_out))) AS total_time_worked
                    FROM hourly_control
                    WHERE id_user = :id_user
                    AND DATE(date_in) = :date";

        try {
            $stm = $this->dbcon->pdo->prepare($query);
            $stm->bindValue(":id_user", $id_user);
            $stm->bindValue(":date", $date);
            $stm->execute();
            $rows = $stm->fetch(PDO::FETCH_ASSOC);            
            
            return $rows['total_time_worked'];

        } catch (\Throwable $th) {
            throw new \Exception("{$th->getMessage()}", 1);
        }
    }
    
    public function getTotalTimeWorkedAtDayByUser(string $date, int $id_user): array|bool
    {
        $query = "SELECT TIME(date_in) AS date_in, 
                    TIME(date_out) AS date_out,                     
                    total_time_worked
                    FROM hourly_control 
                    WHERE id_user = :id_user 
                    AND DATE(date_in) = :date
                    ORDER BY date_in ASC";

        try {
            $stm = $this->dbcon->pdo->prepare($query);
            $stm->bindValue(":id_user", $id_user);
            $stm->bindValue(":date", $date);
            $stm->execute();
            $rows = $stm->fetchAll(PDO::FETCH_ASSOC);

            return $rows ?? false;

        } catch (\Throwable $th) {
            throw new \Exception("{$th->getMessage()}", 1);
        }
    }

    /**
     * Tests if the last row of the hourly_control table for a given user has a valid date_out field.
     * 
     * If the last row has a null date_out, then the user is currently working and the function returns false.
     * 
     * If the last row has a non-null date_out, then the user is not currently working and the function returns true.
     * 
     * @param int $id_user The id of the user to test.
     * 
     * @return bool Whether the user is currently working.
     */
    public function isValidRow(int $id_user): bool
    {
        $query = "SELECT date_out 
                    FROM hourly_control
                    WHERE id_user = :id_user
                    AND date_in = (SELECT MAX(date_in))
                    AND date_out IS NULL";
            
        $stm = $this->dbcon->pdo->prepare($query);
        $stm->bindValue(":id_user", $id_user);
        $stm->execute();

        $rows = $stm->fetch(PDO::FETCH_ASSOC);

        return $rows ? false : true;
    }
}