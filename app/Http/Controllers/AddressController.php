<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\AddressService;

class AddressController extends Controller
{
    protected $addressService;
    
    public function __construct(AddressService $addressService)
    {
        $this->addressService = $addressService;
    }

    public function provinces()
    {
        $provinces = $this->addressService->getProvinces();
        return response()->json($provinces);
    }
    
    public function provinceWithDistricts($id)
    {
        $province = $this->addressService->getProvinceWithDistricts($id);
        if (!$province) {
            return response()->json(['message' => 'Province not found'], 404);
        }
        return response()->json($province);
    }
    
    public function districtWithWards($id)
    {
        $district = $this->addressService->getDistrictWithWards($id);
        if (!$district) {
            return response()->json(['message' => 'District not found'], 404);
        }
        return response()->json($district);
    }
}