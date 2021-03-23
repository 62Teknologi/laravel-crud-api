<?php

namespace EnamDuaTeknologi\LaravelCrudApi\Traits;

use Illuminate\Http\Request;

trait Requestable
{
    public $requestable = true;

    public function get($path, $auth = null)
    {
        return $this->process(Request::create($path, 'GET'), $auth);
    }

    public function post($path, $data, $auth = null)
    {
        return $this->process(Request::create($path, 'POST', $data), $auth);
    }

    public function put($path, $data, $auth = null)
    {
        return $this->process(Request::create($path, 'PUT', $data), $auth);
    }

    public function del($path, $data, $auth = null)
    {
        return $this->process(Request::create($path, 'DELETE'), $auth);
    }

    private function process($request, $auth)
    {
        if ($auth) {
            $request->headers->set('Authorization', 'Bearer '.$auth);
        }

        $request = app()->handle($request);
        $response = json_decode($request->getContent(), true);
        $response["status_code"] = $request->getStatusCode();
        
        if ($response["status_code"] === 401 && $auth != 'login') {
            session()->flush();
            return redirect(env('APP_URL').'/admin/auth/logout')->send();
        }

        return $response;
    }
}
