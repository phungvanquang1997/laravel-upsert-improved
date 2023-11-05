<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class TestController extends Controller
{
    public function test(UserRepository $userRepository)
    {
        $a = $this->getAll();
        \DB::enableQueryLog();
        App::make(User::class)->wantsUpsert($a);
        dd(\DB::getQueryLog());

    }

    public function getAll()
    {
        return User::query()->get();
    }
}