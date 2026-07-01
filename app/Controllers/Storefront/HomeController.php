<?php

declare(strict_types=1);

namespace App\Controllers\Storefront;

use App\Controllers\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Services\CatalogService;

final class HomeController extends Controller
{
    public function index(Request $request): Response
    {
        $data = (new CatalogService())->home();

        return $this->view('storefront/home', $data);
    }
}
