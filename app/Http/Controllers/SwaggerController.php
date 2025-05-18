<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *     title="My Awesome API",
 *     version="1.0.0",
 *     description="Swagger test documentation",
 *     @OA\Contact(
 *         email="your@email.com"
 *     )
 * )
 */


class SwaggerController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/welcome",
     *     summary="Say Hello",
     *     tags={"Swagger"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful welcome"
     *     )
     * )
     */
    public function welcome()
    {
        return response()->json("hello");
    }
}
