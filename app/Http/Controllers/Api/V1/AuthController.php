<?php
namespace App\Http\Controllers\Api\V1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Models\User;
use App\Transformers\AuthorizationTransformer;
use App\Jobs\SendRegisterEmail;

class AuthController extends BaseController
{
    

    /**
     * @api {post} /auth/register register new user
     * @apiDescription register new user
     * @apiGroup Auth
     * @apiPermission none
     * @apiParam {String} first_name
     * @apiParam {String} last_name     
     * @apiParam {Email} email     
     * @apiParam {String} password  
     * @apiVersion 1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "data": {
     *              "user": {
     *                  "email": "admin@admin.com",
     *                  "first_name": "Leo",
     *                  "last_name": "Oliveira",
     *                  "role": "user",
     *                   "active": 1,
     *                   "updated_at": "2018-05-04 13:01:11",
     *                   "created_at": "2018-05-04 13:01:11",
     *                   "id": 1
     *             },
     *             "status_code": 201,
     *             "token": "LONG_TOKEN....",
     *             "expired_at": "2017-03-10 15:28:13",
     *             "refresh_expired_at": "2017-01-23 15:28:13"
     *         }
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 401
     *     {
     *       "error": "Register failed",
     *       "status_code": 401
     *     }
     */
    public function register(Request $request)
    {
        //Logging...
        \Log::info(json_encode($request->all()));
        \Log::info($request);

        // validate fields...
        $validator = \Validator::make($request->input(), [
            'email' => 'required|email|unique:users',
            'first_name' => 'required|max:20',
            'last_name' => 'required|max:20',
            'password' => 'required|confirmed',
        ]);

        // Validation fails?
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        // Get Password
        $password = $request->get('password');

        // Attr to create user
        $attributes = [
            'email' => $request->get('email'),
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'password' => app('hash')->make($password),
            'role' => 'user',
            'active' => 1 // for test, lets activate automatically
        ];

        // create user
        $user = User::create($attributes);
        
        $credentials = $request->only('email', 'password');

        // Validation failed will return 401
        if (!$token = \Auth::attempt($credentials)) {
            $this->response->errorUnauthorized(trans('auth.incorrect'));
        }

        $expired_at = Carbon::now()->addMinutes(config('jwt.ttl'))->timestamp;
        $refresh_expired_at = Carbon::now()->addMinutes(config('jwt.refresh_ttl'))->timestamp;

        $status_code = 201;

        $result['data'] = [
            'user' => $user,
            'token' => $token,
            'status_code' => $status_code,
            'expired_at' => $expired_at,
            'refresh_expired_at' => $refresh_expired_at,
        ];

        return $this->response
                ->array($result)
                ->setStatusCode($status_code);
    }

    /**
     * @api {put} /auth/refresh/ refresh token
     * @apiDescription refresh token
     * @apiGroup Auth
     * @apiPermission JWT
     * @apiVersion 1.0
     * @apiHeader {String} Authorization The user's old jwt-token, value has started with Bearer
     * @apiHeaderExample {json} Header-Example:
     *     {
     *       "Authorization": "Bearer {TOKEN}"
     *     }
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         "data": {
     *             "token": "{TOKEN}",
     *             "expired_at": "2017-03-10 15:28:13",
     *             "refresh_expired_at": "2017-01-23 15:28:13"
     *         }
     *     }
     */
    public function update()
    {
        $authorization = new Authorization(\Auth::refresh());
        return $this->response->item($authorization, new AuthorizationTransformer());
    }
    /**
     * @api {delete} /authorizations/current delete current token
     * @apiDescription delete current token
     * @apiGroup Auth
     * @apiPermission jwt
     * @apiVersion 1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     */
    public function destroy()
    {
        \Auth::logout();
        return $this->response->noContent();
    }

    /**
     * @api {post} /auth/ create a token
     * @apiDescription create a token
     * @apiGroup Auth
     * @apiPermission none
     * @apiParam {Email} email     
     * @apiParam {String} password  
     * @apiVersion 1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 201 Created
     *     {
     *         "data": {
     *             "token": "{TOKEN}",
     *             "status_code": 201,
     *             "expired_at": "2017-03-10 15:28:13",
     *             "refresh_expired_at": "2017-01-23 15:28:13"
     *         }
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 401
     *     {
     *       "error": "Create token failed",
     *       "status_code": 401
     *     }
     */
    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        // check user is active or not
        $user = User::where([
                ['email', '=', $request->email],
                ['active', '=', 1]
        ])->first();

        if (!$user) {
          return $this->response->errorUnauthorized();
        }

        $credentials = $request->only('email', 'password');

        // Validation failed to return 401
        if (! $token = \Auth::attempt($credentials)) {
            $this->response->errorUnauthorized(trans('auth.incorrect'));
        }

        $authorization = new Authorization($token);

        return $this->response->item($authorization, new AuthorizationTransformer())
            ->setStatusCode(201);
    }
}