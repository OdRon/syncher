<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Mail;
use \App\User;
use \App\Mail\UserCreated;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //

  //       DB::table('user_types')->insert([
		//     ['id' => '1', 'user_type' => 'System Administrator'],
		//     ['id' => '2', 'user_type' => 'Program Officers'],
		//     ['id' => '3', 'user_type' => 'Partner'],
		//     ['id' => '4', 'user_type' => 'CASCO/CHRIO'],
		//     ['id' => '5', 'user_type' => 'Sub CASCO'],
		//     ['id' => '6', 'user_type' => 'Super Counites'],
		//     ['id' => '7', 'user_type' => 'Super Partner'],
		//     ['id' => '8', 'user_type' => 'Facility Users'],
		//     ['id' => '9', 'user_type' => 'Maryland Support Team'],
		//     ['id' => '10', 'user_type' => 'Super Administrator'],
		//     ['id' => '11', 'user_type' => 'SCMS / Kit Management'],
		// 	['id' => '12', 'user_type' => 'Allocation Committee'],
		// 	['id' => '13', 'user_type' => 'Lab Administrator'],
		// 	['id' => '14', 'user_type' => 'NHRL Commodities User'],
		// 	['id' => '15', 'user_type' => 'EDARP Commodities User'],
		// ]);

		// $old_users = DB::connection('old')->table('users')->get();

		// foreach ($old_users as $old_user) {
		// 	$user = new User;
		// 	$user->id = $old_user->ID;
		// 	$user->user_type_id = $old_user->account;
		// 	$user->lab_id = $old_user->lab;
		// 	$user->surname = $old_user->surname;
		// 	$user->oname = $old_user->oname;
		// 	$user->email = $old_user->email;

		// 	$existing = User::where('email', $old_user->email)->get()->first();
		// 	if($existing) $user->email = rand(1, 20) . $user->email;

		// 	$user->password = '12345678';
		// 	$user->save();
		// }



     //    $users = factory(App\User::class, 1)->create([
	    //     'user_type_id' => 10,
	    //     'surname' => 'Kithinji',
	    //     'oname' => 'Joel',
	    //     'email' => 'joelkith@gmail.com',
	    //     'username' => 'joelkith@gmail.com',
    	// ]);

     //    $users = factory(App\User::class, 1)->create([
	    //     'user_type_id' => 14,
	    //     'surname' => 'Bakasa',
	    //     'oname' => 'Joshua',
	    //     'email' => 'bakasa@gmail.com',
	    //     'username' => 'bakasa@gmail.com',
    	// ]);

     //    $users = factory(App\User::class, 1)->create([
	    //     'user_type_id' => 10,
	    //     'surname' => 'Ngugi',
	    //     'oname' => 'Tim',
	    //     'email' => 'tim@gmail.com',
	    //     'username' => 'tim@gmail.com',
    	// ]);

     //    $users = factory(App\User::class, 1)->create([
	    //     'user_type_id' => 10,
	    //     'surname' => 'Lusike',
	    //     'oname' => 'Judy',
	    //     'email' => 'judy@gmail.com',
	    //     'username' => 'judy@gmail.com',
    	// ]);

     //    $users = factory(App\User::class, 1)->create([
	    //     'user_type_id' => 2,
	    //     'surname' => 'Default',
	    //     'oname' => 'Admin',
	    //     'email' => 'admin@admin.com',
	    //     'username' => 'admin@admin.com',
    	// ]);

    	// $facilitys = DB::table('facilitys')->get();

    	// $i=0;
    	// $data= null;

    	// foreach ($facilitys as $key => $facility) {
    	// 	$fac = factory(App\User::class, 1)->create([
		   //      'user_type_id' => 8,
		   //      'surname' => '',
		   //      'oname' => '',
		   //      'facility_id' => $facility->id,
		   //      'email' => 'facility' . $facility->id . '@nascop-lab.com',
		   //      'username' => 'facility' . $facility->id . '@nascop-lab.com',
		   //      'password' => encrypt($facility->name)
	    // 	]);

	    // 	// if($key==100) break;

    	// 	// $data[$i] = [
		   //  //     'user_type_id' => 5,
		   //  //     'surname' => '',
		   //  //     'oname' => '',
		   //  //     'facility_id' => $facility->id,
		   //  //     'email' => 'facility' . $facility->id . '@nascop-lab.com',
		   //  //     'password' => bcrypt(encrypt($facility->name)),
	    // 	// ];

	    // 	// if($i == 200){
	    // 	// 	DB::table('users')->insert($data);
	    // 	// 	$i=0;
	    // 	// 	$data = NULL;
	    // 	// }
    	// }
    	// // DB::table('users')->insert($data);

    	$allocationCommittee = [
    		[
    			'email' =>'roselinewarutere@gmail.com',
    			'surname' => 'warutere',
    			'oname' => 'roseline',
    		],[
    			'email' =>'japhgituku@gmail.com',
    			'surname' => 'gituku',
    			'oname' => 'japheth',
    		],[
    			'email' =>'eddymsa@yahoo.com',
    			'surname' => 'eddy',
    			'oname' => 'msa',
    		],[
    			'email' =>'asoita65@gmail.com',
    			'surname' => 'soita',
    			'oname' => '',
    		],[
    			'email' =>'caroline.kasera@kemsa.co.ke',
    			'surname' => 'kasera',
    			'oname' => 'caroline',
    		],[
    			'email' =>'peter.mwangi@kemsa.co.ke',
    			'surname' => 'mwangi',
    			'oname' => 'peter',
    		],[
    			'email' =>'solwande@clintonthealthaccess.org',
    			'surname' => 'olwande',
    			'oname' => 'sharon',
    		],[
    			'email' =>'tngugi@clintonthealthaccess.org',
    			'surname' => 'ngugi',
    			'oname' => 'tim',
    		],[
    			'email' =>'jlusike@clintonthealthaccess.org',
    			'surname' => 'lusike',
    			'oname' => 'judy',
    		]
    	]

    	foreach ($allocationCommittee as $key => $value) {
    		$value = (object) $value;
    		$user = factory(App\User::class, 1)->create([
		        'user_type_id' => 12,
		        'surname' => ucfirst($value->surname),
		        'oname' => ucfirst($value->oname),
		        'email' => $value->email,
		        'username' => $value->email,
	    	]);

	    	Mail::to([$user->email, 'bakasajoshua09@gmail.com'])->send(new UserCreated($user));
    	}
	}
}
