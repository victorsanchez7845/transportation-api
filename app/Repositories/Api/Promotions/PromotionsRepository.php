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
}