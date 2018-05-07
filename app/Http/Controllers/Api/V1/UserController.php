<?php
namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Authorization;
use App\Transformers\UserTransformer;
use App\Transformers\EventTransformer;

class UserController extends BaseController
{
    /**
     * @api {get} /users user list
     * @apiDescription user list
     * @apiGroup user
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": [
     *         {
     *           "id": 2,
     *           "email": "490554191@qq.com",
     *           "name": "fff",
     *           "created_at": "2015-11-12 10:37:14",
     *           "updated_at": "2015-11-13 02:26:36",
     *           "deleted_at": null
     *         }
     *       ],
     *       "meta": {
     *         "pagination": {
     *           "total": 1,
     *           "count": 1,
     *           "per_page": 15,
     *           "current_page": 1,
     *           "total_pages": 1,
     *           "links": []
     *         }
     *       }
     *     }
     */
    public function index(User $user)
    {
        if ($this->user()->role == 'admin') {
            $users = User::whereIn('role', ['user', 'admin'])->paginate();
        }
        else if ($this->user()->role == 'superadmin') {
            $users = User::paginate();
        }
        else {
            return $this->response->errorUnauthorized();
        }

        return $this->response->paginator($users, new UserTransformer());
    }

    /**
     * @api {get} /users/{id} user's info
     * @apiDescription user's info
     * @apiGroup user
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": {
     *         "id": 2,
     *         "email": "490554191@qq.com",
     *         "name": "fff",
     *         "created_at": "2015-11-12 10:37:14",
     *         "updated_at": "2015-11-13 02:26:36",
     *         "deleted_at": null
     *       }
     *     }
     */
    public function show($id)
    {
        $user = User::findOrFail($id);
        return $this->response->item($user, new UserTransformer());
    }
    /**
     * @api {get} /user current user info
     * @apiDescription current user info
     * @apiGroup user
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *       "data": {
     *         "id": 2,
     *         "email": 'user@gmail.com',
     *         "name": "foobar",
     *         "created_at": "2015-09-08 09:13:57",
     *         "updated_at": "2015-09-08 09:13:57",
     *         "deleted_at": null
     *       }
     *     }
     */
    public function userShow()
    {
        return $this->response->item($this->user(), new UserTransformer());
    }
    
    /**
     * @api {post} create a user
     * @apiDescription create a user
     * @apiGroup user
     * @apiPermission none
     * @apiVersion 0.1.0
     * @apiParam {Email}  email   email[unique]
     * @apiParam {String} password   password
     * @apiParam {String} name      name
     * @apiParam {Date}  birthdate  birthdate
     * @apiParam {String} role   role
     * @apiParam {String} active      active
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         token: {TOKEN}
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "email": [
     *             "Email has been registered by others"
     *         ],
     *     }
     */
    public function store(Request $request)
    {
        // forbidden
        if ($this->user()->role == 'user') {
            return $this->response->errorForbidden();
        }

        $validator = \Validator::make($request->all(), [
            'email'       => 'required|email|unique:users',
            'first_name'  => 'required|min:3',
            'last_name'  => 'required|min:3',
            'password'    => 'required|confirmed|min:3',
            'role'        => 'required|string',
            'active'      => 'required',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $active = (int)($request->active === 'true');

        $attributes = [
            'email' => $request->get('email'),
            'first_name' => $request->get('first_name'),
            'last_name' => $request->get('last_name'),
            'password' => app('hash')->make($request->get('password')),
            'created_at' => \Carbon\Carbon::now(),
            'updated_at' => \Carbon\Carbon::now(),
            'role' => $request->role,
            'active' => $active
        ];
        $user = User::create($attributes);

        return $this->response->item($user, new UserTransformer());
    }

    /**
     * @api {put} /user/id update user
     * @apiDescription update user by id
     * @apiGroup user | admin
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiParam {String}                   
     * @apiParam {String} first_name
     * @apiParam {String} last_name
     * @apiParam {String} email              
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "email": [
     *             "Email is not valid",
     *         ],
     *         "first_name": [
     *             "Frist name is not valid"
     *         ],
     *         "last_name": [
     *             "Last name is not valid"
     *         ]
     *     }
     */
    public function update($id, Request $request)
    {
        // is Admin?
        $isAdmin = $this->user()->role == 'admin' || $this->user()->role == 'superadmin';

        // Allow only the current user and admin
        if ($this->user()->id != $id && !$isAdmin)
        {
            //Forbbiden
            return $this->response->errorForbidden();
        }

        $user = User::find($id);

        if (!$user)
        {
            return $this->response->errorNotFound();
        }

        $validation = [
            'email'       => 'required|min:3|email|unique:users,email,'. $id,
            'first_name'  => 'required|min:3',
            'last_name'   => 'required|min:3',
        ];

        $validator = \Validator::make($request->input(), $validation);

        // Validation fails
        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        // Assign user
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->updated_at = \Carbon\Carbon::now();

        // Save the user
        $user->save();
        return $this->response->item($user, new UserTransformer());
    }

    /**
     * @api {put} /user/password update password
     * @apiDescription update password
     * @apiGroup user
     * @apiPermission JWT
     * @apiVersion 0.1.0
     * @apiParam {String} old_password          
     * @apiParam {String} password              
     * @apiParam {String} password_confirmation 
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 204 No Content
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 400 Bad Request
     *     {
     *         "password": [
     *             "The password entered twice is inconsistent",
     *             "Old and new passwords can not be the same"
     *         ],
     *         "password_confirmation": [
     *             "The password entered twice is inconsistent"
     *         ],
     *         "old_password": [
     *             "wrong password"
     *         ]
     *     }
     */
    public function updatePassword(Request $request)
    {

        $validator = \Validator::make($request->all(), [
            'old_password' => 'required',
            //'password' => 'required|confirmed|different:old_password',
            'password' => 'required|confirmed',
            'password_confirmation' => 'required|same:password',
        ]);

        if ($validator->fails()) {
            return $this->errorBadRequest($validator);
        }

        $user = $this->user();

        $auth = \Auth::once([
            'email' => $user->email,
            'password' => $request->get('old_password'),
        ]);
        
        if (!$auth)
            return $this->response->errorUnauthorized();
        
        $password = app('hash')->make($request->get('password'));

        $user->update([
            'password' => $password
        ]);

        return $this->response->noContent();
    }

    public function userEvents()
    {
        // Get Events
        $events = $this->user()->events()->paginate();

        return  $this->response->paginator($events, new EventTransformer());
    }

    /**
     * @api {put} /users/{id} delete
     * @apiDescription delete user
     * @apiGroup user | admin
     * @apiPermission JWT
     * @apiVersion 1.0
     * @apiParam {int} id          
     * @apiSuccessExample {json} Success-Response:
     *     HTTP/1.1 200 OK
     *     {
     *         {user transformer}
     *     }
     * @apiErrorExample {json} Error-Response:
     *     HTTP/1.1 404 Not Found
     *     {
     *         "error": "User not found"
     *     }
     */
    public function destroy($id)
    {

        // is Admin?
        $isAdmin = $this->user()->role == 'admin' || $this->user()->role == 'superadmin';

        // Allow only the current user and admin
        if ($this->user()->id != $id && !$isAdmin)
        {
            //Forbbiden
            return $this->response->errorForbidden();
        }

        $user = User::find($id);

        if (!$user)
        {
            return $this->response->errorNotFound();
        }

        $user->forceDelete();

        return $this->response->item($user, new UserTransformer());
    }
    
}