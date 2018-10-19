<?php


use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;
class DatabaseSeeder extends Seeder
{
    /**
    * Seed the application's database.
    *
    * @return void
    */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // $this->call(UsersTableSeeder::class);
        App\User::truncate();
        App\Category::truncate();
        App\Product::truncate();
        App\Transaction::truncate();
        DB::table('category_product')->truncate();

        App\User::flushEventListeners();

        $userQuantity = 200;
        $categoryQuantity = 30;
        $productQuantity = 1000;
        $transactionQuantity = 1000;

        factory(App\User::class, $userQuantity)->create();
        factory(App\Category::class, $userQuantity)->create();

        factory(App\Product::class, $userQuantity)->create()->each(
            function($product){
                $categories = App\Category::all()->random(mt_rand(1, 5))->pluck('id');
                $product->categories()->attach($categories);
            });

            factory(App\Transaction::class, $userQuantity)->create();
        }
    }
