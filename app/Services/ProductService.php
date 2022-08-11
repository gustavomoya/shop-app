<?php


namespace App\Services;


use App\Models\Product;

class ProductService
{

    public function getAll() {
        $user = auth()->user();

        return Product::where('user_id', $user->id)->get();
    }

    public function getById($id) {
        $user = auth()->user();

        return Product::where('id', $id)->where('user_id', $user->id)->first();
    }

    public function create($data) {
        return Product::create($data);
    }

    public function update(Product $product, $data) {

        $product->update($data);

        return $product;
    }

    public function delete($id) {
        $product = Product::find($id);

        $product->delete();
    }

}
