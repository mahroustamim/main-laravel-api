<?php

namespace App\Traits;

trait ResponseTrait
{
    public function returnError($errors, $code = 404) 
    {
        $response = [
            'success' => false,
            'errors' => $errors,
        ];
        
        return response()->json($response, $code);
    }

    public function returnData($data, $msg='') 
    {
        $response = [
            'success' => true,
            'data' => $data,
            'msg' => $msg,
        ];
        return response()->json($response, 200);
    }

    public function returnSuccess($msg)
    {
        $response = [
            'success' => true,
            'msg' => $msg,
        ];
        
        return response()->json($response, 200);
    }
}