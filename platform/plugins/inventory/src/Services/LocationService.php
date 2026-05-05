<?php

namespace Botble\Inventory\Services;
use Illuminate\Support\Facades\DB;

class LocationService
{
    public function queryCity(){
        return DB::table('cities');
    }

    public function queryState(){
        return DB::table('states');
    }
    
    // Lấy danh sách tỉnh/thành (states)
    public function showState(){
        return $this->queryState()
            ->orderBy('name')
            ->get();
    }
    
    // Lấy quận/huyện theo tỉnh (cities)
    public function showCity($stateId){
        return $this->queryCity()
            ->where('state_id', $stateId)
            ->orderBy('name')
            ->get();
    }

    public function findCity(int $cityId): ?object
    {
        return $this->queryCity()
            ->where('id', $cityId)
            ->first();
    }
}
