<?php
/**
 * Created by JetBrains PhpStorm.
 * User: tmaus
 * Date: 03.07.13
 * Time: 15:40
 * To change this template use File | Settings | File Templates.
 */

class HoF {
    private function __construct() {
    }



    public static function getHofRanking(SmrMySqlDatabase &$db, $order){
        switch($order){
            case 1:
                $order = "SUM(EXPERIENCE) DESC";
                break;
            case 2:
                $order = "SUM(po.score) DESC";
                break;
            case 3:
                $order = "SUM(p.kills) DESC";
                break;
            default:
                $order = "SUM(EXPERIENCE) DESC";
        }
        $arr = array();
        $query = "SELECT p.account_id AS accountId,  SUM(po.score) AS operation, SUM(p.experience), SUM(p.kills) as kills "
                 ." FROM player_operation po, player p "
                 ." WHERE  p.player_id=po.player_id "
                 ." GROUP BY  account_id "
                 . $order;

        $db->query($query);

        while($db->nextRecord()){
            $arr[] = $db->getRow();
        }
        return $arr;
    }

}