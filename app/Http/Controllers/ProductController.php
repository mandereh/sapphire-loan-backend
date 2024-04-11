<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Http\Requests\StoreProductRequest;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    //

    public function availableProducts(Request $request){
        $products = Product::where('active', true);

        $search = $request->q;

        if($search){
            $products = $products->where(function($q) use ($search){
                $q->where('name', 'LIKE', "%$search%")
                    ->orWhere('description', 'LIKE', "%$search%");
            });
                           
        }

        $products = $products->paginate(10);

        $resp = [
            'status_code' => '00',
            'message' => "Products retrieved Successfully",
            'data' => $products
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function index(Request $request){
        $products = new Product();

        $search = $request->q;
        if($search){
            $products = $products
                            ->where('name', 'LIKE', "%$search%")
                            ->orWhere('description', 'LIKE', "%$search%");
        }

        $products = $products->paginate(10);

        $resp = [
            'status_code' => '00',
            'message' => "Products retrieved Successfully",
            'data' => Product::paginate(10)
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function store(StoreProductRequest $request){
        $product = Product::create([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'active' => true
        ]);

        $resp = [
            'status_code' => '00',
            'message' => "Product created Successfully",
            'data' => $product
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function update(StoreProductRequest $request, Product $product){
        $product->update([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
        ]);

        $resp = [
            'status_code' => '00',
            'message' => "Product created Successfully",
            'data' => $product
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }

    public function deactivate(StoreProductRequest $request, Product $product){
        $product->active = false;

        $product->save();

        $resp = [
            'status_code' => '00',
            'message' => "Product Successfully deactivated",
        ];

        $statusCode = 200;

        return response($resp, $statusCode);
    }
}
