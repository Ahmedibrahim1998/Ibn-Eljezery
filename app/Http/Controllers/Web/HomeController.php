<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\Web\Home\HomePageService;
use Illuminate\Contracts\View\View;

class HomeController extends Controller
{
    public function __construct(
        private readonly HomePageService $homePageService,
    ) {}

    public function index(): View
    {
        return view('home.index', $this->homePageService->data());
    }
}
