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
                'address' => 'myaddress1',
            ],
            [
                'id' => 2,
                'name' => 'QuangPV2',
                'email' => 'quangpv@gmal.com2',
                'address' => 'myaddress2',
            ],
            [
                'id' => 3,
                'name' => 'QuangPV3',
                'email' => 'quangpv@gmal.com3',
                'address' => 'myaddress3',
            ],
        ];
      
       $index = ['id'] // update by id
       $fieldsUpdate = ['name']; // just update field `name`
       $expectFields = ['name']; // update all fields except field `name`

       // Just update field `name`
       App::make(User::class)->wantsUpsert($records, ['id'], ['name'], []); 
      
       // Update all field by id
       App::make(User::class)->wantsUpsert($records, ['id'], [], []);
     
      // update all field except 'email'
       App::make(User::class)->wantsUpsert($records, ['id'], [], ['email']);

       // or this->repository->wantsUpsert($records)
    }
}
