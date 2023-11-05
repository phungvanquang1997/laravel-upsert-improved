<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\App;

class TestController extends Controller
{
    public function test()
    {
         $records = [
            [
                'id' => 1,
                'name' => 'QuangPV1',
                'email' => 'quangpv@gmal.com1',
            ],
            [
                'id' => 2,
                'name' => 'QuangPV2',
                'email' => 'quangpv@gmal.com2',
            ],
            [
                'id' => 3,
                'name' => 'QuangPV3',
                'email' => 'quangpv@gmal.com3',
            ],
        ];
       // or this->repository->wantsUpsert($records)
       App::make(User::class)->wantsUpsert($records);
    }
}
