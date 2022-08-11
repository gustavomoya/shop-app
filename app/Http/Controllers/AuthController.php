<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Validator;
use Session;

class AuthController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Login user and get a JWT via given credentials.
     *
     * @param  Request  $request
     *
     * @OA\Post(
     *     path="/api/login",
     *     tags={"auth"},
     *     summary="Login an user",
     *     description="Login an user",
     *     operationId="login",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="johndoe@email.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="the token"
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="number",
     *                 example="3600"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="string",
     *                 format="array",
     *                 example={"id": 1, "name":"John Doe", "email": "johndoe@email.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     ),
     *    @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), Response::HTTP_BAD_REQUEST);
        }

        if (! $token = auth()->attempt($validator->validated())) {
            return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        return $this->createNewToken($token);
    }

    /**
     * Register a user.
     *
     * @param  Request  $request
     *
     * @OA\Post(
     *     path="/api/register",
     *     tags={"auth"},
     *     summary="Register a user",
     *     description="Register a user",
     *     operationId="register",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="name",
     *                 type="string",
     *                 example="John Doe"
     *             ),
     *             @OA\Property(
     *                 property="email",
     *                 type="string",
     *                 example="johndoe@email.com"
     *             ),
     *             @OA\Property(
     *                 property="password",
     *                 type="string",
     *                 example="password"
     *             ),
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="User successfully registered"
     *             ),
     *             @OA\Property(
     *                 property="access_token",
     *                 type="string",
     *                 example="the token"
     *             ),
     *             @OA\Property(
     *                 property="expires_in",
     *                 type="number",
     *                 example="3600"
     *             ),
     *             @OA\Property(
     *                 property="user",
     *                 type="string",
     *                 format="array",
     *                 example={"id": 1, "name":"John Doe", "email": "johndoe@email.com"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request"
     *     )
     * )
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();

        try {
            $user = User::create(array_merge(
                $validator->validated(),
                ['password' => bcrypt($request->password)]
            ));

            $credentials = request(['email', 'password']);

            $token = auth()->attempt($credentials);

            $data = $this->createNewToken($token);

            return response()->json(array_merge(
                ['message' => 'User successfully registered'],
                json_decode($data->content(), true)
            ), 201);
        } catch (\Throwable $ex) {

            DB::rollback();

            return response()->json([
                'message' => 'An error occurred',
            ], 500);
        }
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile()
    {
        return response()->json(auth()->user());
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @OA\Post(
     *     path="/api/logout",
     *     tags={"auth"},
     *     summary="Logs out current logged in user session",
     *     operationId="logoutUser",
     *     @OA\Response(
     *         response="204",
     *         description="successful operation"
     *     )
     * )
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {

        Session::flush();

        auth()->logout();

        return response()->json(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->createNewToken(auth()->refresh());
    }


    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->user()
        ]);
    }
}
