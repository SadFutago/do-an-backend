<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class AddressService
{
    protected $baseUrl = 'https://provinces.open-api.vn/api';
    
    public function getProvince($provinceId)
    {
        return Cache::remember('province_'.$provinceId, 86400, function () use ($provinceId) {
            $response = Http::get("{$this->baseUrl}/p/{$provinceId}");
            return $response->successful() ? $response->json() : null;
        });
    }
    
    public function getDistrict($districtId)
    {
        return Cache::remember('district_'.$districtId, 86400, function () use ($districtId) {
            $response = Http::get("{$this->baseUrl}/d/{$districtId}");
            return $response->successful() ? $response->json() : null;
        });
    }
    
    public function getWard($wardId)
    {
        return Cache::remember('ward_'.$wardId, 86400, function () use ($wardId) {
            $response = Http::get("{$this->baseUrl}/w/{$wardId}");
            return $response->successful() ? $response->json() : null;
        });
    }

    public function getProvinces()
    {
        return Cache::remember('provinces', 86400, function () {
            $response = Http::get("{$this->baseUrl}/p");
            return $response->successful() ? $response->json() : null;
        });
    }

    public function getProvinceWithDistricts($provinceId)
    {
        return Cache::remember('province_with_districts_'.$provinceId, 86400, function () use ($provinceId) {
            $response = Http::get("{$this->baseUrl}/p/{$provinceId}", [
                'depth' => 2
            ]);
            return $response->successful() ? $response->json() : null;
        });
    }

    public function getDistrictWithWards($districtId)
    {
        return Cache::remember('district_with_wards_'.$districtId, 86400, function () use ($districtId) {
            $response = Http::get("{$this->baseUrl}/d/{$districtId}", [
                'depth' => 2
            ]);
            return $response->successful() ? $response->json() : null;
        });
    }
}