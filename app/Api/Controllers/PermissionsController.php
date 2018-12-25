<?php

namespace App\Api\Controllers;


use Spatie\Permission\Models\Permission;

class PermissionsController extends BaseController
{
    /**
     * Get all permissions.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $permissions = Permission::all();

        return $this->response->collection($permissions);
    }
}
