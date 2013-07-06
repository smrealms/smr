<?php

class HoF {
    private function __construct() {
    }



    public static function getHofRanking(SmrMySqlDatabase &$db, $order){
        $subselect = null;
        switch($order){
            case 1:
                $order = "a.login";
                break;
            case 3:
                $order = "SUM(EXPERIENCE) DESC";
                $subselect = ",(SELECT label FROM ranking WHERE experience <= SUM(p.experience) ORDER BY experience DESC LIMIT 1 ) AS ranking";
                break;
            case 5:
                $order = "SUM(po.score) DESC";
                $subselect = ",(SELECT label FROM ranking WHERE operation <= SUM(po.score) ORDER BY operation DESC LIMIT 1 ) AS ranking";
                break;
            case 7:
                $order = "SUM(p.kills) DESC";
                $subselect = ",(SELECT label FROM ranking WHERE kills <= SUM(p.kills) ORDER BY kills DESC LIMIT 1 ) AS ranking";
                break;
            default:
                $order = "SUM(EXPERIENCE) DESC";
                $subselect = ",(SELECT label FROM ranking WHERE experience <= SUM(p.experience) ORDER BY experience DESC LIMIT 1 ) AS ranking";
        }
        $arr = array();
        $query = "SELECT a.login AS login, p.account_id AS accountId,  SUM(po.score) AS operation, SUM(p.experience) AS experience, SUM(p.kills) AS kills";
        if($subselect){
            $query .= $subselect;
        }
        $query .= " FROM player_operation po, player p, account a "
                 ." WHERE  p.player_id=po.player_id "
                 ." AND a.account_id=p.account_id "
                 ." GROUP BY  a.account_id "
                 ." ORDER BY " . $order;

        $db->query($query);

        while($db->nextRecord()){
            $arr[] = $db->getRow();
        }
        return $arr;
    }

}