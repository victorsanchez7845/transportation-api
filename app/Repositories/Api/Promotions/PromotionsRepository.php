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
        $item =  DB::select('SELECT rez.id
                                FROM reservations as rez
                                WHERE rez.id = :code AND rez.is_cancelled = 0 AND rez.is_duplicated = 0 AND rez.is_advanced = 1', [
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
        return DB::select('SELECT pro.id, IFNULL(trans.name, pro.name) AS name, IFNULL(trans.description, pro.description) AS description, pro.logo, IFNULL(trans.hidden_instructions, pro.hidden_instructions) AS hidden_instructions, 
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