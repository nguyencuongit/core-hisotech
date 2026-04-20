<?php

namespace Botble\Logistics\Usecase;

use Botble\Logistics\Repositories\Interfaces\ShippingDistrictMappingInterface;
use Botble\Logistics\Repositories\Interfaces\ShippingProvinceMappingInterface;
use Botble\Logistics\Services\Factories\ShippingFactory;
use Botble\Logistics\DTO\ShippingData;

use Botble\Logistics\Repositories\Interfaces\ShippingProviderInterface;

class ShippingFeeUsecase 
{
     public function __construct(
          private ShippingDistrictMappingInterface $ShippingDistrictMappingInterface,
          private ShippingProvinceMappingInterface $ShippingProvinceMappingInterface,
          private ShippingProviderInterface $shippingProviderInterface,
     ){}

     public function shippingFee($data){
          $provider = $data['provider'];

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
          $shipping = ShippingFactory::make($provider);
          $fee = $shipping->calculateFee($dto);
          return $fee;
     }

     public function stateId($id, $code){
          return $this->ShippingProvinceMappingInterface->findByStateId($id, $code);
     }
     public function districtId($id, $code){
          return $this->ShippingDistrictMappingInterface->findByCityId($id, $code);
     }

     public function calculateCheckout($data){
          $result['logistics'] = [];
          $package = [
               'size_weight'=> 0,
               'size_length'=> 0,
               'size_width'=> 0,
               'size_height'=> 0,
          ];
          foreach($data['items'] as $item){
               $package['size_weight'] +=  $item['weight'];
               $package['size_length'] +=  $item['length'];
               $package['size_width'] +=  $item['wide'];
               $package['size_height'] +=  $item['height'];
          }
          $address = [
               'from_province_id'=> $data['origin']['state'],
               'to_province_id'=> $data['address_to']['state'],
               'from_district_id'=> $data['origin']['city'],
               'to_district_id'=> $data['address_to']['city'],
          ];
          $informations = array_merge($address, $package);
          $providers = $this->shippingProviderInterface->findByIsActive(1);
          foreach($providers as $item){
               $informations['provider'] = $item['code'];
               $fee = $this->shippingFee($informations);
               if(isset($fee)){
                    $result['logistics'][$item["code"]] = [
                         'name'  => $item["name"],
                         'price' => $fee,
                    ];
               }
          }
          return $result;
     }
}