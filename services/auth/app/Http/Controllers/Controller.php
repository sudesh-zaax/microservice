<?php

namespace App\Http\Controllers;

abstract class Controller
{
    
	public function returnExceptionResponse($message,$code=500){
		return response()->json([
		'status' => 'error',
		'statusCode' => $code,
		'message' => $message,
		], $code);
	}
	
	
	
	public function returnResponse($data,$message,$code=200){
		if($data){
			return response()->json([
				'success' => true,
				'statusCode' => $code,
				'message' => $message,
				'response' => $data
			], $code);
		}else{
			return response()->json([
				'success' => true,
				'statusCode' => $code,
				'message' => $message,
				'response'=>[]
			], $code);
		}
	}
}
