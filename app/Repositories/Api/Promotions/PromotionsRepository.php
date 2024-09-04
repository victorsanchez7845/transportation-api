<?php

namespace App\Repositories\Api\Promotions;
use Illuminate\Support\Facades\DB;

class PromotionsRepository{

    public function getPromotions($request){
        return DB::select('SELECT pro.id, IFNULL(trans.name, pro.name) AS name, IFNULL(trans.description, pro.description) AS description, pro.logo, 
                            IFNULL(trans.type, pro.type) AS type, IFNULL(trans.promo, pro.promo) AS promo
                        FROM promotions as pro
                            LEFT JOIN promotions_translate as trans ON trans.promotion_id = pro.id AND trans.lang = :lang
                        WHERE pro.status = 1
                        ORDER BY pro.order ASC', 
                        [
                            'lang' => $request['language'],
                        ]);
    }

    public function check($request){
        $item =  DB::select('SELECT rit.code, rez.is_cancelled
                            FROM reservations_items as rit
                        INNER JOIN reservations as rez ON rez.id = rit.reservation_id
                        WHERE rit.code = :code and rit.destination_service_id = 1', [
                            'code' => $request->code,
                        ]);
        if(sizeof($item) >= 1):
            return true;
        else:
            return false;
        endif;
    }

    public function getItems($request){
        $items = $request->coupons;
        return DB::select('SELECT pro.id, IFNULL(trans.name, pro.name) AS name, IFNULL(trans.description, pro.description) AS description, pro.logo, 
                            IFNULL(trans.type, pro.type) AS type, IFNULL(trans.promo, pro.promo) AS promo
                        FROM promotions as pro
                            LEFT JOIN promotions_translate as trans ON trans.promotion_id = pro.id AND trans.lang = :lang
                        WHERE pro.status = 1 AND pro.id IN ('.$items.')
                        ORDER BY pro.order ASC', 
                        [
                            'lang' => $request->language
                        ]);
    }
}