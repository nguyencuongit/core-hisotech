<?php

namespace Botble\Logistics\Usecase;

use Botble\Logistics\Repositories\Interfaces\ShippingDistrictMappingInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingProvinceMappingInterface;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\DTO\ShippingData;



class ShippingFeeUsecase 
{
     public function __construct(
          private ShippingDistrictMappingInterface $ShippingDistrictMappingInterface,
          private ShippingProvinceMappingInterface $ShippingProvinceMappingInterface
     ){}

     public function shippingFee($data){
          $code = $data['provider'];

          $fromProvinceID = $this->stateId($data['from_province_id'],$data['provider'] );
          $toProvinceID = $this->stateId($data['to_province_id'],$data['provider'] );

          $fromDistrictID = $this->districtId($data['from_district_id'],$data['provider'] );
          $toDistrictID = $this->districtId($data['to_district_id'],$data['provider'] );


          $dto = new ShippingData(
               fromProvinceID: $fromProvinceID['province_id'],
               fromDistrictID: $fromDistrictID['district_id'],
               toProvinceID: $toProvinceID['province_id'],
               toDistrictID: $toDistrictID['district_id'],
               weight: (int) $data['size_weight'],
               length: (int) $data['size_length'],
               width: (int) $data['size_width'],
               height: (int) $data['size_height'],
          );
          $shipping = ShippingFactory::make($code);
          $fee = $shipping->calculateFee($dto);
          return $fee;
     }

     public function stateId($id, $code){
          return $this->ShippingProvinceMappingInterface->findByStateId($id, $code);
     }
     public function districtId($id, $code){
          return $this->ShippingDistrictMappingInterface->findByCityId($id, $code);
     }
}