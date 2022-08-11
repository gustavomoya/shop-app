<?php

namespace App\Http\Controllers;

use App\Services\ProductService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Validator;

class ProductController extends Controller
{
    private ProductService $productService;

    public function __construct(ProductService $productService) {
        $this->middleware('auth:api');

        $this->productService = $productService;
    }

    /**
     * Returns a list of products.
     *
     */
    /**
     * @OA\Get(
     *     path="/api/products",
     *     tags={"products"},
     *     summary="Returns a list of products",
     *     description="Returns a list of products",
     *     operationId="index",
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                     @OA\Property(
     *                         property="id",
     *                         type="number",
     *                         example="1"
     *                      ),
     *                      @OA\Property(
     *                         property="name",
     *                         type="string",
     *                         example="Iphone 11"
     *                      ),
     *                      @OA\Property(
     *                         property="description",
     *                         type="string",
     *                         example="Latest iphone in the market"
     *                      ),
     *                      @OA\Property(
     *                         property="amount",
     *                         type="number",
     *                         example="500"
     *                      ),
     *                ),
     *         )
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $products =  $this->productService->getAll();

        return response()->json($products, Response::HTTP_OK);
    }

    /**
     * Create a new product.
     *
     * @param  Request  $request
     *
     * @OA\Post(
     *     path="/api/products",
     *     tags={"products"},
     *     summary="Create a new product",
     *     description="Create a new product",
     *     operationId="store",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Iphone 11"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Latest iphone in the market"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example="500"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="number",
     *                 example="1"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Iphone 11"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Latest iphone in the market"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example="500"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Invalid input"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'amount' => 'required|integer'
        ]);

        $user = auth()->user();
        $validated['user_id'] = $user->id;

        $product =  $this->productService->create($validated);

        return response()->json($product, Response::HTTP_CREATED);
    }

    /**
     * Get a product by id.
     *
     * @param  int  $id
     *
     * @OA\Get(
     *     path="/api/products/{id}",
     *     tags={"products"},
     *     description="Get a product by id",
     *     operationId="show",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="the product id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="number",
     *                 example="1"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Iphone 11"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Latest iphone in the market"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example="500"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {

        $product =  $this->productService->getById($id);

        if ($product) {
            return response()->json($product, Response::HTTP_OK);
        } else {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }
    }



    /**
     * Update a product.
     *
     * @param  Request  $request
     * @param  int  $id
     *
     * @OA\Put(
     *     path="/api/products/{id}",
     *     tags={"products"},
     *     summary="Update a given product",
     *     description="Update a given product",
     *     operationId="update",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Iphone 11"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Latest iphone in the market"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example="500"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="id",
     *                 type="number",
     *                 example="1"
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="Iphone 11"
     *             ),
     *             @OA\Property(
     *                 property="description",
     *                 type="string",
     *                 example="Latest iphone in the market"
     *             ),
     *             @OA\Property(
     *                 property="amount",
     *                 type="number",
     *                 example="500"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $product = $this->productService->getById($id);
        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:500',
            'amount' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
        }

        $product =  $this->productService->update($product,  $validator->validated());

        return response()->json($product, Response::HTTP_OK);
    }


    /**
     * Remove the given product
     *
     * @param  $id
     *
     * @OA\Delete(
     *     path="/api/products/{id}",
     *     tags={"products"},
     *     summary="Delete a product",
     *     description="Delete a product",
     *     operationId="destroy",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of product that needs to be deleted",
     *         @OA\Schema(
     *             type="integer",
     *             minimum=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=204,
     *         description="successful operation"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found"
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function destroy($id)
    {

        $product = $this->productService->getById($id);

        if (!$product) {
            return response()->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->productService->delete($id);

        return response(null, Response::HTTP_NO_CONTENT);
    }
}
