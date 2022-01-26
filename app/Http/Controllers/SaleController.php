<?php

namespace App\Http\Controllers;

use App\Http\Resources\SaleResource;
use Illuminate\Support\Facades\Auth;

class SaleController extends Controller
{
    public function index()
    {
        return SaleResource::collection(Auth::user()->sales);
    }
}
