<?php

namespace SkyaTura\EloquentREST\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use SkyaTura\EloquentREST\Traits\FilterTrait;
use SkyaTura\EloquentREST\Traits\ResourceTrait;
use SkyaTura\EloquentREST\Traits\ValidationTrait;

class RESTController extends Controller
{
    use ResourceTrait, ValidationTrait, FilterTrait;
}